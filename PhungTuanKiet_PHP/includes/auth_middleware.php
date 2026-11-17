<?php
declare(strict_types=1);

/**
 * Authentication and Role-Based Access Control (RBAC) Middleware
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';

/**
 * Check if a session has an authenticated user
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']);
}

/**
 * Fetch currently authenticated user record from database
 *
 * @param PDO|null $pdo
 * @return array<string, mixed>|null
 */
function getCurrentUser(?PDO $pdo = null): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $db = $pdo ?? getDbConnection();
    $userId = (int)$_SESSION['user_id'];

    $stmt = $db->prepare('SELECT id, full_name, email, role, status, avatar_url, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        // Stale session, clean up
        logoutUser();
        return null;
    }

    // Update active role and status in session
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_status'] = $user['status'];

    return $user;
}

/**
 * Enforce that the user is logged in and not locked
 */
function requireLogin(string $redirectTo = '/LearningPHP/PhungTuanKiet_PHP/login.php'): array
{
    $user = getCurrentUser();
    if (!$user) {
        setFlash('warning', 'Please sign in to access that page.');
        header("Location: {$redirectTo}");
        exit;
    }

    if ($user['status'] === 'locked') {
        logoutUser();
        setFlash('danger', 'Your account has been locked by an administrator. Please contact support.');
        header("Location: {$redirectTo}");
        exit;
    }

    return $user;
}

/**
 * Enforce Admin Role (role === 'admin')
 */
function requireAdmin(string $redirectTo = '/LearningPHP/PhungTuanKiet_PHP/login.php'): array
{
    $user = requireLogin($redirectTo);

    if ($user['role'] !== 'admin') {
        http_response_code(403);
        die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>403 Forbidden - Admin Privilege Required</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head><body class="bg-light py-5"><div class="container text-center"><div class="card shadow-sm p-5 border-0"><h1 class="display-4 text-danger mb-3"><i class="bi bi-shield-lock"></i> 403 Forbidden</h1><p class="lead">Access restricted. You do not possess administrator credentials to view this module.</p><div class="mt-4"><a href="/LearningPHP/PhungTuanKiet_PHP/index.php" class="btn btn-primary me-2">Marketplace Home</a><a href="/LearningPHP/PhungTuanKiet_PHP/login.php" class="btn btn-outline-secondary">Switch Account</a></div></div></div></body></html>');
    }

    return $user;
}

/**
 * Enforce Seller or Admin Role
 */
function requireSeller(string $redirectTo = '/LearningPHP/PhungTuanKiet_PHP/login.php'): array
{
    $user = requireLogin($redirectTo);

    if ($user['role'] !== 'seller' && $user['role'] !== 'admin') {
        http_response_code(403);
        die('Access denied: Seller privileges required.');
    }

    return $user;
}

/**
 * Log in a user by ID and refresh session
 */
function loginUserById(int $userId, ?PDO $pdo = null): bool
{
    $db = $pdo ?? getDbConnection();
    $stmt = $db->prepare('SELECT id, full_name, email, role, status FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user || $user['status'] === 'locked') {
        return false;
    }

    // Regenerate session id to protect against session fixation
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_status'] = $user['status'];
    generateCsrfToken();

    return true;
}

/**
 * Log out and destroy session
 */
function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
