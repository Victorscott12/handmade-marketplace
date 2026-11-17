<?php
declare(strict_types=1);

/**
 * Product Details and Interactive Review & Rating System (Luxury Redesign)
 * Handmade and Art Products Marketplace Platform (Artisan Haven)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth_middleware.php';

$pdo = getDbConnection();
$currentUser = getCurrentUser($pdo);

// 1. Fetch Product with complete aliases
$productId = (int)($_GET['id'] ?? 1);
$stmt = $pdo->prepare("
    SELECT p.id, p.seller_id, p.category_id, p.name, p.name AS title,
           p.slug, p.description, p.price, p.stock, p.stock AS stock_quantity,
           p.image_url, p.craft_material, p.status, p.created_at,
           c.name AS category_name, c.slug AS category_slug,
           u.full_name AS seller_name, u.email AS seller_email,
           sp.store_name, sp.bio AS store_bio, sp.craft_specialty, sp.phone AS store_phone
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN seller_profiles sp ON u.id = sp.user_id
    WHERE p.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Piece Not Found | Artisan Haven';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center"><div class="card card-artisan p-5 mx-auto" style="max-width: 500px;"><i class="bi bi-palette text-muted fs-1 mb-3"></i><h4 class="font-serif fw-bold">Artwork Not Found</h4><p class="text-muted small">The handmade piece you requested does not exist or may have been unlisted.</p><a href="index.php" class="btn btn-brand-primary mx-auto">Return to Gallery</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$itemTitle = $product['name'] ?? $product['title'] ?? 'Handmade Creation';
$itemStock = (int)($product['stock'] ?? $product['stock_quantity'] ?? 0);
$itemPrice = (float)($product['price'] ?? 0.0);
$imgSrc = !empty($product['image_url']) ? $product['image_url'] : 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80';
$craftMaterial = $product['craft_material'] ?? 'Authentic Natural Materials';
$storeName = $product['store_name'] ?? $product['seller_name'] ?? 'Artisan Maker';

$pageTitle = e($itemTitle) . ' | Artisan Haven';
require_once __DIR__ . '/includes/header.php';

// 2. Fetch Review Aggregates (Average rating, total count, star breakdowns)
$aggStmt = $pdo->prepare('
    SELECT
        COUNT(*) AS total_reviews,
        COALESCE(AVG(rating), 0) AS avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS count_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS count_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS count_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS count_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS count_1
    FROM reviews
    WHERE product_id = :product_id
');
$aggStmt->execute([':product_id' => $productId]);
$stats = $aggStmt->fetch();

$totalReviews = (int)($stats['total_reviews'] ?? 0);
$avgRating = round((float)($stats['avg_rating'] ?? 0), 1);

// Star breakdown percentages
$breakdown = [];
for ($star = 5; $star >= 1; $star--) {
    $c = (int)($stats["count_{$star}"] ?? 0);
    $pct = ($totalReviews > 0) ? round(($c / $totalReviews) * 100, 1) : 0.0;
    $breakdown[$star] = [
        'count' => $c,
        'percentage' => $pct
    ];
}

// 3. User Review Status & Purchase Verification
$hasReviewed = false;
$existingUserReview = null;
$isVerifiedBuyer = false;

if ($currentUser) {
    // Check if user already reviewed
    $userRevStmt = $pdo->prepare('SELECT * FROM reviews WHERE product_id = :p_id AND user_id = :u_id LIMIT 1');
    $userRevStmt->execute([':p_id' => $productId, ':u_id' => $currentUser['id']]);
    $existingUserReview = $userRevStmt->fetch();
    $hasReviewed = (bool)$existingUserReview;

    // Check verified purchase (buyer completed or shipped order containing this product)
    $orderCheckStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.buyer_id = :user_id
          AND oi.product_id = :product_id
          AND o.status IN ('completed', 'shipped')
    ");
    $orderCheckStmt->execute([
        ':user_id' => $currentUser['id'],
        ':product_id' => $productId
    ]);
    $isVerifiedBuyer = ((int)$orderCheckStmt->fetchColumn()) > 0;
}

// 4. Paginated Reviews List (Newest first)
$reviewsPerPage = 5;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset = ($currentPage - 1) * $reviewsPerPage;
$totalPages = (int)ceil($totalReviews / $reviewsPerPage);
if ($totalPages < 1) {
    $totalPages = 1;
}

$revListStmt = $pdo->prepare("
    SELECT r.*, u.full_name AS reviewer_name,
           (SELECT COUNT(*) FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE o.buyer_id = r.user_id AND oi.product_id = r.product_id AND o.status IN ('completed', 'shipped')) AS buyer_purchase_count
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = :product_id
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
");
$revListStmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
$revListStmt->bindValue(':limit', $reviewsPerPage, PDO::PARAM_INT);
$revListStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$revListStmt->execute();
$reviewsList = $revListStmt->fetchAll();
?>

<div class="container py-4">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Marketplace</a></li>
            <li class="breadcrumb-item"><a href="index.php?category=<?= e($product['category_slug']) ?>" class="text-decoration-none text-muted"><?= e($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= e($itemTitle) ?></li>
        </ol>
    </nav>

    <!-- Main Product Showcase Grid -->
    <div class="row g-4 g-lg-5 mb-5 align-items-start">
        <!-- Left: Image Gallery Card -->
        <div class="col-lg-6">
            <div class="card card-artisan overflow-hidden border-0 shadow-sm p-2 bg-white">
                <div class="position-relative overflow-hidden rounded-3" style="height: 480px; background-color: #f7f3ed;">
                    <img src="<?= e($imgSrc) ?>"
                         alt="<?= e($itemTitle) ?>"
                         class="w-100 h-100"
                         style="object-fit: cover;"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80';">

                    <span class="position-absolute top-0 start-0 m-3 badge-craft shadow-sm">
                        <?= e($product['category_name']) ?>
                    </span>
                    <span class="position-absolute bottom-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill small" style="backdrop-filter: blur(4px);">
                        <i class="bi bi-gem me-1 text-warning"></i><?= e($craftMaterial) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Right: Purchase Details & Story -->
        <div class="col-lg-6">
            <div class="ps-lg-2">
                <!-- Maker info small -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="small text-muted text-uppercase fw-bold tracking-wide">
                        <i class="bi bi-shop me-1 text-warning"></i><?= e($storeName) ?>
                    </span>
                    <span class="text-muted">&bull;</span>
                    <?php if ($product['status'] === 'approved'): ?>
                        <span class="badge-verified"><i class="bi bi-patch-check-fill me-1"></i>Curated Artisan Piece</span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border px-2 py-1 small">Pending Admin Review</span>
                    <?php endif; ?>
                </div>

                <!-- Product Name -->
                <h1 class="font-serif display-5 fw-bold text-dark mb-2" style="line-height: 1.2;">
                    <?= e($itemTitle) ?>
                </h1>

                <!-- Star Rating Jump Link -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="star-filled fs-5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= ($i <= round($avgRating)) ? '-fill' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="fw-bold fs-5 text-dark"><?= ($avgRating > 0) ? number_format($avgRating, 1) : 'No ratings yet' ?></span>
                    <a href="#reviews" class="text-muted small text-decoration-underline ms-1">
                        (<?= $totalReviews ?> <?= ($totalReviews === 1) ? 'collector review' : 'collector reviews' ?>)
                    </a>
                </div>

                <!-- Price Tag -->
                <div class="d-flex align-items-baseline gap-2 mb-4">
                    <span class="display-6 fw-bold text-dark font-monospace">$<?= number_format($itemPrice, 2) ?></span>
                    <span class="text-muted small">Taxes included &bull; Ships directly from maker studio</span>
                </div>

                <!-- Story / Description -->
                <div class="p-3 bg-white rounded-3 border mb-4" style="border-color: #ede4d8 !important;">
                    <h6 class="fw-bold text-dark small text-uppercase mb-2"><i class="bi bi-feather me-1 text-warning"></i> Artisan Craftsmanship &amp; Story</h6>
                    <p class="text-secondary small mb-0 leading-relaxed">
                        <?= nl2br(e($product['description'] ?? 'Every single piece is individually created by the artist. Minor surface irregularities and organic glaze variations reflect the authentic human touch.')) ?>
                    </p>
                </div>

                <!-- Stock & Purchase Box -->
                <div class="card card-artisan p-4 border-0 mb-4 bg-white shadow-xs">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small fw-bold text-muted text-uppercase">Inventory Availability:</span>
                        <?php if ($itemStock > 0): ?>
                            <span class="badge-verified"><i class="bi bi-check-circle-fill me-1"></i> In Stock (<?= $itemStock ?> available)</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger px-3 py-1"><i class="bi bi-hourglass-split me-1"></i> Made to Order / Backordered</span>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-brand-primary py-3 fs-6 d-flex align-items-center justify-content-center gap-2"
                                type="button"
                                onclick="alert('Demo Cart: Artwork item added to your shopping bag.');">
                            <i class="bi bi-bag-heart-fill fs-5"></i>
                            <span>Purchase Artwork ($<?= number_format($itemPrice, 2) ?>)</span>
                        </button>
                        <a href="#review-form-section" class="btn btn-outline-secondary py-2 small rounded-pill">
                            <i class="bi bi-pencil-square me-1"></i> Write a Customer Review
                        </a>
                    </div>
                </div>

                <!-- Artisan Studio Card -->
                <div class="card card-artisan p-3 border-0 bg-white shadow-xs">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center text-warning fs-3 me-3" style="width: 52px; height: 52px;">
                            <i class="bi bi-palette"></i>
                        </div>
                        <div>
                            <div class="small text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">Independent Studio Maker</div>
                            <div class="fw-bold text-dark fs-6"><?= e($storeName) ?></div>
                            <p class="small text-muted mb-0"><?= e($product['store_bio'] ?? 'Dedicated to reviving traditional handmade techniques with modern aesthetics.') ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <hr class="my-5" style="border-color: #e5ded4;">

    <!-- ===================================================================== -->
    <!-- REVIEWS & STAR RATING SECTION -->
    <!-- ===================================================================== -->
    <div id="reviews" class="pt-3">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="font-serif fw-bold text-dark mb-1">Customer Reviews &amp; Star Ratings</h2>
                <p class="text-muted small mb-0">Genuine feedback and rating breakdown from collectors.</p>
            </div>
            <a href="#review-form-section" class="btn btn-brand-primary btn-sm">
                <i class="bi bi-chat-heart-fill me-1"></i> Review This Artwork
            </a>
        </div>

        <!-- Rating Overview Breakdown Bar Card -->
        <div class="card card-artisan p-4 p-md-5 mb-5 border-0 shadow-sm bg-white">
            <div class="row align-items-center g-4">
                <!-- Left: Overall Score -->
                <div class="col-md-4 text-center border-md-end" style="border-color: #ede4d8 !important;">
                    <div class="display-3 fw-bold text-dark mb-1"><?= ($avgRating > 0) ? number_format($avgRating, 1) : '0.0' ?></div>
                    <div class="star-filled fs-4 mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= ($i <= round($avgRating)) ? '-fill' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="text-muted small">Based on <strong><?= $totalReviews ?></strong> verified rating(s)</div>
                </div>

                <!-- Right: 5 to 1 Star Breakdown Progress Bars -->
                <div class="col-md-8">
                    <div class="px-md-3">
                        <?php foreach ($breakdown as $starNum => $bar): ?>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span class="text-nowrap small fw-bold text-muted" style="width: 55px;">
                                    <?= $starNum ?> <i class="bi bi-star-fill star-filled"></i>
                                </span>
                                <div class="progress flex-grow-1" style="height: 10px; background-color: #f1ede8; border-radius: 20px;">
                                    <div class="progress-bar"
                                         role="progressbar"
                                         style="width: <?= $bar['percentage'] ?>%; background: linear-gradient(90deg, #e58a3a, #c45d3c); border-radius: 20px;"
                                         aria-valuenow="<?= $bar['percentage'] ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <span class="small text-muted font-monospace text-end" style="width: 80px;">
                                    <?= $bar['count'] ?> (<?= $bar['percentage'] ?>%)
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Review Submission Form Section -->
        <div id="review-form-section" class="card card-artisan p-4 p-md-5 mb-5 border-0 bg-white shadow-sm">
            <h4 class="font-serif fw-bold text-dark mb-3">
                <i class="bi bi-pencil-fill text-warning me-2"></i> Share Your Experience
            </h4>

            <?php if (!$currentUser): ?>
                <!-- Not Logged In State -->
                <div class="alert alert-info border-0 d-flex align-items-center justify-content-between p-3 rounded-3" style="background-color: #eaf3fa;" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-primary fs-4 me-3"></i>
                        <div>
                            <strong>Please sign in to rate this artwork</strong>
                            <div class="small text-muted">You can use our 1-click Demo Persona switcher to sign in instantly.</div>
                        </div>
                    </div>
                    <a href="login.php" class="btn btn-brand-primary btn-sm px-3 text-nowrap">Sign In / Switch Account</a>
                </div>
            <?php elseif ($hasReviewed): ?>
                <!-- Already Reviewed State (Duplicate Prevention) -->
                <div class="alert alert-success border-0 p-4 rounded-3" style="background-color: #e8f5ed;" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
                        <strong class="fs-6">You have already submitted a review for this artwork!</strong>
                    </div>
                    <p class="small text-muted mb-3">Platform policy allows one review per collector per product to maintain authentic rating integrity.</p>
                    <div class="p-3 bg-white rounded-3 border">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="star-filled">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= ($i <= (int)$existingUserReview['rating']) ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </span>
                            <span class="small text-muted font-monospace"><?= date('M d, Y', strtotime($existingUserReview['created_at'])) ?></span>
                        </div>
                        <div class="fst-italic text-dark">&ldquo;<?= e($existingUserReview['comment']) ?>&rdquo;</div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Review Form for Authenticated User -->
                <?php if ($isVerifiedBuyer): ?>
                    <div class="badge-verified d-inline-block mb-3 p-2">
                        <i class="bi bi-patch-check-fill me-1"></i> Verified Buyer Confirmed: You purchased this piece in a completed order.
                    </div>
                <?php else: ?>
                    <div class="badge bg-secondary-subtle text-secondary border d-inline-block mb-3 p-2">
                        <i class="bi bi-eye me-1"></i> Community Review: You are reviewing as a registered member.
                    </div>
                <?php endif; ?>

                <form action="submit_review.php" method="POST" id="productReviewForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    <input type="hidden" name="rating" id="selectedRatingInput" value="5">

                    <!-- Interactive Star Selector -->
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-1">Select Star Rating <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="interactive-star-group fs-2" id="starSelector" style="cursor: pointer;">
                                <i class="bi bi-star-fill text-warning star-choice" data-star="1"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="2"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="3"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="4"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="5"></i>
                            </div>
                            <span id="ratingDescriptor" class="fw-bold text-dark ms-2 fs-6">5 Stars (Masterpiece / Excellent)</span>
                        </div>
                    </div>

                    <!-- Comment Input -->
                    <div class="mb-4">
                        <label for="reviewComment" class="form-label fw-bold">Your Review &amp; Feedback <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="reviewComment"
                                  name="comment"
                                  rows="4"
                                  placeholder="Describe the craftsmanship, tactile finish, packaging, and your overall impression..."
                                  required></textarea>
                        <div class="form-text small">Constructive reviews help other collectors and support artisan creators.</div>
                    </div>

                    <button type="submit" class="btn btn-brand-primary px-4 py-2">
                        <i class="bi bi-send-fill me-1"></i> Submit Review
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Paginated List of Customer Reviews -->
        <h4 class="font-serif fw-bold text-dark mb-4">Collector Testimonials (<?= $totalReviews ?>)</h4>

        <?php if (empty($reviewsList)): ?>
            <div class="card card-artisan p-5 text-center text-muted border-0 bg-white">
                <i class="bi bi-chat-square-heart fs-1 text-secondary opacity-50 mb-2"></i>
                <h5 class="fw-bold">No Reviews Yet</h5>
                <p class="small mb-0">Be the first to share your thoughts on this handmade creation!</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($reviewsList as $rev): ?>
                    <div class="card card-artisan p-4 border-0 bg-white shadow-xs">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-muted me-3" style="width: 44px; height: 44px;">
                                    <?= e(strtoupper(substr($rev['reviewer_name'] ?? 'U', 0, 1))) ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <span><?= e($rev['reviewer_name']) ?></span>
                                        <?php if ((int)$rev['buyer_purchase_count'] > 0): ?>
                                            <span class="badge-verified" style="font-size: 0.68rem;">
                                                <i class="bi bi-patch-check-fill me-1"></i> Verified Buyer
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="star-filled small">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= ($i <= (int)$rev['rating']) ? '-fill' : '' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <span class="text-muted small font-monospace">
                                <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                            </span>
                        </div>
                        <p class="text-secondary mb-0 leading-relaxed ps-md-5 pt-1">
                            <?= nl2br(e($rev['comment'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination Controls -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Review pagination" class="d-flex justify-content-center">
                    <ul class="pagination">
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?id=<?= $productId ?>&page=<?= $currentPage - 1 ?>#reviews">&laquo; Previous</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <li class="page-item <?= ($p === $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="?id=<?= $productId ?>&page=<?= $p ?>#reviews"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?id=<?= $productId ?>&page=<?= $currentPage + 1 ?>#reviews">Next &raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

<!-- Interactive Star Selector Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const starSelector = document.getElementById('starSelector');
    if (!starSelector) return;

    const stars = starSelector.querySelectorAll('.star-choice');
    const input = document.getElementById('selectedRatingInput');
    const label = document.getElementById('ratingDescriptor');

    const descriptors = {
        1: '1 Star (Poor / Defective)',
        2: '2 Stars (Below Expectations)',
        3: '3 Stars (Average / Acceptable)',
        4: '4 Stars (Very Good / Pleased)',
        5: '5 Stars (Masterpiece / Excellent)'
    };

    function highlightStars(ratingValue) {
        stars.forEach(star => {
            const val = parseInt(star.getAttribute('data-star'), 10);
            if (val <= ratingValue) {
                star.classList.remove('bi-star');
                star.classList.add('bi-star-fill', 'text-warning');
            } else {
                star.classList.remove('bi-star-fill', 'text-warning');
                star.classList.add('bi-star', 'text-secondary');
            }
        });
        if (label && descriptors[ratingValue]) {
            label.textContent = descriptors[ratingValue];
        }
    }

    stars.forEach(star => {
        star.addEventListener('click', function () {
            const val = parseInt(this.getAttribute('data-star'), 10);
            input.value = val;
            highlightStars(val);
        });

        star.addEventListener('mouseenter', function () {
            const val = parseInt(this.getAttribute('data-star'), 10);
            highlightStars(val);
        });
    });

    starSelector.addEventListener('mouseleave', function () {
        const currentVal = parseInt(input.value || '5', 10);
        highlightStars(currentVal);
    });

    highlightStars(parseInt(input.value || '5', 10));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
