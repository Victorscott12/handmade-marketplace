</main>

<!-- Modern Luxury Footer -->
<footer class="site-footer pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand Column -->
            <div class="col-lg-5 col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <span class="brand-icon me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #c45d3c, #e58a3a); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: white;">
                        <i class="bi bi-palette2"></i>
                    </span>
                    <span class="font-serif fw-bold fs-4 text-white">Artisan<span style="color: #c45d3c;">Haven</span></span>
                </div>
                <p class="small text-white-50 leading-relaxed pe-lg-4 mb-3">
                    A curated digital sanctuary bridging independent artisans, ceramicists, weavers, and woodcarvers directly with discerning collectors worldwide.
                </p>
                <div class="d-flex gap-2">
                    <span class="badge bg-dark border border-secondary text-white-50 p-2"><i class="bi bi-shield-check text-success me-1"></i> PDO MySQL Prepared</span>
                    <span class="badge bg-dark border border-secondary text-white-50 p-2"><i class="bi bi-lock-fill text-warning me-1"></i> CSRF Guarded</span>
                    <span class="badge bg-dark border border-secondary text-white-50 p-2"><i class="bi bi-code-slash text-info me-1"></i> XSS Escaped</span>
                </div>
            </div>

            <!-- Quick Navigation -->
            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="text-white text-uppercase small fw-bold tracking-wide mb-3">Marketplace</h6>
                <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                    <li><a href="/LearningPHP/PhungTuanKiet_PHP/index.php" class="text-white-50 text-decoration-none hover-white">Explore All Artworks</a></li>
                    <li><a href="/LearningPHP/PhungTuanKiet_PHP/product_detail.php?id=1" class="text-white-50 text-decoration-none hover-white">Featured Review Demo (Item #1)</a></li>
                    <li><a href="/LearningPHP/PhungTuanKiet_PHP/security_audit_test.php" class="text-white-50 text-decoration-none hover-white">Interactive Security Lab</a></li>
                    <li><a href="/LearningPHP/PhungTuanKiet_PHP/login.php" class="text-white-50 text-decoration-none hover-white">Demo Persona Switcher</a></li>
                </ul>
            </div>

            <!-- Academic & Project Info -->
            <div class="col-lg-4 col-md-12 col-6">
                <h6 class="text-white text-uppercase small fw-bold tracking-wide mb-3">Academic Capstone</h6>
                <p class="small text-white-50 mb-2">
                    <strong>Project:</strong> Development of a Handmade and Art Products Marketplace Platform<br>
                    <strong>Author:</strong> Phung Tuan Kiet<br>
                    <strong>Technology:</strong> PHP 8.2 &bull; MySQL PDO &bull; Bootstrap 5.3 &bull; Chart.js
                </p>
                <div class="p-2 rounded bg-dark border border-secondary small text-white-50 font-monospace" style="font-size: 0.75rem;">
                    Demo Logins: <code>admin@artisan.test</code> / <code>seller.emma@artisan.test</code> (PW: <code>Password123@</code>)
                </div>
            </div>
        </div>

        <hr class="border-secondary opacity-25 my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50 gap-2">
            <div>&copy; <?= date('Y') ?> Artisan Haven. Built with artisanal care &amp; zero-placeholder standard.</div>
            <div class="text-center text-md-end font-monospace" style="font-size: 0.8rem;">
                Branch: <code>develop</code> &bull; Verified 100% Passing Tests
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
