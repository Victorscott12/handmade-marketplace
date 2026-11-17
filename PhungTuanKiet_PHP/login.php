<?php
declare(strict_types=1);

/**
 * Authentication and Persona Switcher
 * Handmade and Art Products Marketplace Platform
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth_middleware.php';

$pdo = getDbConnection();
$errors = [];

// Handle POST sign-in or quick-switch BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();

    // 1. Quick switch demo button pressed
    if (!empty($_POST['quick_login_id']) && is_numeric($_POST['quick_login_id'])) {
        $quickId = (int)$_POST['quick_login_id'];
        if (loginUserById($quickId, $pdo)) {
            $user = getCurrentUser($pdo);
            setFlash('success', 'Welcome, ' . $user['full_name'] . '! Switched to ' . strtoupper($user['role']) . ' persona.');
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $errors[] = 'Failed to switch to target account. The account may be locked or inactive.';
        }
    } else {
        // 2. Standard email/password login
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $errors[] = 'Please fill in both email and password.';
        } else {
            $stmt = $pdo->prepare('SELECT id, full_name, email, password, password AS password_hash, role, status FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            $pwdHash = (string)($user['password'] ?? $user['password_hash'] ?? '');
            if ($user && password_verify($password, $pwdHash)) {
                if ($user['status'] === 'locked') {
                    $errors[] = 'This account has been locked by an administrator. Access prohibited.';
                } else {
                    loginUserById((int)$user['id'], $pdo);
                    setFlash('success', 'Sign in successful. Welcome back, ' . $user['full_name'] . '!');
                    if ($user['role'] === 'admin') {
                        header('Location: admin/index.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                }
            } else {
                $errors[] = 'Invalid email address or password combination.';
            }
        }
    }
}

// Fetch demo persona accounts for quick buttons
$demoUsersStmt = $pdo->query("SELECT id, full_name, email, role, status FROM users WHERE id IN (1, 2, 6, 8, 9) ORDER BY FIELD(role, 'admin', 'seller', 'buyer'), id ASC");
$demoUsers = $demoUsersStmt->fetchAll();

// HTML Layout starts here AFTER all headers and redirects are completed
$pageTitle = 'Sign In & Demo Switcher - Artisan Haven';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card card-artisan p-4 p-md-5 border-0 shadow-sm bg-white">
                <div class="text-center mb-4">
                    <div class="display-6 font-serif text-dark mb-2">
                        <i class="bi bi-shield-lock-fill text-warning me-2"></i>Sign In
                    </div>
                    <p class="text-muted small">Access your Artisan Haven account or use 1-click academic demo personas below.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm border-0 rounded-3">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Standard Credentials Form -->
                <form action="login.php" method="POST" class="mb-4">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small text-uppercase">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold small text-uppercase">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>
                        <div class="form-text small">Default demo password for seeded users is <code>Password123@</code></div>
                    </div>

                    <button type="submit" class="btn btn-brand-primary w-100 py-2 fs-6">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Account
                    </button>
                </form>

                <hr class="my-4" style="border-color: #ede4d8;">

                <!-- 1-Click Persona Quick Switcher -->
                <div class="mb-2">
                    <h6 class="fw-bold text-uppercase small text-muted text-center tracking-wide mb-3">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> 1-Click Academic Persona Switcher
                    </h6>
                    <div class="d-grid gap-2">
                        <?php foreach ($demoUsers as $du): ?>
                            <?php
                            $roleColor = match ($du['role']) {
                                'admin' => 'danger',
                                'seller' => 'success',
                                default => 'primary'
                            };
                            $badgeLabel = strtoupper($du['role']);
                            if ($du['status'] === 'locked') {
                                $badgeLabel = 'LOCKED USER';
                                $roleColor = 'dark';
                            }
                            ?>
                            <form action="login.php" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="quick_login_id" value="<?= (int)$du['id'] ?>">
                                <button type="submit" class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 bg-white rounded-3 shadow-xs">
                                    <div>
                                        <div class="fw-bold text-dark"><?= e($du['full_name']) ?></div>
                                        <small class="text-muted font-monospace"><?= e($du['email']) ?></small>
                                    </div>
                                    <span class="badge bg-<?= $roleColor ?> text-uppercase px-2 py-1">
                                        <?= e($badgeLabel) ?>
                                    </span>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
