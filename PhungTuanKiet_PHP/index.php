<?php
declare(strict_types=1);

/**
 * Public Marketplace Catalog & Storefront (Luxury Redesign)
 * Handmade and Art Products Marketplace Platform (Artisan Haven)
 */

$pageTitle = 'Artisan Haven | Handcrafted & Fine Art Marketplace';
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

// Filters: Category & Search keyword
$selectedCategorySlug = sanitizeInput($_GET['category'] ?? '');
$searchKeyword = sanitizeInput($_GET['q'] ?? '');

// Fetch Categories with approved product counts
$catStmt = $pdo->query("
    SELECT c.*, COUNT(p.id) AS total_items
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'approved'
    GROUP BY c.id, c.name, c.slug, c.description, c.created_at
    ORDER BY c.id ASC
");
$categories = $catStmt->fetchAll();

// Build Product Query with aggregate ratings & alias compatibility
$sql = "
    SELECT p.id, p.seller_id, p.category_id, p.name, p.name AS title,
           p.slug, p.description, p.price, p.stock, p.stock AS stock_quantity,
           p.image_url, p.craft_material, p.status, p.created_at,
           c.name AS category_name, c.slug AS category_slug,
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
    $sql .= " AND (p.name LIKE :keyword OR p.description LIKE :keyword OR sp.store_name LIKE :keyword OR p.craft_material LIKE :keyword)";
    $params[':keyword'] = '%' . $searchKeyword . '%';
}

$sql .= " GROUP BY p.id, c.name, c.slug, u.full_name, sp.store_name ORDER BY p.id ASC";

$prodStmt = $pdo->prepare($sql);
$prodStmt->execute($params);
$products = $prodStmt->fetchAll();
?>

<!-- Hero Section with Luxury Artisan Aesthetics -->
<section class="py-5 bg-white border-bottom shadow-xs position-relative overflow-hidden">
    <div class="container py-4 text-center">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background-color: #f7ede5; border: 1px solid #ebd3c3;">
            <i class="bi bi-patch-check-fill text-warning"></i>
            <span class="small fw-bold text-uppercase" style="color: #9c4b2a; letter-spacing: 0.08em; font-size: 0.72rem;">Authentic Handmade Goods Only</span>
        </div>

        <h1 class="font-serif display-4 fw-bold mb-3" style="color: #1f1b18; letter-spacing: -0.02em;">
            Where Passion Meets <span style="color: var(--brand-terracotta); font-style: italic;">Craftsmanship</span>
        </h1>
        <p class="lead text-secondary mx-auto mb-4" style="max-width: 660px; font-size: 1.125rem;">
            Discover original wheel-thrown ceramics, botanical-dyed knits, hand-carved hardwoods, and fine bespoke art made with love by independent artisans.
        </p>

        <!-- Search Bar with Elegant Styling -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8 col-lg-6">
                <form action="index.php" method="GET" class="position-relative">
                    <?php if (!empty($selectedCategorySlug)): ?>
                        <input type="hidden" name="category" value="<?= e($selectedCategorySlug) ?>">
                    <?php endif; ?>
                    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border" style="border-color: #e5ded4 !important;">
                        <span class="input-group-text bg-white border-0 ps-4 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text"
                               name="q"
                               class="form-control border-0 bg-white ps-2 fs-6 shadow-none"
                               placeholder="Search pottery, hand-knit blankets, walnut spoons..."
                               value="<?= e($searchKeyword) ?>">
                        <button class="btn btn-brand-primary px-4 me-1 my-1" type="submit">
                            Search Art
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4 Value Proposition Highlights -->
        <div class="row g-3 justify-content-center mt-3 pt-3 border-top" style="border-color: #f0e9df !important;">
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2 small text-muted">
                    <i class="bi bi-gem text-warning fs-5"></i>
                    <span class="fw-semibold text-dark">100% Handcrafted</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2 small text-muted">
                    <i class="bi bi-person-heart text-danger fs-5"></i>
                    <span class="fw-semibold text-dark">Verified Artisans</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2 small text-muted">
                    <i class="bi bi-box-seam text-primary fs-5"></i>
                    <span class="fw-semibold text-dark">Safe Art Shipping</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="d-flex align-items-center justify-content-center gap-2 small text-muted">
                    <i class="bi bi-star-fill text-warning fs-5"></i>
                    <span class="fw-semibold text-dark">Verified Reviews</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Storefront Catalog -->
<div class="container py-5">
    <!-- Category Filter Bar (Pills) -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="font-serif fw-bold text-dark mb-0">Curated Collections</h4>
            <div class="small text-muted">Browse by medium &amp; artisan specialty</div>
        </div>
        <div class="small text-muted">
            Showing <strong><?= count($products) ?></strong> handcrafted artwork(s)
        </div>
    </div>

    <!-- Category Pills Row -->
    <div class="d-flex align-items-center gap-2 overflow-auto pb-3 mb-4" style="scrollbar-width: thin;">
        <a href="index.php<?= !empty($searchKeyword) ? '?q=' . urlencode($searchKeyword) : '' ?>"
           class="btn btn-sm <?= empty($selectedCategorySlug) ? 'btn-dark' : 'btn-outline-secondary bg-white' ?> rounded-pill px-3 py-2 text-nowrap shadow-xs">
            <i class="bi bi-grid-fill me-1"></i> All Creations
        </a>
        <?php foreach ($categories as $cat): ?>
            <?php
            $isActive = ($selectedCategorySlug === $cat['slug']);
            $count = (int)$cat['total_items'];
            ?>
            <a href="index.php?category=<?= e($cat['slug']) ?><?= !empty($searchKeyword) ? '&q=' . urlencode($searchKeyword) : '' ?>"
               class="btn btn-sm <?= $isActive ? 'btn-dark' : 'btn-outline-secondary bg-white' ?> rounded-pill px-3 py-2 text-nowrap shadow-xs">
                <?= e($cat['name']) ?>
                <span class="badge <?= $isActive ? 'bg-light text-dark' : 'bg-light text-secondary' ?> ms-1 rounded-pill">
                    <?= $count ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Active Filter Notification -->
    <?php if (!empty($selectedCategorySlug) || !empty($searchKeyword)): ?>
        <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-white rounded-3 border shadow-xs">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-muted small fw-semibold">Filtered by:</span>
                <?php if (!empty($selectedCategorySlug)): ?>
                    <span class="badge bg-secondary px-3 py-2 rounded-pill">Category: <?= e($selectedCategorySlug) ?></span>
                <?php endif; ?>
                <?php if (!empty($searchKeyword)): ?>
                    <span class="badge bg-secondary px-3 py-2 rounded-pill">Keyword: &ldquo;<?= e($searchKeyword) ?>&rdquo;</span>
                <?php endif; ?>
            </div>
            <a href="index.php" class="btn btn-sm btn-outline-danger rounded-pill">
                <i class="bi bi-x-circle me-1"></i> Reset Filters
            </a>
        </div>
    <?php endif; ?>

    <!-- Product Grid -->
    <?php if (empty($products)): ?>
        <div class="card card-artisan p-5 text-center text-muted border-0 bg-white">
            <div class="mb-3">
                <i class="bi bi-palette fs-1 text-secondary opacity-50"></i>
            </div>
            <h4 class="font-serif fw-bold text-dark">No Artworks Matched Your Selection</h4>
            <p class="small text-muted mb-4">Try searching for other craft materials or explore our broad handmade categories.</p>
            <div>
                <a href="index.php" class="btn btn-brand-primary">View All Available Pieces</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php foreach ($products as $prod): ?>
                <?php
                $itemTitle = $prod['name'] ?? $prod['title'] ?? 'Artisan Creation';
                $itemStock = (int)($prod['stock'] ?? $prod['stock_quantity'] ?? 0);
                $itemPrice = (float)($prod['price'] ?? 0.0);
                $avgScore = round((float)($prod['avg_rating'] ?? 0), 1);
                $revCount = (int)($prod['review_count'] ?? 0);
                $sellerLabel = $prod['store_name'] ?? $prod['seller_name'] ?? 'Independent Artisan';
                $imgSrc = !empty($prod['image_url']) ? $prod['image_url'] : 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80';
                $material = $prod['craft_material'] ?? 'Handmade';
                ?>
                <div class="col">
                    <div class="card card-artisan h-100 d-flex flex-column">
                        <!-- Artwork Thumbnail with Badges -->
                        <div class="product-img-wrapper position-relative">
                            <img src="<?= e($imgSrc) ?>"
                                 alt="<?= e($itemTitle) ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80';">

                            <!-- Top Badges -->
                            <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1">
                                <span class="badge-craft shadow-sm">
                                    <?= e($prod['category_name']) ?>
                                </span>
                            </div>

                            <!-- In Stock / Material Badge -->
                            <div class="position-absolute bottom-0 start-0 m-2">
                                <span class="badge bg-dark bg-opacity-75 text-white small px-2 py-1 rounded-pill" style="font-size: 0.72rem; backdrop-filter: blur(4px);">
                                    <i class="bi bi-gem me-1"></i><?= e($material) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Details -->
                        <div class="card-body d-flex flex-column p-4">
                            <!-- Artisan attribution -->
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small text-muted fw-semibold text-truncate" style="max-width: 180px;">
                                    <i class="bi bi-person-fill text-warning me-1"></i><?= e($sellerLabel) ?>
                                </span>
                                <?php if ($itemStock > 0): ?>
                                    <span class="badge-verified"><i class="bi bi-check2 me-1"></i>In Stock (<?= $itemStock ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary small">Made to Order</span>
                                <?php endif; ?>
                            </div>

                            <!-- Title -->
                            <h5 class="card-title font-serif fw-bold text-dark mb-2" style="font-size: 1.15rem; line-height: 1.35;">
                                <a href="product_detail.php?id=<?= (int)$prod['id'] ?>" class="text-decoration-none text-dark stretched-link">
                                    <?= e($itemTitle) ?>
                                </a>
                            </h5>

                            <!-- Star Rating -->
                            <div class="d-flex align-items-center gap-1 mb-3">
                                <div class="star-filled small">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?= ($i <= round($avgScore)) ? '-fill' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="fw-bold small text-dark ms-1"><?= ($avgScore > 0) ? number_format($avgScore, 1) : 'New' ?></span>
                                <span class="text-muted small">(<?= $revCount ?> <?= ($revCount === 1) ? 'review' : 'reviews' ?>)</span>
                            </div>

                            <!-- Bottom Price and Details Link -->
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center" style="border-color: #f0e9df !important;">
                                <div>
                                    <span class="small text-muted d-block" style="font-size: 0.75rem;">Price</span>
                                    <span class="fs-5 fw-bold text-dark font-monospace">$<?= number_format($itemPrice, 2) ?></span>
                                </div>
                                <span class="btn btn-sm btn-brand-outline rounded-pill px-3 py-1">
                                    View Piece <i class="bi bi-arrow-right ms-1"></i>
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
