<?php
declare(strict_types=1);

/**
 * Product Details and Interactive Review & Rating System
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth_middleware.php';

$pdo = getDbConnection();
$currentUser = getCurrentUser($pdo);

// 1. Fetch Product
$productId = (int)($_GET['id'] ?? 1);
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug,
           u.full_name AS seller_name, u.email AS seller_email, u.avatar_url AS seller_avatar,
           sp.store_name, sp.bio AS store_bio
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
    $pageTitle = 'Product Not Found - Artisan Haven';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center"><div class="alert alert-warning"><h4>Product Not Found</h4><p>The handmade artwork or craft you requested does not exist or has been removed.</p><a href="index.php" class="btn btn-brand">Return to Marketplace</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($product['title']) . ' - Artisan Haven';
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
    $pct = ($totalReviews > 0) ? round(($c / $totalReviews) * 100, 1) : 0;
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
    SELECT r.*, u.full_name AS reviewer_name, u.avatar_url AS reviewer_avatar,
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
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?category=<?= e($product['category_slug']) ?>" class="text-decoration-none text-muted"><?= e($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page"><?= e($product['title']) ?></li>
        </ol>
    </nav>

    <!-- Product Showcase Row -->
    <div class="row g-4 mb-5">
        <!-- Product Image -->
        <div class="col-lg-6">
            <div class="card card-custom overflow-hidden border-0 p-2">
                <img src="<?= e($product['image_url'] ?: 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80') ?>"
                     alt="<?= e($product['title']) ?>"
                     class="img-fluid rounded-3 w-100"
                     style="max-height: 480px; object-fit: cover;">
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="ps-lg-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge badge-handmade"><?= e($product['category_name']) ?></span>
                    <?php if ($product['status'] === 'approved'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-patch-check-fill me-1"></i> Artisan Verified
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-capitalize">
                            Status: <?= e($product['status']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="font-serif display-6 fw-bold text-dark mb-2"><?= e($product['title']) ?></h1>

                <!-- Rating Quick Banner -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="star-filled fs-5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= ($i <= round($avgRating)) ? '-fill' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="fw-bold fs-5 text-dark"><?= ($avgRating > 0) ? number_format($avgRating, 1) : 'No ratings' ?></span>
                    <a href="#reviews" class="text-muted small text-decoration-underline">
                        (<?= $totalReviews ?> <?= ($totalReviews === 1) ? 'customer review' : 'customer reviews' ?>)
                    </a>
                </div>

                <div class="fs-2 fw-bold text-brand mb-3">
                    $<?= number_format((float)$product['price'], 2) ?>
                </div>

                <p class="text-secondary leading-relaxed mb-4">
                    <?= nl2br(e($product['description'] ?? 'Every handmade piece is uniquely crafted with artistic attention to detail.')) ?>
                </p>

                <!-- Stock & Cart Section -->
                <div class="p-3 bg-white rounded-3 border mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Availability:</span>
                        <?php if ((int)$product['stock_quantity'] > 0): ?>
                            <span class="text-success fw-bold"><i class="bi bi-check2 me-1"></i> In Stock (<?= (int)$product['stock_quantity'] ?> units available)</span>
                        <?php else: ?>
                            <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Made to Order / Sold Out</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-brand px-4 py-2 flex-grow-1 shadow-sm" type="button" onclick="alert('Demo Cart: Artwork item added to your shopping bag.');">
                            <i class="bi bi-bag-plus me-2"></i> Purchase Artwork
                        </button>
                        <a href="#review-form-section" class="btn btn-outline-secondary px-3 py-2">
                            <i class="bi bi-pencil-square me-1"></i> Write Review
                        </a>
                    </div>
                </div>

                <!-- Seller Profile Card -->
                <div class="card card-custom p-3 bg-light border-0">
                    <div class="d-flex align-items-center">
                        <img src="<?= e($product['seller_avatar'] ?: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100&q=80') ?>"
                             alt="Seller Avatar"
                             class="rounded-circle me-3 border"
                             width="48" height="48" style="object-fit: cover;">
                        <div>
                            <div class="small text-uppercase text-muted fw-bold" style="font-size: 0.725rem;">Artisan &amp; Maker</div>
                            <div class="fw-bold text-dark"><?= e($product['store_name'] ?: $product['seller_name']) ?></div>
                            <div class="small text-muted"><?= e($product['seller_email']) ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <hr class="my-5">

    <!-- REVIEWS & STAR RATING SECTION -->
    <div id="reviews" class="pt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="font-serif fw-bold text-dark mb-1">Customer Reviews &amp; Ratings</h2>
                <p class="text-muted small mb-0">Verified feedback from art collectors and handmade enthusiasts.</p>
            </div>
            <a href="#review-form-section" class="btn btn-outline-brand btn-sm">
                <i class="bi bi-chat-heart me-1"></i> Share Your Review
            </a>
        </div>

        <!-- Rating Overview Breakdown Bar Card -->
        <div class="card card-custom p-4 mb-5 border-0 shadow-sm">
            <div class="row align-items-center g-4">
                <!-- Left: Overall Score -->
                <div class="col-md-4 text-center border-md-end">
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
                                <div class="progress flex-grow-1" style="height: 10px; background-color: #f1ede8;">
                                    <div class="progress-bar"
                                         role="progressbar"
                                         style="width: <?= $bar['percentage'] ?>%; background-color: #d97736;"
                                         aria-valuenow="<?= $bar['percentage'] ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <span class="small text-muted font-monospace text-end" style="width: 75px;">
                                    <?= $bar['count'] ?> (<?= $bar['percentage'] ?>%)
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Review Submission Form Section -->
        <div id="review-form-section" class="card card-custom p-4 mb-5 border-0 bg-white">
            <h4 class="font-serif fw-bold text-dark mb-3">
                <i class="bi bi-pencil-fill text-warning me-2"></i> Leave a Product Review
            </h4>

            <?php if (!$currentUser): ?>
                <!-- Not Logged In State -->
                <div class="alert alert-info border-info d-flex align-items-center justify-content-between mb-0" role="alert">
                    <div>
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        Please sign in to rate this artwork and share your collector experience.
                    </div>
                    <a href="login.php" class="btn btn-primary btn-sm px-3">Sign In / Switch Demo Account</a>
                </div>
            <?php elseif ($hasReviewed): ?>
                <!-- Already Reviewed State (Duplicate Prevention) -->
                <div class="alert alert-success border-success mb-0" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                        <strong>You have already reviewed this handmade artwork!</strong>
                    </div>
                    <p class="small mb-2">Platform policy allows one review per verified customer per artwork to preserve mathematical integrity.</p>
                    <div class="p-3 bg-white rounded border">
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
                    <div class="badge bg-success-subtle text-success border border-success-subtle mb-3 p-2">
                        <i class="bi bi-bag-check-fill me-1"></i> Verified Purchase Confirmed: You purchased this handmade piece.
                    </div>
                <?php else: ?>
                    <div class="badge bg-secondary-subtle text-secondary border mb-3 p-2">
                        <i class="bi bi-eye me-1"></i> Community Review: You have not purchased this piece yet on your current account.
                    </div>
                <?php endif; ?>

                <form action="submit_review.php" method="POST" id="productReviewForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    <input type="hidden" name="rating" id="selectedRatingInput" value="5">

                    <!-- Interactive Star Selector -->
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1">Your Star Rating <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-2">
                            <div class="interactive-star-group fs-2" id="starSelector" style="cursor: pointer;">
                                <i class="bi bi-star-fill text-warning star-choice" data-star="1"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="2"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="3"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="4"></i>
                                <i class="bi bi-star-fill text-warning star-choice" data-star="5"></i>
                            </div>
                            <span id="ratingDescriptor" class="fw-bold text-brand ms-2 fs-6">5 Stars (Masterpiece / Excellent)</span>
                        </div>
                    </div>

                    <!-- Comment Input -->
                    <div class="mb-3">
                        <label for="reviewComment" class="form-label fw-bold">Your Review &amp; Experience <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="reviewComment"
                                  name="comment"
                                  rows="4"
                                  placeholder="Describe the craftsmanship, materials, color fidelity, packaging, and overall impression..."
                                  required></textarea>
                        <div class="form-text small">Your constructive review supports artisan creators and assists prospective collectors.</div>
                    </div>

                    <button type="submit" class="btn btn-brand px-4 py-2">
                        <i class="bi bi-send-fill me-1"></i> Submit Review
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Paginated List of Customer Reviews -->
        <h4 class="font-serif fw-bold text-dark mb-3">All Customer Testimonials (<?= $totalReviews ?>)</h4>

        <?php if (empty($reviewsList)): ?>
            <div class="card card-custom p-5 text-center text-muted border-0 bg-white">
                <i class="bi bi-chat-square-heart fs-1 text-secondary mb-2"></i>
                <h5 class="fw-bold">No Reviews Yet</h5>
                <p class="small mb-0">Be the first to review this handmade artwork using the form above!</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3 mb-4">
                <?php foreach ($reviewsList as $rev): ?>
                    <div class="card card-custom p-3 p-md-4 border-0 bg-white">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <img src="<?= e($rev['reviewer_avatar'] ?: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=80&q=80') ?>"
                                     class="rounded-circle me-3 border"
                                     width="44" height="44" style="object-fit: cover;" alt="Reviewer">
                                <div>
                                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                                        <span><?= e($rev['reviewer_name']) ?></span>
                                        <?php if ((int)$rev['buyer_purchase_count'] > 0): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 0.7rem;">
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
                        <p class="text-secondary mb-0 leading-relaxed ps-md-5">
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

    // Initial state
    highlightStars(parseInt(input.value || '5', 10));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
