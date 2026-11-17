<?php
declare(strict_types=1);

/**
 * Public Layout Header
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth_middleware.php';

$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? 'Artisan Haven - Handmade & Art Marketplace';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #8b5e3c;
            --brand-primary-dark: #6f472b;
            --brand-accent: #d97736;
            --brand-bg: #faf7f2;
            --brand-text: #2c2724;
            --brand-card-border: #ede4d8;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--brand-bg);
            color: var(--brand-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .font-serif {
            font-family: 'Playfair Display', Georgia, serif;
        }
        .navbar-brand {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--brand-primary) !important;
        }
        .btn-brand {
            background-color: var(--brand-primary);
            color: #ffffff;
            border: none;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
        }
        .btn-brand:hover {
            background-color: var(--brand-primary-dark);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .btn-outline-brand {
            border: 1.5px solid var(--brand-primary);
            color: var(--brand-primary);
            font-weight: 600;
        }
        .btn-outline-brand:hover {
            background-color: var(--brand-primary);
            color: #ffffff;
        }
        .card-custom {
            border: 1px solid var(--brand-card-border);
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(139, 94, 60, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-custom:hover {
            box-shadow: 0 10px 25px rgba(139, 94, 60, 0.1);
        }
        .badge-handmade {
            background-color: #f6e0d0;
            color: #8b5e3c;
            font-weight: 600;
            border-radius: 2rem;
            padding: 0.35rem 0.85rem;
        }
        .star-filled {
            color: #f59e0b;
        }
        .star-empty {
            color: #e2e8f0;
        }
        footer {
            margin-top: auto;
            background-color: #2c2724;
            color: #e6dfd8;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fs-4 d-flex align-items-center" href="/LearningPHP/PhungTuanKiet_PHP/index.php">
            <i class="bi bi-palette2 me-2 text-warning fs-3"></i>
            <span>Artisan<span class="text-dark">Haven</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#marketplaceNavbar" aria-controls="marketplaceNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="marketplaceNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active fw-bold text-dark' : '' ?>" href="/LearningPHP/PhungTuanKiet_PHP/index.php">
                        <i class="bi bi-shop me-1"></i> Browse Marketplace
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'product_detail.php') ? 'active fw-bold text-dark' : '' ?>" href="/LearningPHP/PhungTuanKiet_PHP/product_detail.php?id=1">
                        <i class="bi bi-star-fill text-warning me-1"></i> Reviews Demo (Item #1)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-primary fw-semibold" href="/LearningPHP/PhungTuanKiet_PHP/admin/index.php">
                        <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-success fw-semibold" href="/LearningPHP/PhungTuanKiet_PHP/security_audit_test.php">
                        <i class="bi bi-shield-check me-1"></i> Security Audit Tester
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-<?= ($currentUser['role'] === 'admin') ? 'danger' : (($currentUser['role'] === 'seller') ? 'success' : 'primary') ?> text-uppercase">
                                <?= e($currentUser['role']) ?>
                            </span>
                            <span class="fw-semibold small"><?= e($currentUser['full_name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold"><?= e($currentUser['full_name']) ?></div>
                                <div class="small text-muted"><?= e($currentUser['email']) ?></div>
                            </li>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/admin/index.php"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/admin/approvals.php"><i class="bi bi-check2-circle me-2 text-warning"></i>Approvals Center</a></li>
                                <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/admin/users.php"><i class="bi bi-people me-2 text-info"></i>User Manager</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/login.php"><i class="bi bi-arrow-repeat me-2 text-secondary"></i>Switch Account</a></li>
                            <li><a class="dropdown-item py-2 text-danger" href="/LearningPHP/PhungTuanKiet_PHP/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/LearningPHP/PhungTuanKiet_PHP/login.php" class="btn btn-outline-brand rounded-pill px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In / Demo Switcher
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- System Flash Messages -->
<div class="container mt-3">
    <?= renderFlashAlerts() ?>
</div>
<main class="flex-grow-1">
