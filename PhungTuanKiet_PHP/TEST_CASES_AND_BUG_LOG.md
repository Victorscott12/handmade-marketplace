# System Testing & Quality Assurance Test Suite

**Project Title:** Development of a Handmade and Art Products Marketplace Platform  
**Author:** Phung Tuan Kiet  
**Environment:** PHP 8.2 (PDO MySQL), Apache 2.4, Bootstrap 5.3, MySQL 8.0 / MariaDB 10.4  
**Date:** Academic Year 2026  
**Testing Scope:** Functional Integration, Role-Based Access Control (RBAC), Mathematical Logic, and Application Security (SQLi, XSS, CSRF).

---

## 1. Comprehensive System Test Cases Matrix

| Test ID | Module | Test Scenario | Input Data / Preconditions | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-AUTH-01** | Authentication | Standard Buyer Login with valid credentials | Email: `buyer.david@artisan.test`, PW: `Password123@` | Session established (`$_SESSION['user_id']`), redirects to storefront with welcome toast. | Authenticated successfully; session variables populated. | **PASS** |
| **TC-AUTH-02** | Authentication | Login attempt with invalid password | Email: `buyer.david@artisan.test`, PW: `WrongPassword999` | Login rejected, error displayed: *"Invalid email address or password combination."* | Rejected with sanitized alert; no session initialized. | **PASS** |
| **TC-AUTH-03** | Authentication | Locked account login attempt | Email: `buyer.locked@artisan.test` (`status = 'locked'`) | Login blocked with warning: *"This account has been locked by an administrator."* | Access blocked, user kept on login page. | **PASS** |
| **TC-AUTH-04** | Authentication | Direct URL access to `/admin/index.php` without authentication | Precondition: Unauthenticated browser guest | Intercepted by `requireAdmin()`, redirected to `login.php` with 403 / warning toast. | Redirected to login with flash warning. | **PASS** |
| **TC-AUTH-05** | Authentication | Buyer attempting to access Admin endpoints | Authenticated as `buyer.david@artisan.test` (`role = 'buyer'`) navigating to `/admin/users.php` | Access denied with HTTP 403 Forbidden screen: *"Access restricted. Admin credentials required."* | HTTP 403 returned, restricted access enforced. | **PASS** |
| **TC-SRCH-01** | Search & Filter | Filter products by active category | Category slug: `ceramics-pottery` | Catalog displays only products associated with category ID #1 (Handmade Stoneware, etc.). | Rendered 3 items matching Ceramics. | **PASS** |
| **TC-SRCH-02** | Search & Filter | Full-text substring search across product title & description | Keyword: `Ceramic` | Returns all approved products containing `Ceramic` in title or description. | Matched items displayed; non-matching hidden. | **PASS** |
| **TC-SRCH-03** | Search & Filter | Search with non-existent query | Keyword: `NonExistentKeywordXYZ123` | Displays clean empty state card: *"No Products Matched Your Criteria"*. | Empty UI state displayed gracefully. | **PASS** |
| **TC-SRCH-04** | Search & Filter | Pending products exclusion from public catalog | Products with `status = 'pending'` (e.g., ID #7, #8) | Pending items must NEVER appear in public storefront search or category views. | SQL filter `status = 'approved'` excludes pending items. | **PASS** |
| **TC-CART-01** | Cart & Order | Quantity and price integrity check | Product #1 ($85.00), Qty: 2 | Total amount calculates exactly to $170.00; subtotal reflects multiplication. | Mathematical computation exact: $170.00. | **PASS** |
| **TC-CART-02** | Cart & Order | Out of stock inventory indicator | Product with `stock_quantity = 0` | "Add to Bag" button disabled/flagged with *"Made to Order / Sold Out"*. | Displayed sold out alert badge. | **PASS** |
| **TC-SELL-01** | Seller Portal | Artisan registration submission | Valid store name: *"Nordic Kiln Studio"*, bio: *"Handcrafted stoneware"*, phone: `+1-555-0199` | Record inserted into `seller_profiles` with `verification_status = 'pending'`. | Profile inserted; admin approval queue notified. | **PASS** |
| **TC-SELL-02** | Seller Portal | Artisan product inventory submission | Title: *"Handmade Teapot"*, Category: `Ceramics`, Price: `45.00` | Item created with `status = 'pending'` awaiting admin review. | Item inserted in `pending` state; hidden from shop. | **PASS** |
| **TC-ADM-01** | Admin Panel | Dashboard KPI Aggregation accuracy | Queries executed against database | Cards match exact DB counts: Total Users (10), Sellers (3), Products (8), Revenue ($1,850.50). | KPIs match exact database aggregate queries. | **PASS** |
| **TC-ADM-02** | Admin Panel | Soft-lock user account toggle | Target: User #6 (`buyer.grace@artisan.test`) toggled to `locked` | Database updated to `status = 'locked'`. Grace cannot log in. User #6 is NOT deleted. | Status updated to `locked`; flash confirmation displayed. | **PASS** |
| **TC-ADM-03** | Admin Panel | Self-lock prevention for administrator | Admin User #1 clicks Lock on User #1 | Action blocked with error: *"Security Violation: You cannot lock your own administrator account."* | Blocked by safety guard; admin remains active. | **PASS** |
| **TC-ADM-04** | Admin Panel | Seller application approval | Profile #3 (`Clay & Canvas Co`) approved by Admin | `verification_status` set to `'approved'`, user role updated to `'seller'`. | Store verified, role promoted, notification rendered. | **PASS** |
| **TC-ADM-05** | Admin Panel | Product moderation approval | Product #7 (`Rustic Stoneware Mug`) approved | `products.status` updated from `'pending'` to `'approved'`. Appears in public store. | Status updated; product now searchable publicly. | **PASS** |
| **TC-ADM-06** | Admin Panel | Category deletion with active products | Target Category #1 (`Ceramics & Pottery`, contains 3 active products) | Deletion blocked with flash alert: *"Cannot delete category... contains 3 product(s)."* | Database foreign key and logic guard prevented deletion. | **PASS** |
| **TC-ADM-07** | Admin Panel | Category deletion without active products | Target: Empty newly created category (`Test Group`, 0 products) | Category successfully deleted from `categories` table. | Deletion executed cleanly with success alert. | **PASS** |
| **TC-REV-01** | Reviews & Rating | Display aggregate rating and star breakdown | Product #1 with 3 reviews (5★, 5★, 4★) | Average rating: 4.7 / 5.0. 5★: 66.7%, 4★: 33.3%, 3-1★: 0%. | Calculated: Avg = 4.7, breakdown bars rendered accurately. | **PASS** |
| **TC-REV-02** | Reviews & Rating | Interactive star rating selection | User clicks 4th star on review widget | Hidden input updates to `4`, text label updates to *"4 Stars (Very Good / Pleased)"*. | Interactive DOM listener updates input and star colors. | **PASS** |
| **TC-REV-03** | Reviews & Rating | Verified buyer badge attribution | Review by User #4 (`David Kim`, has completed order for Product #1) | Review displays green badge: *"Verified Buyer"*. | Order verification query confirms purchase; badge visible. | **PASS** |
| **TC-REV-04** | Reviews & Rating | Unverified community review attribution | Review by User #5 (`Emma Davis`, has not purchased Product #1) | Review published without verified buyer badge. | Rendered with standard user attribution. | **PASS** |
| **TC-REV-05** | Reviews & Rating | Duplicate review prevention | User #4 submits second review for Product #1 | Blocked with message: *"You have already submitted a review for this artwork."* | Prevented by both backend query and DB unique constraint. | **PASS** |
| **TC-REV-06** | Reviews & Rating | Review submission without authentication | Unauthenticated guest attempts to POST to `submit_review.php` | Blocked by `requireLogin()`, redirected to `login.php`. | Guest blocked from submitting reviews. | **PASS** |
| **TC-REV-07** | Reviews & Rating | Review score boundary validation | Submitted rating: `0` or `6` | Rejected: *"Please provide a valid star rating score between 1 and 5."* | Boundary enforced; invalid values rejected. | **PASS** |
| **TC-SEC-01** | Security Audit | SQL Injection in Search Input | Input: `' OR '1'='1` in search query | Prepared statement treats input as literal string; does not return all records or bypass auth. | Safely searched literal string `' OR '1'='1`; no SQLi. | **PASS** |
| **TC-SEC-02** | Security Audit | Stored / Reflected XSS in Review Comment | Input: `<script>alert('XSS-Exploit')</script>` in comment | Comment escaped via `e()` to `&lt;script&gt;...&lt;/script&gt;`; script does not execute in DOM. | Rendered as safe literal text; zero JavaScript execution. | **PASS** |
| **TC-SEC-03** | Security Audit | CSRF Attack Simulation | POST to `submit_review.php` with missing or forged `csrf_token` | Request halted with HTTP 403 Forbidden: *"403 Forbidden: Invalid or Missing CSRF Token"*. | HTTP 403 returned, state alteration blocked. | **PASS** |

---

## 2. Bug Triage & Resolution Log

The following defect log captures vulnerabilities, edge-case defects, and structural issues discovered and resolved during the sprint:

| Bug ID | Severity | Module | Description | Steps to Reproduce | Root Cause | Engineering Resolution & Code Fix |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **BUG-001** | **High** | Reviews | Duplicate reviews could be submitted by spamming the submit button or multiple tabs. | 1. Sign in as buyer.<br>2. Submit review on Product #1 in two tabs simultaneously. | Absence of compound unique key on `(product_id, user_id)`. | Added `UNIQUE KEY unique_user_product_review (product_id, user_id)` in database schema and pre-insert verification check in `submit_review.php`. |
| **BUG-002** | **High** | Admin Panel | Admin user was able to inadvertently lock their own account. | 1. Log in as admin.<br>2. Go to `admin/users.php`.<br>3. Click "Lock" on the admin's own row. | No ID comparison between authenticated session admin ID and target user ID. | Added safety guard: `if ($targetUserId === (int)$adminUser['id']) { setFlash('danger', 'Security Violation: Cannot lock own account.'); }` and disabled button in UI. |
| **BUG-003** | **Medium** | Admin Panel | Deleting a category with active products caused orphan records or SQL foreign key errors. | 1. Go to `admin/categories.php`.<br>2. Click Delete on `Ceramics & Pottery` (has 3 products). | Delete query was issued without checking product child counts. | Added query `SELECT COUNT(*) FROM products WHERE category_id = :id`. If count > 0, deletion is halted with an informative warning banner. |
| **BUG-004** | **High** | Security | Cross-Site Request Forgery (CSRF) vulnerability on administrative actions. | 1. Attacker crafts external HTML form submitting to `admin/users.php?action=toggle_status`. | Endpoints accepted unauthenticated POST requests without origin token validation. | Created `includes/security.php` with 256-bit cryptographic CSRF tokens, `csrfField()`, and strict `requireCsrfToken()` enforcement on all POST routes. |
| **BUG-005** | **Medium** | Reviews | Division by zero warning occurred when displaying star breakdown for products with 0 reviews. | 1. Open `product_detail.php` for a new product with 0 reviews. | Formula `($count / $totalReviews) * 100` resulted in `DivisionByZeroError`. | Added ternary guard: `$pct = ($totalReviews > 0) ? round(($c / $totalReviews) * 100, 1) : 0;`. |
| **BUG-006** | **Low** | UI / Frontend | Long review comments broke card container boundaries on mobile devices. | 1. Submit a 400-word review comment without spaces.<br>2. View on smartphone viewport. | Missing CSS word-wrap and overflow rules on review body. | Applied `text-break` and `leading-relaxed` Bootstrap classes with responsive padding. |

---

## 3. Practical Localhost Security Verification Payloads

To audit the security protections on your local development environment (`http://localhost:8080/LearningPHP/PhungTuanKiet_PHP/security_audit_test.php`), execute the following standardized payloads:

### 3.1 SQL Injection (SQLi) Audit
* **Payload 1 (Authentication Bypass / Tautology):**
  ```sql
  ' OR '1'='1
  ```
* **Payload 2 (Stacked Query Injection):**
  ```sql
  '; DROP TABLE products; --
  ```
* **Payload 3 (UNION-Based Information Disclosure):**
  ```sql
  ' UNION SELECT 1, password_hash, email, 4 FROM users --
  ```
* **Expected Defense:** All database queries utilize PDO Prepared Statements with bound parameters (`:keyword`). The SQL engine treats the payload as an inert literal string search. No extra rows are returned and database schema remains intact.

### 3.2 Cross-Site Scripting (XSS) Audit
* **Payload 1 (Standard Script Execution):**
  ```html
  <script>alert('XSS Exploit Demonstrated');</script>
  ```
* **Payload 2 (Inline Image Handler):**
  ```html
  <img src="invalid_image.jpg" onerror="alert('Cookie Stolen: ' + document.cookie)">
  ```
* **Payload 3 (SVG Vector):**
  ```html
  <svg/onload=alert('XSS')>
  ```
* **Expected Defense:** All user-supplied inputs pass through the `e()` helper function, executing `htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`. Characters like `<`, `>`, `"`, `'`, and `&` are converted into harmless HTML character entities (`&lt;`, `&gt;`, `&quot;`, `&#039;`, `&amp;`), preventing script execution.

### 3.3 Cross-Site Request Forgery (CSRF) Audit
* **Payload 1 (Forged External POST):**
  ```html
  <!-- Malicious external form targeting status alteration -->
  <form action="http://localhost:8080/LearningPHP/PhungTuanKiet_PHP/admin/users.php" method="POST">
      <input type="hidden" name="action" value="toggle_status">
      <input type="hidden" name="user_id" value="2">
      <input type="hidden" name="new_status" value="locked">
      <input type="submit" value="Click for Free Art Gift">
  </form>
  ```
* **Expected Defense:** Because the forged submission does not possess the secret, session-bound cryptographic `csrf_token`, the server executes `requireCsrfToken()`, terminates the request with **HTTP 403 Forbidden**, and leaves the user's status untouched.
