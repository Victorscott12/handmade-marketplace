<?php
declare(strict_types=1);

/**
 * User Management Module
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/auth_middleware.php';

// Enforce Admin Role
$adminUser = requireAdmin('/LearningPHP/PhungTuanKiet_PHP/login.php');
$pdo = getDbConnection();

// Handle Lock / Unlock Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    requireCsrfToken();

    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $newStatus = sanitizeInput($_POST['new_status'] ?? '');

    if ($targetUserId <= 0 || !in_array($newStatus, ['active', 'locked'], true)) {
        setFlash('danger', 'Invalid user status modification request.');
        header('Location: users.php');
        exit;
    }

    // Safety constraint: Prevent admin from locking their own account
    if ($targetUserId === (int)$adminUser['id']) {
        setFlash('danger', 'Security Violation: You cannot lock your own administrator account.');
        header('Location: users.php');
        exit;
    }

    // Check target user existence
    $checkStmt = $pdo->prepare('SELECT id, full_name, email, role, status FROM users WHERE id = :id LIMIT 1');
    $checkStmt->execute([':id' => $targetUserId]);
    $targetUser = $checkStmt->fetch();

    if (!$targetUser) {
        setFlash('danger', 'User record not found.');
    } else {
        $updateStmt = $pdo->prepare('UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id');
        $updateStmt->execute([
            ':status' => $newStatus,
            ':id' => $targetUserId
        ]);

        $actionWord = ($newStatus === 'locked') ? 'locked' : 'unlocked and restored to active state';
        setFlash('success', "Account for <strong>" . e($targetUser['full_name']) . "</strong> ({$targetUser['email']}) has been {$actionWord}.");
    }

    header('Location: users.php');
    exit;
}

$adminTitle = 'User Management - Artisan Admin';
$activePage = 'users';
require_once __DIR__ . '/includes/admin_header.php';

// Filters & Search handling
$searchKeyword = sanitizeInput($_GET['q'] ?? '');
$filterRole = sanitizeInput($_GET['role'] ?? '');
$filterStatus = sanitizeInput($_GET['status'] ?? '');

$sql = 'SELECT id, full_name, email, role, status, avatar_url, created_at FROM users WHERE 1=1';
$params = [];

if (!empty($searchKeyword)) {
    $sql .= ' AND (full_name LIKE :keyword OR email LIKE :keyword)';
    $params[':keyword'] = '%' . $searchKeyword . '%';
}

if (!empty($filterRole) && in_array($filterRole, ['buyer', 'seller', 'admin'], true)) {
    $sql .= ' AND role = :role';
    $params[':role'] = $filterRole;
}

if (!empty($filterStatus) && in_array($filterStatus, ['active', 'locked'], true)) {
    $sql .= ' AND status = :status';
    $params[':status'] = $filterStatus;
}

$sql .= ' ORDER BY id ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usersList = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1 text-dark">User Account Governance</h2>
        <p class="text-muted small mb-0">Inspect all registered platform members, filter roles, and toggle soft-lock account access.</p>
    </div>
    <div>
        <span class="badge bg-secondary-subtle text-secondary border px-3 py-2">
            Total Displayed: <strong><?= count($usersList) ?></strong> Accounts
        </span>
    </div>
</div>

<!-- Search & Filtering Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="users.php" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search by name or email..." value="<?= e($searchKeyword) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All Roles (Buyer / Seller / Admin)</option>
                    <option value="buyer" <?= ($filterRole === 'buyer') ? 'selected' : '' ?>>Buyers Only</option>
                    <option value="seller" <?= ($filterRole === 'seller') ? 'selected' : '' ?>>Sellers Only</option>
                    <option value="admin" <?= ($filterRole === 'admin') ? 'selected' : '' ?>>Admins Only</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filterStatus === 'active') ? 'selected' : '' ?>>Active Only</option>
                    <option value="locked" <?= ($filterStatus === 'locked') ? 'selected' : '' ?>>Locked Only</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <a href="users.php" class="btn btn-outline-secondary" title="Reset Filters">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Profile</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined Date</th>
                    <th class="text-end">Account Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usersList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-person-x fs-1 d-block text-secondary mb-2"></i>
                            No users matched your search criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($usersList as $u): ?>
                        <tr>
                            <td class="font-monospace text-muted small">#<?= (int)$u['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= e($u['avatar_url'] ?: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=80&q=80') ?>" class="rounded-circle me-3 border" width="38" height="38" style="object-fit: cover;" alt="Avatar">
                                    <div>
                                        <div class="fw-bold text-dark"><?= e($u['full_name']) ?></div>
                                        <?php if ((int)$u['id'] === (int)$adminUser['id']): ?>
                                            <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;">CURRENT ADMIN</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="font-monospace small text-dark"><?= e($u['email']) ?></td>
                            <td>
                                <?php
                                $roleBadge = match ($u['role']) {
                                    'admin' => 'bg-danger text-white',
                                    'seller' => 'bg-success text-white',
                                    default => 'bg-primary-subtle text-primary border border-primary-subtle'
                                };
                                ?>
                                <span class="badge <?= $roleBadge ?> text-uppercase px-2 py-1">
                                    <?= e($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="bi bi-lock-fill me-1"></i> Locked
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= date('M d, Y', strtotime($u['created_at'])) ?>
                            </td>
                            <td class="text-end">
                                <?php if ((int)$u['id'] === (int)$adminUser['id']): ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="You cannot lock your own account">
                                        <i class="bi bi-shield-shaded"></i> Self
                                    </button>
                                <?php else: ?>
                                    <form action="users.php" method="POST" class="d-inline" onsubmit="return confirm('Confirm soft status change for <?= e($u['full_name']) ?>?');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                        <?php if ($u['status'] === 'active'): ?>
                                            <input type="hidden" name="new_status" value="locked">
                                            <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-lock-fill"></i> Lock Account
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_status" value="active">
                                            <button type="submit" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-unlock-fill"></i> Unlock Account
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
