<?php
declare(strict_types=1);

/**
 * Security and Sanitization Helper Library
 * Provides robust CSRF Protection, XSS Output Escaping, and Flash Messaging.
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

/**
 * Escape strings for safe HTML output (XSS Mitigation)
 *
 * @param mixed $value
 * @return string
 */
function e($value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Sanitize plain string input (trim and remove control characters)
 *
 * @param mixed $value
 * @return string
 */
function sanitizeInput($value): string
{
    if (!is_string($value)) {
        return '';
    }
    $trimmed = trim($value);
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $trimmed) ?? '';
}

/**
 * Generate or retrieve an existing cryptographic CSRF token for the session
 *
 * @return string
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get active CSRF token without regeneration
 *
 * @return string
 */
function getCsrfToken(): string
{
    return generateCsrfToken();
}

/**
 * Output a hidden HTML input containing the CSRF token
 *
 * @return string
 */
function csrfField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Verify submitted CSRF token using constant-time comparison
 *
 * @param string|null $token
 * @return bool
 */
function verifyCsrfToken(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
}

/**
 * Validate CSRF token from POST request or terminate with 403 Forbidden
 *
 * @return void
 */
function requireCsrfToken(): void
{
    $submittedToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!verifyCsrfToken($submittedToken)) {
        http_response_code(403);
        die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>403 Forbidden - CSRF Verification Failed</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></head><body class="bg-light py-5"><div class="container"><div class="alert alert-danger shadow-sm"><h4><i class="bi bi-shield-x"></i> 403 Forbidden: Invalid or Missing CSRF Token</h4><p>Your session token was missing or mismatched. This prevents Cross-Site Request Forgery (CSRF) attacks.</p><a href="javascript:history.back()" class="btn btn-outline-danger btn-sm">Go Back & Try Again</a></div></div></body></html>');
    }
}

/**
 * Store a flash message for next page view
 *
 * @param string $type success|danger|warning|info
 * @param string $message
 * @return void
 */
function setFlash(string $type, string $message): void
{
    if (!isset($_SESSION['flash_messages']) || !is_array($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear flash messages
 *
 * @return array<int, array{type: string, message: string}>
 */
function getFlashMessages(): array
{
    if (!isset($_SESSION['flash_messages']) || !is_array($_SESSION['flash_messages'])) {
        return [];
    }
    $messages = $_SESSION['flash_messages'];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Render Bootstrap alert HTML for all pending flash messages
 *
 * @return string
 */
function renderFlashAlerts(): string
{
    $flashes = getFlashMessages();
    if (empty($flashes)) {
        return '';
    }

    $html = '';
    foreach ($flashes as $flash) {
        $type = e($flash['type'] ?? 'info');
        $msg = e($flash['message'] ?? '');
        $icon = match ($type) {
            'success' => 'bi-check-circle-fill',
            'danger' => 'bi-exclamation-octagon-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            default => 'bi-info-circle-fill'
        };
        $html .= <<<HTML
        <div class="alert alert-{$type} alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi {$icon} me-2 fs-5"></i>
            <div>{$msg}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
HTML;
    }
    return $html;
}
