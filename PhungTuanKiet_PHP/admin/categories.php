<?php
declare(strict_types=1);

/**
 * Category Management CRUD with Relational Integrity Protection
 * Handmade and Art Products Marketplace Platform
 */

$adminTitle = 'Category Management - Artisan Admin';
$activePage = 'categories';
require_once __DIR__ . '/includes/admin_header.php';

// Handle POST operations (Create, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $action = sanitizeInput($_POST['action'] ?? '');

    // 1. CREATE CATEGORY
    if ($action === 'create') {
        $name = sanitizeInput($_POST['name'] ?? '');
        $slug = sanitizeInput($_POST['slug'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');

        if (empty($name)) {
            setFlash('danger', 'Category name is required.');
        } else {
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            }

            // Check slug uniqueness
            $check = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
            $check->execute([':slug' => $slug]);
            if ($check->fetch()) {
                setFlash('danger', "Category with slug '{$slug}' already exists.");
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description, created_at) VALUES (:name, :slug, :description, NOW())');
                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':description' => $description
                ]);
                setFlash('success', "New category <strong>" . e($name) . "</strong> has been created.");
            }
        }
        header('Location: categories.php');
        exit;
    }

    // 2. UPDATE CATEGORY
    if ($action === 'update') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = sanitizeInput($_POST['name'] ?? '');
        $slug = sanitizeInput($_POST['slug'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');

        if ($id <= 0 || empty($name)) {
            setFlash('danger', 'Invalid category data for update.');
        } else {
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            }

            // Check slug uniqueness ignoring current id
            $check = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug AND id != :id LIMIT 1');
            $check->execute([':slug' => $slug, ':id' => $id]);
            if ($check->fetch()) {
                setFlash('danger', "Another category is already using slug '{$slug}'.");
            } else {
                $stmt = $pdo->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id');
                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':id' => $id
                ]);
                setFlash('success', "Category <strong>" . e($name) . "</strong> was updated successfully.");
            }
        }
        header('Location: categories.php');
        exit;
    }

    // 3. DELETE CATEGORY WITH ACTIVE PRODUCT VALIDATION
    if ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);

        if ($id <= 0) {
            setFlash('danger', 'Invalid category identifier.');
        } else {
            // Fetch category details
            $catStmt = $pdo->prepare('SELECT id, name FROM categories WHERE id = :id LIMIT 1');
            $catStmt->execute([':id' => $id]);
            $category = $catStmt->fetch();

            if (!$category) {
                setFlash('danger', 'Category does not exist.');
            } else {
                // Relational integrity check: Count active/existing products referencing this category
                $countStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = :id');
                $countStmt->execute([':id' => $id]);
                $productCount = (int)$countStmt->fetchColumn();

                if ($productCount > 0) {
                    // Prevent deletion to preserve database integrity
                    setFlash('danger', "Integrity Constraint: Cannot delete category <strong>" . e($category['name']) . "</strong> because it still contains <strong>{$productCount}</strong> product(s). Please reassign or remove these items first.");
                } else {
                    $delStmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
                    $delStmt->execute([':id' => $id]);
                    setFlash('success', "Category <strong>" . e($category['name']) . "</strong> was safely removed.");
                }
            }
        }
        header('Location: categories.php');
        exit;
    }
}

// Fetch all categories with computed product counts
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id, c.name, c.slug, c.description, c.created_at
    ORDER BY c.id ASC
");
$categories = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1 text-dark">Category Taxonomy Management</h2>
        <p class="text-muted small mb-0">Organize art and handmade mediums with cascade deletion protection.</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-plus-lg"></i> Add New Category
    </button>
</div>

<!-- Category List Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Slug (URL Key)</th>
                    <th>Description</th>
                    <th>Linked Products</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No categories defined yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="font-monospace text-muted small">#<?= (int)$cat['id'] ?></td>
                            <td>
                                <span class="fw-bold text-dark fs-6"><?= e($cat['name']) ?></span>
                            </td>
                            <td>
                                <code class="text-primary bg-light px-2 py-1 rounded small">/category/<?= e($cat['slug']) ?></code>
                            </td>
                            <td style="max-width: 280px;">
                                <span class="small text-muted text-truncate d-inline-block" style="max-width: 280px;" title="<?= e($cat['description'] ?? '') ?>">
                                    <?= e($cat['description'] ?? 'No description provided.') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int)$cat['product_count'] > 0): ?>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                        <i class="bi bi-box-seam me-1"></i> <?= (int)$cat['product_count'] ?> Products
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                        0 Products
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= date('M d, Y', strtotime($cat['created_at'])) ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <!-- Edit Trigger Button -->
                                <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editCategoryModal<?= (int)$cat['id'] ?>"
                                        title="Edit Category">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <!-- Delete Form -->
                                <form action="categories.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete category \'<?= e($cat['name']) ?>\'?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= ((int)$cat['product_count'] > 0) ? 'btn-outline-secondary' : 'btn-outline-danger' ?>"
                                            title="<?= ((int)$cat['product_count'] > 0) ? 'Has active products (deletion blocked)' : 'Safe to delete' ?>">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal for this category -->
                        <div class="modal fade" id="editCategoryModal<?= (int)$cat['id'] ?>" tabindex="-1" aria-labelledby="editCatLabel<?= (int)$cat['id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="categories.php" method="POST" class="modal-content">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold" id="editCatLabel<?= (int)$cat['id'] ?>">
                                            <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Category
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Category Name</label>
                                            <input type="text" name="name" class="form-control" value="<?= e($cat['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">URL Slug</label>
                                            <input type="text" name="slug" class="form-control" value="<?= e($cat['slug']) ?>" required>
                                            <div class="form-text small">Unique web identifier (e.g., ceramics-pottery).</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Description</label>
                                            <textarea name="description" class="form-control" rows="3"><?= e($cat['description'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCatLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="categories.php" method="POST" class="modal-content">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="createCatLabel">
                    <i class="bi bi-plus-circle me-2 text-success"></i> Create New Craft Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g., Leathercraft &amp; Accessories" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">URL Slug (Optional)</label>
                    <input type="text" name="slug" class="form-control" placeholder="auto-generated from name if blank">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief summary of handmade goods in this group..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Create Category</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
