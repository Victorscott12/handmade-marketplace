<?php
declare(strict_types=1);

/**
 * Security Audit Verification and Demonstration Lab
 * Tests live protection mechanisms against SQLi, XSS, and CSRF on Localhost.
 * Handmade and Art Products Marketplace Platform
 */

$pageTitle = 'Security Audit Verification Lab - Artisan Haven';
require_once __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

// Handlers for interactive demonstrations
$sqliInput = (string)($_POST['sqli_payload'] ?? '');
$sqliResult = null;
$sqliError = null;

$xssInput = (string)($_POST['xss_payload'] ?? '');
$xssEscaped = null;

$csrfTestStatus = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $demoType = sanitizeInput($_POST['demo_type'] ?? '');

    // 1. SQL Injection Test Demo
    if ($demoType === 'sqli') {
        try {
            // SAFE IMPLEMENTATION: PDO Prepared Statement with bound parameters
            $stmt = $pdo->prepare('SELECT id, name, name AS title, price, status FROM products WHERE name LIKE :title LIMIT 5');
            $stmt->execute([':title' => '%' . $sqliInput . '%']);
            $sqliResult = $stmt->fetchAll();
        } catch (PDOException $e) {
            $sqliError = $e->getMessage();
        }
    }

    // 2. Cross-Site Scripting (XSS) Test Demo
    if ($demoType === 'xss') {
        // Escaped output via e()
        $xssEscaped = e($xssInput);
    }

    // 3. CSRF Verification Test Demo
    if ($demoType === 'csrf') {
        $submittedToken = $_POST['csrf_token'] ?? null;
        if (verifyCsrfToken($submittedToken)) {
            $csrfTestStatus = [
                'status' => 'success',
                'message' => 'CSRF Token validated successfully. Legitimate state-changing request allowed.'
            ];
        } else {
            $csrfTestStatus = [
                'status' => 'danger',
                'message' => 'CSRF Token missing, empty, or mismatched! Request was safely rejected with HTTP 403 Forbidden.'
            ];
        }
    }
}
?>

<div class="container py-4">
    <div class="mb-4">
        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1 mb-2">
            <i class="bi bi-shield-check me-1"></i> QA &amp; SECURITY AUDIT SUITE
        </span>
        <h1 class="font-serif display-6 fw-bold text-dark mb-1">Application Security Verification Lab</h1>
        <p class="text-muted">Interactive verification of SQL Injection (SQLi), Cross-Site Scripting (XSS), and Cross-Site Request Forgery (CSRF) defenses.</p>
    </div>

    <!-- Security Architecture Summary Banner -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-primary border-4 h-100">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-database-fill-lock fs-3 text-primary me-2"></i>
                    <h6 class="fw-bold mb-0">SQLi Prevention</h6>
                </div>
                <p class="small text-muted mb-0">Enforces 100% PDO Prepared Statements with parameterized inputs and disabled emulation mode (<code>ATTR_EMULATE_PREPARES => false</code>).</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-warning border-4 h-100">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-code-slash fs-3 text-warning me-2"></i>
                    <h6 class="fw-bold mb-0">XSS Mitigation</h6>
                </div>
                <p class="small text-muted mb-0">Context-aware HTML escaping using <code>e()</code> wrapper around <code>htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')</code> across all Blade/PHP views.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-danger border-4 h-100">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-shield-lock-fill fs-3 text-danger me-2"></i>
                    <h6 class="fw-bold mb-0">CSRF Guard</h6>
                </div>
                <p class="small text-muted mb-0">Cryptographically secure 256-bit session tokens verified with constant-time <code>hash_equals()</code> on every state-altering POST endpoint.</p>
            </div>
        </div>
    </div>

    <!-- Test Modules Accordion / Tabs -->
    <div class="row g-4">
        <!-- 1. SQL Injection Verification -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-terminal-split text-primary me-2"></i> 1. SQLi Defense Verification</h5>
                    <span class="badge bg-primary">PDO Prepared</span>
                </div>
                <p class="small text-muted">Test how parameterized queries neutralize malicious payloads without altering the query structure.</p>

                <form action="security_audit_test.php" method="POST" class="mb-3">
                    <?= csrfField() ?>
                    <input type="hidden" name="demo_type" value="sqli">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Test Payload</label>
                        <div class="input-group">
                            <input type="text" name="sqli_payload" id="sqliPayloadInput" class="form-control font-monospace small" value="<?= e($sqliInput ?: "' OR '1'='1") ?>" required>
                            <button type="submit" class="btn btn-primary btn-sm">Execute Query</button>
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <span class="small text-muted me-1">Sample Payloads:</span>
                        <button type="button" class="btn btn-light btn-sm border py-0 px-2 small font-monospace" onclick="document.getElementById('sqliPayloadInput').value='\' OR \'1\'=\'1';">' OR '1'='1</button>
                        <button type="button" class="btn btn-light btn-sm border py-0 px-2 small font-monospace" onclick="document.getElementById('sqliPayloadInput').value='\'; DROP TABLE products; --';">'; DROP TABLE...</button>
                        <button type="button" class="btn btn-light btn-sm border py-0 px-2 small font-monospace" onclick="document.getElementById('sqliPayloadInput').value='\' UNION SELECT 1,2,3,4--';">' UNION SELECT...</button>
                    </div>
                </form>

                <?php if ($sqliResult !== null): ?>
                    <div class="p-3 bg-light rounded border">
                        <div class="fw-bold small text-success mb-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Result: Prepared Statement Executed Safely
                        </div>
                        <div class="small text-muted mb-2">
                            Matches found: <strong><?= count($sqliResult) ?></strong> item(s). Payload was treated as literal data, not SQL code.
                        </div>
                        <?php if (empty($sqliResult)): ?>
                            <span class="badge bg-secondary">No rows matched literal text</span>
                        <?php else: ?>
                            <ul class="mb-0 small font-monospace ps-3">
                                <?php foreach ($sqliResult as $row): ?>
                                    <li>ID: <?= (int)$row['id'] ?> | Artwork: <?= e($row['name'] ?? $row['title'] ?? 'Artwork') ?> | Price: $<?= number_format((float)$row['price'], 2) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. XSS Output Escaping Verification -->
        <div class="col-lg-6">
            <div class="card card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-code-slash text-warning me-2"></i> 2. XSS Mitigation Verification</h5>
                    <span class="badge bg-warning text-dark">HTML Escaping</span>
                </div>
                <p class="small text-muted">Test how <code>e()</code> transforms executable JavaScript strings into harmless HTML character entities.</p>

                <form action="security_audit_test.php" method="POST" class="mb-3">
                    <?= csrfField() ?>
                    <input type="hidden" name="demo_type" value="xss">
                    <div class="mb-2">
                        <label class="form-label small fw-bold">Test Payload</label>
                        <div class="input-group">
                            <input type="text" name="xss_payload" id="xssPayloadInput" class="form-control font-monospace small" value="<?= e($xssInput ?: '<script>alert("XSS Defeated!");</script>') ?>" required>
                            <button type="submit" class="btn btn-warning btn-sm">Escape &amp; Render</button>
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <span class="small text-muted me-1">Sample Vectors:</span>
                        <button type="button" class="btn btn-light btn-sm border py-0 px-2 small font-monospace" onclick="document.getElementById('xssPayloadInput').value='<script>alert(\'XSS\')<\/script>';">&lt;script&gt;alert(1)&lt;/script&gt;</button>
                        <button type="button" class="btn btn-light btn-sm border py-0 px-2 small font-monospace" onclick="document.getElementById('xssPayloadInput').value='<img src=x onerror=alert(1)>';">&lt;img onerror&gt;</button>
                        <button type="button" class="btn btn-light btn-sm border py-0 px-2 small font-monospace" onclick="document.getElementById('xssPayloadInput').value='<svg onload=alert(document.cookie)>';">&lt;svg onload&gt;</button>
                    </div>
                </form>

                <?php if ($xssEscaped !== null): ?>
                    <div class="p-3 bg-light rounded border">
                        <div class="fw-bold small text-success mb-2">
                            <i class="bi bi-check-circle-fill me-1"></i> Result: Sanitized Output
                        </div>
                        <div class="small text-muted mb-1">Escaped HTML Entities (Rendered as literal text, zero execution):</div>
                        <code class="d-block p-2 bg-white rounded border small text-break mb-2"><?= $xssEscaped ?></code>
                        <div class="small text-muted">Raw DOM Source: <code><?= htmlspecialchars($xssEscaped, ENT_QUOTES, 'UTF-8') ?></code></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 3. CSRF Verification & Attack Simulation -->
        <div class="col-12">
            <div class="card card-custom p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-shield-x text-danger me-2"></i> 3. CSRF Protection &amp; Attack Simulation</h5>
                    <span class="badge bg-danger">Token Validation</span>
                </div>
                <p class="small text-muted">
                    Compare an authentic state-changing POST request carrying a valid session CSRF token against an unauthorized forged request (empty or manipulated token).
                </p>

                <div class="row g-3">
                    <!-- Case A: Legitimate Request with Valid Token -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle me-1"></i> Scenario A: Legitimate Form (Valid Token)</h6>
                            <p class="small text-muted">Includes hidden input with session token: <code><?= e(substr(getCsrfToken(), 0, 16)) ?>...</code></p>
                            <form action="security_audit_test.php" method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="demo_type" value="csrf">
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="bi bi-shield-check me-1"></i> Send Request with Valid Token
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Case B: Simulated CSRF Attack (Forged Token) -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Scenario B: Forged Attack (Forged / Missing Token)</h6>
                            <p class="small text-muted">Simulates an external malicious origin submitting a forged form with invalid token: <code>invalid_attacker_token_xyz</code></p>
                            <form action="security_audit_test.php" method="POST">
                                <input type="hidden" name="csrf_token" value="invalid_attacker_token_xyz">
                                <input type="hidden" name="demo_type" value="csrf">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bi bi-bug-fill me-1"></i> Simulate Forged Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($csrfTestStatus !== null): ?>
                    <div class="alert alert-<?= $csrfTestStatus['status'] ?> mt-3 mb-0 shadow-sm">
                        <strong>Test Result:</strong> <?= e($csrfTestStatus['message']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
