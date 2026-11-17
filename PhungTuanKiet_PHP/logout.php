<?php
declare(strict_types=1);

/**
 * Session Termination and Logout Handler
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth_middleware.php';

logoutUser();

// Start a fresh session to deliver clean flash notification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
setFlash('info', 'You have been signed out successfully.');
header('Location: login.php');
exit;
