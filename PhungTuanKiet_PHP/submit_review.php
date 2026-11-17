<?php
declare(strict_types=1);

/**
 * Review Submission Controller
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth_middleware.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed: Expected POST request.');
}

// 1. Verify User Authentication
$currentUser = requireLogin('/LearningPHP/PhungTuanKiet_PHP/login.php');

// 2. Enforce CSRF Protection
requireCsrfToken();

$pdo = getDbConnection();

// 3. Extract and Validate Input
$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = sanitizeInput($_POST['comment'] ?? '');

$redirectUrl = ($productId > 0)
    ? "product_detail.php?id={$productId}#reviews"
    : 'index.php';

if ($productId <= 0) {
    setFlash('danger', 'Invalid product identifier specified.');
    header("Location: {$redirectUrl}");
    exit;
}

// Verify that the product exists and is active
$prodStmt = $pdo->prepare('SELECT id, title, status FROM products WHERE id = :id LIMIT 1');
$prodStmt->execute([':id' => $productId]);
$product = $prodStmt->fetch();

if (!$product) {
    setFlash('danger', 'The product you are attempting to review does not exist.');
    header('Location: index.php');
    exit;
}

// Validate rating score between 1 and 5
if ($rating < 1 || $rating > 5) {
    setFlash('danger', 'Please provide a valid star rating score between 1 and 5.');
    header("Location: {$redirectUrl}");
    exit;
}

// Validate comment length
if (empty($comment) || mb_strlen($comment) < 5) {
    setFlash('danger', 'Please write a meaningful review comment of at least 5 characters.');
    header("Location: {$redirectUrl}");
    exit;
}

// 4. Duplicate Review Prevention (One review per user per product)
$dupStmt = $pdo->prepare('SELECT id FROM reviews WHERE product_id = :p_id AND user_id = :u_id LIMIT 1');
$dupStmt->execute([
    ':p_id' => $productId,
    ':u_id' => $currentUser['id']
]);

if ($dupStmt->fetch()) {
    setFlash('warning', 'Integrity Notice: You have already submitted a review for this artwork.');
    header("Location: {$redirectUrl}");
    exit;
}

// 5. Optional Verified Purchase Check for Logging/Analytics
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
$isVerified = ((int)$orderCheckStmt->fetchColumn()) > 0;

// 6. Insert Review into Database via Prepared Statement
try {
    $insertStmt = $pdo->prepare('
        INSERT INTO reviews (product_id, user_id, rating, comment, created_at)
        VALUES (:product_id, :user_id, :rating, :comment, NOW())
    ');
    $insertStmt->execute([
        ':product_id' => $productId,
        ':user_id' => $currentUser['id'],
        ':rating' => $rating,
        ':comment' => $comment
    ]);

    $verifiedNotice = $isVerified ? ' (Verified Buyer)' : '';
    setFlash('success', "Thank you, <strong>" . e($currentUser['full_name']) . "</strong>! Your {$rating}-star review{$verifiedNotice} was published successfully.");
} catch (PDOException $e) {
    // Catch potential unique constraint race conditions
    if ($e->getCode() === '23000') {
        setFlash('warning', 'A review for this product has already been recorded for your account.');
    } else {
        setFlash('danger', 'Unable to record your review due to a database exception.');
    }
}

header("Location: {$redirectUrl}");
exit;
