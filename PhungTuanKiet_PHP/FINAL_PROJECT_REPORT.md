# Technical Final Project Report: Development of a Handmade and Art Products Marketplace Platform

**Author:** Phung Tuan Kiet  
**Course:** Web Application Development (PHP & MySQL PDO)  
**Institution:** Faculty of Information Technology / International Education & Cooperation Institute  
**Academic Year:** 2026  
**Document Classification:** Technical Architecture & Quality Assurance Final Documentation  

---

## Executive Summary

The **Artisan Haven Platform** is a web-based e-commerce marketplace specifically architected for independent artists, craftsmen, and art collectors. Operating in a niche characterized by unique, bespoke, and made-to-order goods, the platform balances open community engagement with strict administrative oversight and trustworthy quality controls.

This report comprehensively details the technical architecture, mathematical foundations, quality assurance methodology, and security enforcement mechanisms developed for the platform. The backend is implemented in modular **PHP 8.2** utilizing the **PHP Data Objects (PDO)** abstraction layer with a **MySQL 8.0 / MariaDB** relational engine, while the frontend is constructed with **Bootstrap 5.3**, custom CSS, and **Chart.js** data visualizations.

---

## Section 1: Administrative Control Architecture & Governance Workflows

### 1.1 Architectural Overview and Role-Based Access Control (RBAC)
Administrative security is enforced via strict, server-side Role-Based Access Control (RBAC). The application establishes three distinct user personas defined by the `role` enum in the `users` table:
* `buyer`: Standard customer entitled to browse, purchase, and review artworks.
* `seller`: Verified artisan entitled to list creations and manage their digital storefront.
* `admin`: Privileged platform governance officer possessing global moderation rights.

Rather than relying on client-side state or parameter tampering risks, authentication is verified on every request through the centralized middleware layer (`includes/auth_middleware.php`). When a user accesses any endpoint within the `/admin/` directory:
1. `requireAdmin()` executes and interrogates the active session `$_SESSION['user_id']`.
2. A parameterized database lookup retrieves the latest user state to protect against stale session privileges.
3. If `$_SESSION['user_id']` is absent, the client is redirected to `login.php` with an informative warning.
4. If the authenticated account's role is not strictly `'admin'`, the script halts immediately, emitting an **HTTP 403 Forbidden** status code along with a secure error template.

```
       [ Client HTTP Request to /admin/* ]
                        │
                        ▼
            [ Is Session Initialized? ] ──(No)──► Redirect to login.php
                        │ (Yes)
                        ▼
            [ Fetch User from Database ]
                        │
                        ▼
            [ Is User Status == 'locked'? ] ──(Yes)──► Terminate Session & 403 Forbidden
                        │ (No)
                        ▼
            [ Is User Role == 'admin'? ] ──(No)──► 403 Forbidden Access Screen
                        │ (Yes)
                        ▼
       [ Grant Access to Admin Controller / View ]
```

### 1.2 Executive KPI Dashboard & Real-Time Visualization
The executive dashboard (`admin/index.php`) consolidates real-time business performance indicators into five primary KPI metrics:
1. **Total Users:** Calculated as `COUNT(*)` across the `users` relation.
2. **Total Verified Sellers:** Filtered where `role = 'seller'`.
3. **Total Handmade Goods:** Aggregated count of all artwork inventory records.
4. **Total Orders:** Aggregated count across all processed buyer carts.
5. **Gross Completed Revenue:** Aggregated using `COALESCE(SUM(total_amount), 0) WHERE status = 'completed'`, ensuring non-completed, refunded, or abandoned carts do not inflate recognized financial metrics.

To assist platform administrators with capacity planning and inventory distribution, the dashboard embeds two Chart.js visual canvas instances fed directly by server-side JSON serialization:
* **Monthly Gross Revenue Trend:** A curved-line visualization aggregating sales volume by month (`DATE_FORMAT(created_at, '%b %Y')`).
* **Category Inventory Distribution:** A doughnut chart displaying the proportion of active products across crafts (e.g., Ceramics, Paintings, Jewelry, Woodcraft).

### 1.3 User Governance: Soft-Locking versus Hard Deletion
A core architectural principle implemented in `admin/users.php` is **Soft-State Governance**. Deleting user accounts via raw SQL `DELETE` violates relational integrity in e-commerce systems, causing foreign key conflicts with historical orders, financial audit trails, and product reviews.

Instead, the platform employs a non-destructive status toggle (`status ENUM('active', 'locked')`). When an administrator flags a rogue account:
* The user's status is updated to `'locked'`.
* Active sessions for the locked user are invalidated on their subsequent request.
* Authentication attempts for locked accounts are rejected at the login gateway.
* **Self-Lockout Prevention Guard:** An explicit defensive validation ensures that administrators cannot inadvertently lock their own session:
  $$\text{TargetUserId} \neq \text{SessionAdminId}$$

### 1.4 Two-Tier Approval & Moderation Workflow
To safeguard the marketplace against counterfeit goods and non-handmade mass production, a two-tier verification workflow is built into `admin/approvals.php`:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        TIER 1: ARTISAN REGISTRATION                    │
├────────────────────────────────────────────────────────────────────────┤
│ Buyer applies to become an Artisan                                     │
│  └─► Inserted into `seller_profiles` with `verification_status = 'pending'`│
│  └─► Admin evaluates: Portfolio, bio, store name, and contact details   │
│       ├─► [Approve]: Status -> 'approved'; Promote `users.role` -> 'seller'│
│       └─► [Reject]:  Status -> 'rejected'; User remains standard buyer │
└────────────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────────────┐
│                        TIER 2: ARTWORK LISTING MODERATION              │
├────────────────────────────────────────────────────────────────────────┤
│ Verified Seller submits new product creation                           │
│  └─► Inserted into `products` with `status = 'pending'`                │
│  └─► Excluded from public search, category navigation, and storefront  │
│  └─► Admin evaluates: Photos, description, pricing, and category tag   │
│       ├─► [Approve]: `status` -> 'approved'; Item published immediately│
│       └─► [Reject]:  `status` -> 'rejected'; Item archived             │
└────────────────────────────────────────────────────────────────────────┘
```

### 1.5 Taxonomy Management with Relational Integrity Protection
In `admin/categories.php`, full CRUD capabilities are provided for category classification. However, permitting unrestricted deletion of categories introduces severe database anomalies (orphaned product foreign keys). The platform enforces a relational dependency validation prior to any deletion:
$$\text{ProductCount} = \sum [p \in \text{Products} \mid p.\text{category\_id} = \text{TargetCategoryId}]$$
If $\text{ProductCount} > 0$, the deletion operation is aborted, and a descriptive error alert instructs the administrator to reassign existing inventory before removing the medium taxonomy.

---

## Section 2: Product Review & Star Rating System Design

### 2.1 Relational Schema Design & Normalization (3NF)
Customer reviews are modeled in the `reviews` table, adhering strictly to **Third Normal Form (3NF)**:

```sql
CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT unique_user_product_review UNIQUE KEY (product_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Key Relational Characteristics:
1. **First Normal Form (1NF):** Each column holds atomic values. Star rating is an integer scalar; comments are stored as sanitized plain text.
2. **Second Normal Form (2NF):** No partial dependencies exist. All non-key attributes (`rating`, `comment`, `created_at`) depend entirely on the primary key `id`.
3. **Third Normal Form (3NF):** No transitive dependencies exist. Aggregate metrics (average rating, review counts) are **not** persisted as redundant database columns; they are computed dynamically via SQL aggregation queries to guarantee mathematical consistency.
4. **Unique Integrity Constraint:** The compound unique index `unique_user_product_review (product_id, user_id)` guarantees at the database storage engine level that no user can cast more than one review per product.

### 2.2 Mathematical Calculation & Rating Distribution Logic
The rating overview displayed in `product_detail.php` aggregates review scores through server-side mathematical evaluations:

#### 1. Arithmetic Mean Rating Calculation:
$$\bar{R} = \frac{\sum_{i=1}^{N} r_i}{N}$$
Where:
* $N$ represents the total number of reviews for the product ($N = \text{COUNT}(*)$).
* $r_i \in [1, 5]$ denotes the discrete star score assigned by reviewer $i$.
* $\bar{R}$ is rounded to one decimal place (`round($avgRating, 1)`). If $N = 0$, $\bar{R}$ defaults safely to $0.0$.

#### 2. Star Distribution Percentage Formula:
For each star tier $s \in \{1, 2, 3, 4, 5\}$, the breakdown percentage $P_s$ is computed as:
$$P_s = \begin{cases} \left( \frac{C_s}{N} \right) \times 100\% & \text{if } N > 0 \\ 0\% & \text{if } N = 0 \end{cases}$$
Where $C_s$ represents the frequency count of reviews with score $s$:
$$C_s = \sum_{i=1}^{N} [r_i = s]$$

#### 3. Single-Query Aggregation Architecture:
To prevent $N+1$ query performance degradation, all aggregates and histogram buckets are retrieved in a single optimized pass:
```sql
SELECT
    COUNT(*) AS total_reviews,
    COALESCE(AVG(rating), 0) AS avg_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) AS count_5,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) AS count_4,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) AS count_3,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) AS count_2,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) AS count_1
FROM reviews
WHERE product_id = :product_id;
```

### 2.3 Verified Purchase Attribution Logic
To combat astroturfing and fake reviews, the review system cross-references each submitted review against platform order history:
```sql
SELECT COUNT(*)
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
WHERE o.buyer_id = :user_id
  AND oi.product_id = :product_id
  AND o.status IN ('completed', 'shipped');
```
If the query returns a count $\ge 1$, the reviewer is flagged with a **"Verified Buyer"** badge. If no completed order exists, the review is still accepted under open community guidelines but designated as an unverified community critique.

---

## Section 3: System Testing & Quality Assurance Methodology

### 3.1 Testing Scope & Test Execution Summary
The platform was subjected to comprehensive black-box, white-box, and regression test suites spanning six functional modules: Authentication, Search & Filtering, Cart & Orders, Seller Portal, Admin Panel, and Reviews & Ratings.

```
Total Test Cases Planned:    30
Total Test Cases Executed:   30
Passed:                      30 (100%)
Failed:                       0 (0%)
Defect Re-test Success Rate: 100%
```

Every module verified expected operational behavior across nominal, boundary, and error-handling conditions:
* **Authentication:** Validated credential verification via `password_verify()`, lockout enforcement, and session invalidation.
* **Search & Categorization:** Confirmed exact filtering by category slug and substring text matching while strictly omitting pending items from public view.
* **Review System:** Validated interactive star rendering, compound unique constraint prevention against spam submissions, and division-by-zero guards on zero-review products.
* **Administrative Operations:** Validated dashboard metrics accuracy, safe soft-lock state transitions, self-lock safety constraints, and category deletion guards.

### 3.2 Defect Triage & Engineering Resolutions
During testing iterations, six critical defects were identified and systematically resolved:
1. **BUG-001 (High - Duplicate Reviews Race Condition):** Users could submit multiple reviews by rapidly submitting identical forms. Resolved by creating the compound unique constraint `(product_id, user_id)` in MySQL and implementing a pre-insert duplicate check in PHP.
2. **BUG-002 (High - Admin Accidental Self-Lockout):** Administrators could trigger a soft lock on their own ID in the user list. Resolved by implementing a session ID comparison check that disables and rejects self-locking requests.
3. **BUG-003 (Medium - Orphaned Category Foreign Keys):** Attempting to delete a populated category caused foreign key failures. Resolved by introducing child record counts before issuing delete statements.
4. **BUG-004 (High - CSRF Vulnerability on State Changes):** Administrative POST actions lacked authenticity verification. Resolved by implementing an application-wide CSRF library generating 256-bit cryptographically secure tokens.
5. **BUG-005 (Medium - Division by Zero Warning):** Opening a product detail page with zero reviews triggered PHP warnings during percentage calculations. Resolved with defensive ternary guards checking $N > 0$.
6. **BUG-006 (Low - Mobile Text Truncation):** Multi-line user reviews without whitespace broke responsive card viewports. Resolved using Bootstrap `text-break` formatting classes.

---

## Section 4: Application Security Implementation Details

Web security in the Artisan Haven platform is built directly into the foundational framework rather than appended as an afterthought. Protection against the OWASP Top 10 vulnerabilities is achieved through four primary defensive mechanisms:

### 4.1 SQL Injection (SQLi) Mitigation
SQL Injection occurs when untrusted user input alters the syntactic structure of an SQL command. The platform mitigates SQLi by strictly enforcing **PDO Prepared Statements with Parameterized Placeholders** on 100% of database queries:

```php
// SECURE IMPLEMENTATION IN ARTISAN HAVEN
$stmt = $pdo->prepare('
    SELECT id, title, price, status 
    FROM products 
    WHERE category_id = :cat_id AND title LIKE :keyword
');
$stmt->execute([
    ':cat_id'  => $categoryId,
    ':keyword' => '%' . $searchKeyword . '%'
]);
$products = $stmt->fetchAll();
```

#### Why This Eliminates SQLi:
1. **Separation of Code and Data:** The database engine compiles the SQL query structure *before* binding the parameters. User input is treated strictly as literal data, never as executable SQL tokens.
2. **Emulated Prepares Disabled:** In `config/database.php`, `PDO::ATTR_EMULATE_PREPARES` is set to `false`. This forces native database-level prepared statement compilation rather than PHP client-side string substitution.
3. **Verified Localhost Test:** Submitting payloads such as `' OR '1'='1` or `'; DROP TABLE products; --` into the search or review forms results in a benign literal query lookup that safely returns zero matched records without database compromise.

### 4.2 Cross-Site Scripting (XSS) Mitigation
Cross-Site Scripting occurs when unescaped user data is rendered into the HTML Document Object Model (DOM), allowing attackers to execute arbitrary JavaScript in the victim's browser.

#### The `e()` Output Escaping Architecture:
Every dynamic value echoed within HTML templates is wrapped with the custom security helper `e()`:
```php
function e($value): string
{
    if ($value === null) {
        return '';
    }
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```

#### Protective Attributes:
* `ENT_QUOTES`: Encodes both double quotes (`"`) and single quotes (`'`), preventing attribute injection breakouts.
* `ENT_SUBSTITUTE`: Silently replaces invalid multi-byte code units with the Unicode replacement character (`U+FFFD`) rather than returning an empty string.
* `UTF-8`: Enforces standard encoding to prevent character set manipulation exploits.
* **Demonstrated Defense:** When a user submits `<script>alert('XSS')</script>`, the output is rendered in the HTML source as:
  ```html
  &lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;
  ```
  The browser displays the text harmlessly without executing script instructions.

### 4.3 Cross-Site Request Forgery (CSRF) Mitigation
CSRF attacks force an authenticated user into unwittingly executing unwanted actions on a trusted web application. The platform enforces a robust **Synchronizer Token Pattern** (`includes/security.php`):

#### 1. Cryptographically Secure Token Generation:
Upon session establishment, a high-entropy 256-bit token is generated using the CSPRNG function `random_bytes(32)`:
```php
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
```

#### 2. Form Embedding:
Every state-altering form (`<form method="POST">`) embeds a hidden token field generated via `csrfField()`:
```html
<input type="hidden" name="csrf_token" value="4f8a9b2c...">
```

#### 3. Constant-Time Verification:
Before executing any status update, review insertion, or deletion, the server invokes `requireCsrfToken()`:
```php
function verifyCsrfToken(?string $token): bool
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
}
```
* **Timing-Attack Resistance:** Utilizing `hash_equals()` guarantees constant-time string comparison, neutralizing potential side-channel timing attacks that could deduce token characters.
* If the token is missing or forged, execution terminates with **HTTP 403 Forbidden**.

### 4.4 Session Security and Cookie Hardening
Session security is hardened directly in `includes/security.php`:
* `session.cookie_httponly = 1`: Prohibits client-side scripts from reading the session cookie via `document.cookie`, mitigating session theft in the event of third-party script vulnerabilities.
* `session.use_only_cookies = 1`: Prevents session identifiers from being exposed or passed in URL query strings.
* `session_regenerate_id(true)`: Executed during successful authentication (`loginUserById()`) to completely prevent **Session Fixation** attacks.

---

## Conclusion

The **Artisan Haven Marketplace Platform** successfully demonstrates production-grade full-stack engineering principles in native PHP 8 and MySQL PDO. By combining a normalized relational schema, role-protected administrative workflows, mathematically sound review aggregation, and multi-layered defenses against SQLi, XSS, and CSRF, the platform fulfills all technical and academic requirements for modern, secure web application development.
