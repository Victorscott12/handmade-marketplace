<?php
declare(strict_types=1);

/**
 * Public Marketplace Catalog & Storefront
 * Handmade and Art Products Marketplace Platform
 */

$pageTitle = 'Artisan Haven - Handmade & Art Marketplace';
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

// Filters: Category & Search keyword
$selectedCategorySlug = sanitizeInput($_GET['category'] ?? '');
$searchKeyword = sanitizeInput($_GET['q'] ?? '');

// Fetch Categories with counts
$catStmt = $pdo->query("
    SELECT c.*, COUNT(p.id) AS total_items
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'approved'
    GROUP BY c.id, c.name, c.slug, c.description, c.created_at
    ORDER BY c.name ASC
");
$categories = $catStmt->fetchAll();

// Build Product Query with aggregate ratings
$sql = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug,
           u.full_name AS seller_name, sp.store_name,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS review_count
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN seller_profiles sp ON u.id = sp.user_id
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.status = 'approved'
";
$params = [];

if (!empty($selectedCategorySlug)) {
    $sql .= " AND c.slug = :category_slug";
    $params[':category_slug'] = $selectedCategorySlug;
}

if (!empty($searchKeyword)) {
    $sql .= " AND (p.title LIKE :keyword OR p.description LIKE :keyword OR sp.store_name LIKE :keyword)";
    $params[':keyword'] = '%' . $searchKeyword . '%';
}

$sql .= " GROUP BY p.id, c.name, c.slug, u.full_name, sp.store_name ORDER BY p.id ASC";

$prodStmt = $pdo->prepare($sql);
$prodStmt->execute($params);
$products = $prodStmt->fetchAll();
?>

<!-- Hero Header Section -->
<section class="py-5 bg-white border-bottom shadow-sm">
    <div class="container text-center py-4">
        <span class="badge badge-handmade mb-2 text-uppercase font-monospace tracking-wide">
            <i class="bi bi-stars me-1 text-warning"></i> Curated Handmade Marketplace
        </span>
        <h1 class="font-serif display-4 fw-bold text-dark mb-3">Authentic Creations by Independent Artisans</h1>
        <p class="lead text-secondary mx-auto mb-4" style="max-width: 680px;">
            Explore handcrafted ceramics, original oil paintings, bespoke jewelry, and tailored textiles created with passion and verified authenticity.
        </p>

        <!-- Search Bar -->
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <form action="index.php" method="GET" class="input-group input-group-lg shadow-sm">
                    <?php if (!empty($selectedCategorySlug)): ?>
                        <input type="hidden" name="category" value="<?= e($selectedCategorySlug) ?>">
                    <?php endif; ?>
                    <input type="text" name="q" class="form-control" placeholder="Search handcrafted pottery, oil paintings, jewelry..." value="<?= e($searchKeyword) ?>">
                    <button class="btn btn-brand px-4" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Main Store Catalog -->
<div class="container py-5">
    <!-- Category Filter Tabs -->
    <div class="d-flex align-items-center gap-2 overflow-auto pb-3 mb-4">
        <a href="index.php<?= !empty($searchKeyword) ? '?q=' . urlencode($searchKeyword) : '' ?>"
           class="btn btn-sm <?= empty($selectedCategorySlug) ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill px-3 py-2 text-nowrap">
            All Categories
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="index.php?category=<?= e($cat['slug']) ?><?= !empty($searchKeyword) ? '&q=' . urlencode($searchKeyword) : '' ?>"
               class="btn btn-sm <?= ($selectedCategorySlug === $cat['slug']) ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill px-3 py-2 text-nowrap">
                <?= e($cat['name']) ?>
                <span class="badge bg-light text-dark ms-1"><?= (int)$cat['total_items'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Active Filter Indicator -->
    <?php if (!empty($selectedCategorySlug) || !empty($searchKeyword)): ?>
        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded-3 border">
            <div>
                <span class="text-muted small">Active Filters:</span>
                <?php if (!empty($selectedCategorySlug)): ?>
                    <span class="badge bg-secondary me-2">Category: <?= e($selectedCategorySlug) ?></span>
                <?php endif; ?>
                <?php if (!empty($searchKeyword)): ?>
                    <span class="badge bg-secondary me-2">Keyword: &ldquo;<?= e($searchKeyword) ?>&rdquo;</span>
                <?php endif; ?>
            </div>
            <a href="index.php" class="small text-danger text-decoration-none">
                <i class="bi bi-x-circle me-1"></i> Clear all filters
            </a>
        </div>
    <?php endif; ?>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="card card-custom p-5 text-center text-muted border-0 bg-white">
            <i class="bi bi-palette fs-1 text-secondary mb-3"></i>
            <h4 class="fw-bold text-dark">No Products Matched Your Criteria</h4>
            <p class="small text-muted mb-3">Try searching for other keywords or explore our diverse handmade categories.</p>
            <div>
                <a href="index.php" class="btn btn-brand btn-sm">Reset Catalog View</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php foreach ($products as $prod): ?>
                <?php
                $avg = round((float)$prod['avg_rating'], 1);
                $revCount = (int)$prod['review_count'];
                ?>
                <div class="col">
                    <div class="card card-custom h-100 d-flex flex-column overflow-hidden">
                        <!-- Product Thumbnail -->
                        <div class="position-relative overflow-hidden" style="height: 240px;">
                            <img src="<?= e($prod['image_url'] ?: 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=600&q=80') ?>"
                                 alt="<?= e($prod['title']) ?>"
                                 class="w-100 h-100"
                                 style="object-fit: cover;">
                            <span class="position-absolute top-0 start-0 m-2 badge badge-handmade shadow-sm">
                                <?= e($prod['category_name']) ?>
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body d-flex flex-column p-4">
                            <div class="small text-muted mb-1">
                                <i class="bi bi-person-fill text-secondary me-1"></i>
                                <?= e($prod['store_name'] ?: $prod['seller_name']) ?>
                            </div>

                            <h5 class="card-title font-serif fw-bold text-dark mb-2">
                                <a href="product_detail.php?id=<?= (int)$prod['id'] ?>" class="text-decoration-none text-dark stretched-link">
                                    <?= e($prod['title']) ?>
                                </a>
                            </h5>

                            <!-- Star Rating -->
                            <div class="d-flex align-items-center gap-1 mb-3">
                                <div class="star-filled small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= ($i <= round($avg)) ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="fw-bold small text-dark ms-1"><?= ($avg > 0) ? number_format($avg, 1) : 'New' ?></span>
                                <span class="text-muted small">(<?= $revCount ?>)</span>
                            </div>

                            <!-- Pricing & Details Button -->
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Price:</span>
                                    <span class="fs-5 fw-bold text-brand ms-1">$<?= number_format((float)$prod['price'], 2) ?></span>
                                </div>
                                <span class="btn btn-outline-brand btn-sm">
                                    View Details <i class="bi bi-arrow-right ms-1"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
