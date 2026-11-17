# Handmade & Art Products Marketplace Platform (Artisan Haven)

A full-stack PHP & MySQL PDO e-commerce web platform engineered for independent artisans, craftsmen, and art collectors.

## 🚀 Key Features
- **Admin Control Center (`/admin/`)**:
  - Executive Overview Dashboard with real-time KPI metrics & Chart.js visualizations (Monthly gross revenue & Category distribution).
  - User Governance with soft-lock/unlock status toggle and self-lockout prevention.
  - Two-tier Approvals Center for reviewing Artisan Seller applications and Handmade Product submissions.
  - Category Taxonomy Management with relational integrity guards against deleting populated categories.
- **Product Reviews & Star Rating System**:
  - Interactive star rating selector (1 to 5 stars).
  - Single-query arithmetic mean and 5-to-1 star breakdown percentage bar.
  - Anti-gaming protection via compound unique constraint `(product_id, user_id)` preventing duplicate reviews.
  - Verified Purchase check cross-referencing completed customer orders.
- **Application Security & Hardening**:
  - 100% PDO Prepared Statements with native types (`ATTR_EMULATE_PREPARES => false`) to eliminate SQL Injection.
  - Context-aware HTML escaping (`e()`) to eliminate Stored/Reflected Cross-Site Scripting (XSS).
  - Synchronizer Token Pattern with 256-bit cryptographically secure CSRF tokens and constant-time `hash_equals()` verification.
  - HTTP-only and cookie security flags.
- **Testing & Documentation**:
  - Comprehensive 30-case QA test matrix and bug triage log in `TEST_CASES_AND_BUG_LOG.md`.
  - Full academic project documentation in `FINAL_PROJECT_REPORT.md`.

## 🛠️ Tech Stack
- **Backend:** PHP 8.2 (Pure Native, Modular OOP / Functional, PDO MySQL)
- **Database:** MySQL 8.0 / MariaDB 10.4 (Normalized 3NF relational schema)
- **Frontend:** Bootstrap 5.3, Bootstrap Icons, Chart.js, Vanilla JavaScript
- **Web Server:** Apache (XAMPP environment)

## 📦 Setup & Installation
1. Clone the repository into your web root directory (e.g., `htdocs/`):
   ```bash
   git clone <REPO_URL> PhungTuanKiet_PHP
   ```
2. Import the database schema and seed data into MySQL:
   ```bash
   mysql -u root -p < database.sql
   ```
3. Verify connection configuration in `config/database.php`.
4. Open your browser and navigate to:
   ```text
   http://localhost/PhungTuanKiet_PHP/index.php
   ```

## 👤 Demo Credentials
- **Admin:** `admin@artisan.test` (Password: `Password123@`)
- **Seller / Artisan:** `seller.emma@artisan.test` (Password: `Password123@`)
- **Verified Buyer:** `buyer.david@artisan.test` (Password: `Password123@`)

## 📄 License
Academic Course Project - Phung Tuan Kiet (2026).
