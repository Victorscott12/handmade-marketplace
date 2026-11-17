<?php
declare(strict_types=1);

/**
 * Admin Panel Layout Header
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/auth_middleware.php';

// Enforce role === 'admin'
$adminUser = requireAdmin('/LearningPHP/PhungTuanKiet_PHP/login.php');
$pdo = getDbConnection();

$adminTitle = $adminTitle ?? 'Admin Control Center - Artisan Haven';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle) ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 260px;
            --brand-primary: #8b5e3c;
            --admin-dark: #1e242d;
            --admin-darker: #15191f;
            --admin-active: #d97736;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f4f6f9;
            color: #2c3e50;
            overflow-x: hidden;
        }
        #sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--admin-dark);
            color: #ecf0f1;
            transition: margin 0.25s ease-out;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
        }
        #sidebar-wrapper .sidebar-brand {
            padding: 1.5rem 1.25rem;
            background-color: var(--admin-darker);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 700;
            font-size: 1.15rem;
        }
        .sidebar-nav {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 500;
            border-left: 4px solid transparent;
            transition: all 0.2s ease-in-out;
        }
        .sidebar-nav li a:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }
        .sidebar-nav li a.active {
            color: #ffffff;
            background-color: rgba(217, 119, 54, 0.15);
            border-left-color: var(--admin-active);
            font-weight: 600;
        }
        .sidebar-nav li a i {
            font-size: 1.2rem;
            margin-right: 0.85rem;
            width: 24px;
            text-align: center;
        }
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .admin-topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.85rem 1.75rem;
        }
        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            padding: 1.25rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        }
        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 0.825rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            border-top: none;
        }
        .table-custom td {
            vertical-align: middle;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div id="sidebar-wrapper">
        <div class="sidebar-brand d-flex align-items-center">
            <i class="bi bi-shield-check text-warning fs-4 me-2"></i>
            <div>
                <span class="text-white">Artisan</span><span class="text-warning">Admin</span>
                <div class="small text-muted fw-normal" style="font-size: 0.75rem;">Platform Management</div>
            </div>
        </div>

        <ul class="sidebar-nav">
            <li class="sidebar-heading px-3 pt-3 pb-1 text-uppercase text-muted small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.08em;">Core Management</li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/admin/index.php" class="<?= ($activePage === 'dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard Overview
                </a>
            </li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/admin/users.php" class="<?= ($activePage === 'users') ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i> User Management
                </a>
            </li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/admin/approvals.php" class="<?= ($activePage === 'approvals') ? 'active' : '' ?>">
                    <i class="bi bi-patch-check-fill"></i> Approvals Center
                </a>
            </li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/admin/categories.php" class="<?= ($activePage === 'categories') ? 'active' : '' ?>">
                    <i class="bi bi-tags-fill"></i> Category Manager
                </a>
            </li>

            <li class="sidebar-heading px-3 pt-4 pb-1 text-uppercase text-muted small fw-bold" style="font-size: 0.7rem; letter-spacing: 0.08em;">Quick Links</li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/index.php" target="_blank">
                    <i class="bi bi-box-arrow-up-right text-info"></i> View Marketplace
                </a>
            </li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/product_detail.php?id=1" target="_blank">
                    <i class="bi bi-star-half text-warning"></i> Reviews Demo (ID 1)
                </a>
            </li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/security_audit_test.php">
                    <i class="bi bi-shield-lock-fill text-success"></i> Security Audit Page
                </a>
            </li>
            <li>
                <a href="/LearningPHP/PhungTuanKiet_PHP/logout.php" class="text-danger">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
            </li>
        </ul>
    </div>

    <!-- Page Content Wrapper -->
    <div id="page-content-wrapper">
        <!-- Admin Topbar -->
        <header class="admin-topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 me-2 font-monospace">ROLE: ADMIN</span>
                <span class="text-muted small">Handmade &amp; Art Products Marketplace Platform</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold small text-dark"><?= e($adminUser['full_name']) ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;"><?= e($adminUser['email']) ?></div>
                </div>
                <img src="<?= e($adminUser['avatar_url'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80') ?>" alt="Admin Avatar" class="rounded-circle border" width="38" height="38" style="object-fit: cover;">
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="p-4 flex-grow-1">
            <?= renderFlashAlerts() ?>
