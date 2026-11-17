<?php
declare(strict_types=1);

/**
 * Database Configuration and PDO Connection Provider
 * Handmade and Art Products Marketplace Platform
 */

$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbName = 'artisan_marketplace';
$dbUser = 'root';
$dbPass = '';
$dbCharset = 'utf8mb4';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset={$dbCharset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbCharset} COLLATE utf8mb4_unicode_ci"
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failure. Please ensure MySQL service is running. Error details: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/**
 * Helper to obtain the active PDO instance
 */
function getDbConnection(): PDO
{
    global $pdo;
    return $pdo;
}
