<?php
declare(strict_types=1);

/**
 * Seller & Product Verification and Approvals Center
 * Handmade and Art Products Marketplace Platform
 */

$adminTitle = 'Approvals Center - Artisan Admin';
$activePage = 'approvals';
require_once __DIR__ . '/includes/admin_header.php';

$currentTab = sanitizeInput($_GET['tab'] ?? 'sellers');
if (!in_array($currentTab, ['sellers', 'products'], true)) {
    $currentTab = 'sellers';
}

// Handle Approval / Rejection Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $actionType = sanitizeInput($_POST['action_type'] ?? '');
    $decision = sanitizeInput($_POST['decision'] ?? ''); // 'approve' or 'reject'

    if (!in_array($decision, ['approve', 'reject'], true)) {
        setFlash('danger', 'Invalid moderation decision.');
        header("Location: approvals.php?tab={$currentTab}");
        exit;
    }

    if ($actionType === 'seller') {
        $profileId = (int)($_POST['profile_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT sp.id, sp.user_id, sp.store_name, u.full_name, u.email FROM seller_profiles sp JOIN users u ON sp.user_id = u.id WHERE sp.id = :id LIMIT 1');
        $stmt->execute([':id' => $profileId]);
        $seller = $stmt->fetch();

        if ($seller) {
            $newStatus = ($decision === 'approve') ? 'approved' : 'rejected';
            $update = $pdo->prepare('UPDATE seller_profiles SET status = :status WHERE id = :id');
            $update->execute([':status' => $newStatus, ':id' => $profileId]);

            // When approved, promote user role to 'seller' if not already
            if ($decision === 'approve') {
                $roleStmt = $pdo->prepare("UPDATE users SET role = 'seller' WHERE id = :user_id");
                $roleStmt->execute([':user_id' => $seller['user_id']]);
                setFlash('success', "Artisan Application for <strong>" . e($seller['store_name']) . "</strong> (" . e($seller['full_name']) . ") was approved successfully. Seller store is now live.");
            } else {
                setFlash('warning', "Artisan Application for <strong>" . e($seller['store_name']) . "</strong> has been rejected.");
            }
        } else {
            setFlash('danger', 'Seller application record not found.');
        }

        header('Location: approvals.php?tab=sellers');
        exit;
    }

    if ($actionType === 'product') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT id, name, name AS title, price FROM products WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $productId]);
        $product = $stmt->fetch();

        if ($product) {
            $newStatus = ($decision === 'approve') ? 'approved' : 'rejected';
            $update = $pdo->prepare('UPDATE products SET status = :status, updated_at = NOW() WHERE id = :id');
            $update->execute([':status' => $newStatus, ':id' => $productId]);

            $pName = $product['name'] ?? $product['title'] ?? 'Product';
            if ($decision === 'approve') {
                setFlash('success', "Product item <strong>" . e($pName) . "</strong> has been approved and published to the marketplace.");
            } else {
                setFlash('warning', "Product item <strong>" . e($pName) . "</strong> was rejected.");
            }
        } else {
            setFlash('danger', 'Product record not found.');
        }

        header('Location: approvals.php?tab=products');
        exit;
    }
}

// 1. Fetch Pending Sellers
$sellersStmt = $pdo->query("
    SELECT sp.*, u.full_name, u.email, u.avatar_url
    FROM seller_profiles sp
    JOIN users u ON sp.user_id = u.id
    WHERE sp.status = 'pending'
    ORDER BY sp.created_at ASC
");
$pendingSellersList = $sellersStmt->fetchAll();

// 2. Fetch Pending Products
$productsStmt = $pdo->query("
    SELECT p.*, p.name AS title, p.stock AS stock_quantity, c.name AS category_name, u.full_name AS seller_name, sp.store_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN seller_profiles sp ON u.id = sp.user_id
    WHERE p.status = 'pending'
    ORDER BY p.created_at ASC
");
$pendingProductsList = $productsStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1 text-dark">Platform Verification &amp; Approvals</h2>
        <p class="text-muted small mb-0">Review artisan store registrations and authenticate newly submitted art/craft inventory.</p>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded-3 shadow-sm border" id="approvalTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= ($currentTab === 'sellers') ? 'active' : '' ?>" href="approvals.php?tab=sellers">
            <i class="bi bi-person-badge me-1"></i> Pending Seller Registrations
            <span class="badge bg-<?= ($currentTab === 'sellers') ? 'light text-dark' : 'warning text-dark' ?> ms-2">
                <?= count($pendingSellersList) ?>
            </span>
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link <?= ($currentTab === 'products') ? 'active' : '' ?>" href="approvals.php?tab=products">
            <i class="bi bi-box-seam me-1"></i> Pending Handmade Products
            <span class="badge bg-<?= ($currentTab === 'products') ? 'light text-dark' : 'warning text-dark' ?> ms-2">
                <?= count($pendingProductsList) ?>
            </span>
        </a>
    </li>
</ul>

<!-- Tab 1: Sellers -->
<?php if ($currentTab === 'sellers'): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fs-6 fw-bold text-dark">
                <i class="bi bi-shop text-primary me-2"></i> Artisan Seller Applications Awaiting Review
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Store Details</th>
                        <th>Bio &amp; Craftmanship</th>
                        <th>Applied On</th>
                        <th class="text-end">Verification Decision</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingSellersList)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-all fs-1 text-success d-block mb-2"></i>
                                Great job! No pending seller applications in the queue.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendingSellersList as $s): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= e($s['avatar_url'] ?: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=80&q=80') ?>" class="rounded-circle me-3 border" width="40" height="40" style="object-fit: cover;">
                                        <div>
                                            <div class="fw-bold text-dark"><?= e($s['full_name']) ?></div>
                                            <div class="text-muted small"><?= e($s['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= e($s['store_name']) ?></div>
                                    <div class="small text-muted font-monospace"><i class="bi bi-telephone me-1"></i><?= e($s['phone'] ?? 'N/A') ?></div>
                                </td>
                                <td style="max-width: 320px;">
                                    <p class="small text-muted mb-0 text-truncate" title="<?= e($s['bio'] ?? '') ?>">
                                        <?= e($s['bio'] ?? 'No bio provided.') ?>
                                    </p>
                                </td>
                                <td class="text-muted small">
                                    <?= date('M d, Y', strtotime($s['created_at'])) ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <form action="approvals.php?tab=sellers" method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action_type" value="seller">
                                        <input type="hidden" name="profile_id" value="<?= (int)$s['id'] ?>">
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 me-1 shadow-sm">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>

                                    <form action="approvals.php?tab=sellers" method="POST" class="d-inline" onsubmit="return confirm('Reject this seller application?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action_type" value="seller">
                                        <input type="hidden" name="profile_id" value="<?= (int)$s['id'] ?>">
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Tab 2: Products -->
<?php if ($currentTab === 'products'): ?>
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="card-title mb-0 fs-6 fw-bold text-dark">
                <i class="bi bi-palette text-warning me-2"></i> Handmade Artworks &amp; Crafts Awaiting Moderation
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Item Preview</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Seller / Artisan</th>
                        <th>Pricing &amp; Stock</th>
                        <th>Submitted</th>
                        <th class="text-end">Moderation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingProductsList)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                                All product submissions have been reviewed and approved!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendingProductsList as $p): ?>
                            <tr>
                                <td style="width: 70px;">
                                    <img src="<?= e($p['image_url'] ?: 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=120&q=80') ?>" class="rounded-2 border" width="60" height="60" style="object-fit: cover;" alt="Product">
                                </td>
                                <td style="max-width: 260px;">
                                    <div class="fw-bold text-dark"><?= e($p['name'] ?? $p['title'] ?? 'Artwork') ?></div>
                                    <p class="small text-muted mb-0 text-truncate" title="<?= e($p['description'] ?? '') ?>">
                                        <?= e($p['description'] ?? '') ?>
                                    </p>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border">
                                        <?= e($p['category_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($p['store_name'] ?? $p['seller_name']) ?></div>
                                    <div class="small text-muted"><?= e($p['seller_name']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">$<?= number_format((float)$p['price'], 2) ?></div>
                                    <div class="small text-muted font-monospace">Qty: <?= (int)($p['stock'] ?? $p['stock_quantity'] ?? 0) ?></div>
                                </td>
                                <td class="text-muted small">
                                    <?= date('M d, Y', strtotime($p['created_at'])) ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <form action="approvals.php?tab=products" method="POST" class="d-inline">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action_type" value="product">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="decision" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 me-1 shadow-sm">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>

                                    <form action="approvals.php?tab=products" method="POST" class="d-inline" onsubmit="return confirm('Reject this product submission?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action_type" value="product">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="decision" value="reject">
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
