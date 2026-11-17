<?php
declare(strict_types=1);

/**
 * Modern Luxury Layout Header
 * Handmade and Art Products Marketplace Platform (Artisan Haven)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth_middleware.php';

$currentUser = getCurrentUser();
$pageTitle = $pageTitle ?? 'Artisan Haven | Curated Handmade & Art Marketplace';
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
    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-terracotta: #c45d3c;
            --brand-terracotta-dark: #a24627;
            --brand-amber: #e58a3a;
            --brand-sand: #fbf8f3;
            --brand-sand-dark: #f0e9df;
            --brand-charcoal: #1e1b18;
            --brand-muted: #6c665f;
            --card-radius: 16px;
            --card-shadow: 0 4px 20px rgba(44, 30, 20, 0.06);
            --card-shadow-hover: 0 14px 30px rgba(44, 30, 20, 0.12);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--brand-sand);
            color: var(--brand-charcoal);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            letter-spacing: -0.01em;
        }
        .font-serif {
            font-family: 'Playfair Display', Georgia, serif;
        }
        /* Top announcement bar */
        .top-banner {
            background-color: var(--brand-charcoal);
            color: #d8cfc4;
            font-size: 0.8rem;
            padding: 0.45rem 0;
            letter-spacing: 0.03em;
        }
        /* Navigation styling */
        .navbar-main {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--brand-sand-dark);
            transition: all 0.3s ease;
        }
        .navbar-brand-logo {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 800;
            font-size: 1.55rem;
            color: var(--brand-charcoal) !important;
            letter-spacing: -0.03em;
            display: flex;
            align-items: center;
        }
        .navbar-brand-logo .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--brand-terracotta), var(--brand-amber));
            color: #ffffff;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.65rem;
            box-shadow: 0 4px 10px rgba(196, 93, 60, 0.3);
            font-size: 1.15rem;
        }
        .navbar-brand-logo span.accent {
            color: var(--brand-terracotta);
        }
        .nav-link-custom {
            color: #4a453f !important;
            font-weight: 500;
            font-size: 0.925rem;
            padding: 0.5rem 0.9rem !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-link-custom:hover {
            color: var(--brand-terracotta) !important;
            background-color: rgba(196, 93, 60, 0.06);
        }
        .nav-link-custom.active {
            color: var(--brand-terracotta) !important;
            font-weight: 700;
        }
        /* Buttons */
        .btn-brand-primary {
            background: linear-gradient(135deg, var(--brand-terracotta), var(--brand-terracotta-dark));
            color: #ffffff !important;
            border: none;
            font-weight: 600;
            border-radius: 50px;
            padding: 0.6rem 1.4rem;
            box-shadow: 0 4px 14px rgba(196, 93, 60, 0.25);
            transition: all 0.25s ease;
        }
        .btn-brand-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(196, 93, 60, 0.35);
            background: linear-gradient(135deg, var(--brand-terracotta-dark), #8a361b);
        }
        .btn-brand-outline {
            border: 1.5px solid var(--brand-terracotta);
            color: var(--brand-terracotta) !important;
            font-weight: 600;
            border-radius: 50px;
            padding: 0.55rem 1.3rem;
            background: transparent;
            transition: all 0.2s ease;
        }
        .btn-brand-outline:hover {
            background: var(--brand-terracotta);
            color: #ffffff !important;
        }
        /* Cards */
        .card-artisan {
            background: #ffffff;
            border: 1px solid var(--brand-sand-dark);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
        }
        .card-artisan:hover {
            transform: translateY(-5px);
            box-shadow: var(--card-shadow-hover);
            border-color: #e0d5c5;
        }
        .card-artisan .product-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 250px;
            background-color: #f7f3ed;
        }
        .card-artisan .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .card-artisan:hover .product-img-wrapper img {
            transform: scale(1.06);
        }
        /* Badges */
        .badge-craft {
            background-color: #f5ece2;
            color: #8c5336;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 30px;
            padding: 0.35rem 0.8rem;
            letter-spacing: 0.02em;
        }
        .badge-verified {
            background-color: #e7f6ee;
            color: #1f7a46;
            font-weight: 600;
            font-size: 0.72rem;
            border-radius: 30px;
            padding: 0.3rem 0.75rem;
            border: 1px solid #c2e9d2;
        }
        .star-filled {
            color: #f59e0b;
        }
        .star-muted {
            color: #e2d9ce;
        }
        /* Footer */
        footer.site-footer {
            margin-top: auto;
            background-color: var(--brand-charcoal);
            color: #b5ada2;
            border-top: 1px solid #332d27;
        }
    </style>
</head>
<body>

<!-- Top Announcement Ribbon -->
<div class="top-banner">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-stars text-warning me-1"></i>
            <span>Curated Artisan Marketplace &bull; Every creation is 100% authentic and handmade</span>
        </div>
        <div class="d-none d-md-flex align-items-center gap-3">
            <span><i class="bi bi-shield-check text-success me-1"></i> Direct Maker Support</span>
            <span><i class="bi bi-truck text-info me-1"></i> Eco-Friendly Packaging</span>
        </div>
    </div>
</div>

<!-- Main Sticky Navbar -->
<nav class="navbar navbar-expand-lg navbar-main sticky-top py-3">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand-logo text-decoration-none" href="/LearningPHP/PhungTuanKiet_PHP/index.php">
            <span class="brand-icon"><i class="bi bi-palette2"></i></span>
            <span>Artisan<span class="accent">Haven</span></span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#artisanNav" aria-controls="artisanNav" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2 text-dark"></i>
        </button>

        <!-- Nav Items -->
        <div class="collapse navbar-collapse" id="artisanNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : '' ?>" href="/LearningPHP/PhungTuanKiet_PHP/index.php">
                        <i class="bi bi-shop me-1 text-muted"></i> Marketplace
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom <?= (basename($_SERVER['PHP_SELF']) === 'product_detail.php') ? 'active' : '' ?>" href="/LearningPHP/PhungTuanKiet_PHP/product_detail.php?id=1">
                        <i class="bi bi-star-fill text-warning me-1"></i> Product &amp; Reviews Demo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="/LearningPHP/PhungTuanKiet_PHP/admin/index.php">
                        <i class="bi bi-speedometer2 text-primary me-1"></i> Admin Panel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-custom" href="/LearningPHP/PhungTuanKiet_PHP/security_audit_test.php">
                        <i class="bi bi-shield-lock-fill text-success me-1"></i> Security Audit Lab
                    </a>
                </li>
            </ul>

            <!-- Right Profile / Demo Switcher -->
            <div class="d-flex align-items-center gap-2">
                <?php if ($currentUser): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2 rounded-pill px-3 py-1 bg-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-<?= ($currentUser['role'] === 'admin') ? 'danger' : (($currentUser['role'] === 'seller') ? 'success' : 'primary') ?> text-uppercase px-2">
                                <?= e($currentUser['role']) ?>
                            </span>
                            <span class="fw-semibold small text-dark"><?= e($currentUser['full_name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <li class="px-3 py-2 border-bottom bg-light rounded-top">
                                <div class="fw-bold text-dark"><?= e($currentUser['full_name']) ?></div>
                                <div class="small text-muted font-monospace"><?= e($currentUser['email']) ?></div>
                            </li>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/admin/index.php"><i class="bi bi-speedometer2 me-2 text-primary"></i>Admin Overview</a></li>
                                <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/admin/approvals.php"><i class="bi bi-patch-check me-2 text-warning"></i>Approvals Center</a></li>
                                <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/admin/users.php"><i class="bi bi-people me-2 text-info"></i>User Manager</a></li>
                                <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/admin/categories.php"><i class="bi bi-tags me-2 text-success"></i>Category Manager</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item py-2" href="/LearningPHP/PhungTuanKiet_PHP/login.php"><i class="bi bi-arrow-repeat me-2 text-secondary"></i>Switch Account Persona</a></li>
                            <li><a class="dropdown-item py-2 text-danger" href="/LearningPHP/PhungTuanKiet_PHP/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/LearningPHP/PhungTuanKiet_PHP/login.php" class="btn btn-brand-primary">
                        <i class="bi bi-person-circle me-1"></i> Sign In / Demo Switcher
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- System Flash Alerts -->
<div class="container mt-3">
    <?= renderFlashAlerts() ?>
</div>
<main class="flex-grow-1">
