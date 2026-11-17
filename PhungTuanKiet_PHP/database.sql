-- ==============================================================================
-- Final Course Project: Development of a Handmade & Art Products Marketplace
-- Database Schema & Comprehensive Seed Data
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `artisan_marketplace`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `artisan_marketplace`;

-- Disable foreign key checks for clean recreation
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `seller_profiles`;
DROP TABLE IF EXISTS `users`;

-- ------------------------------------------------------------------------------
-- 1. Table: users
-- ------------------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('buyer', 'seller', 'admin') NOT NULL DEFAULT 'buyer',
    `status` ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    `avatar_url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. Table: seller_profiles
-- ------------------------------------------------------------------------------
CREATE TABLE `seller_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `store_name` VARCHAR(100) NOT NULL,
    `bio` TEXT NULL,
    `craft_specialty` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `rejection_reason` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_seller_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. Table: categories
-- ------------------------------------------------------------------------------
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT 'fa-palette',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. Table: products
-- ------------------------------------------------------------------------------
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `seller_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(160) NOT NULL,
    `description` TEXT NOT NULL,
    `price` DECIMAL(12, 2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(255) NULL,
    `craft_material` VARCHAR(100) NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `rejection_reason` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. Table: orders
-- ------------------------------------------------------------------------------
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(30) NOT NULL UNIQUE,
    `buyer_id` INT NOT NULL,
    `total_amount` DECIMAL(12, 2) NOT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `payment_status` ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'paid',
    `shipping_address` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. Table: order_items
-- ------------------------------------------------------------------------------
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `seller_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(12, 2) NOT NULL,
    `subtotal` DECIMAL(12, 2) NOT NULL,
    CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_items_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. Table: reviews
-- ------------------------------------------------------------------------------
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_product_review` (`product_id`, `user_id`),
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- Comprehensive Seed Data
-- Passwords for all seeded users: "Password123@"
-- Hash generated via password_hash('Password123@', PASSWORD_DEFAULT)
-- ==============================================================================

-- 1. Users (Admin, Sellers, Buyers)
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`, `created_at`) VALUES
(1, 'admin', 'admin@artisanmarketplace.com', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'System Administrator', 'admin', 'active', '2026-01-10 08:00:00'),
(2, 'potter_elena', 'elena@potterycraft.com', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'Elena Rostova', 'seller', 'active', '2026-01-15 09:30:00'),
(3, 'knit_clara', 'clara@yarnstudio.com', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'Clara Vance', 'seller', 'active', '2026-02-01 10:15:00'),
(4, 'wood_arthur', 'arthur@woodcraft.org', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'Arthur Pendelton', 'seller', 'active', '2026-02-10 11:20:00'),
(5, 'silver_maya', 'maya@silverjewelry.net', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'Maya Lin', 'buyer', 'active', '2026-03-01 14:00:00'), -- Applied for seller, currently pending
(6, 'buyer_john', 'john.doe@gmail.com', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'John Doe', 'buyer', 'active', '2026-03-05 15:30:00'),
(7, 'buyer_sarah', 'sarah.connor@yahoo.com', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'Sarah Connor', 'buyer', 'active', '2026-03-12 16:45:00'),
(8, 'buyer_alex', 'alex.spencer@outlook.com', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'Alex Spencer', 'buyer', 'active', '2026-03-20 18:00:00'),
(9, 'locked_user', 'spammer@fakebot.io', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'Bot Spammer', 'buyer', 'locked', '2026-03-22 03:10:00'),
(10, 'leather_david', 'david@leatherworks.com', '$2y$10$wE9sI/bF0.l6u9Q4wW1.9eC82x6F7t7/jZq98hV8O6I0hG6x0hC1W', 'David Miller', 'buyer', 'active', '2026-03-25 10:00:00'); -- Applied for seller, currently pending

-- 2. Seller Profiles
INSERT INTO `seller_profiles` (`id`, `user_id`, `store_name`, `bio`, `craft_specialty`, `phone`, `status`, `created_at`) VALUES
(1, 2, 'Elena Terra Ceramics', 'Hand-thrown stoneware ceramics inspired by Scandinavian nature and Japanese wabi-sabi aesthetics.', 'Ceramics & Pottery', '+1 (555) 234-5678', 'approved', '2026-01-15 09:35:00'),
(2, 3, 'Cozy Loops Knits', 'Sustainable hand-knitted woolen apparel, blankets, and natural-dyed accessories.', 'Knitting & Fiber Art', '+1 (555) 345-6789', 'approved', '2026-02-01 10:20:00'),
(3, 4, 'Timber & Grain Guild', 'Hand-carved wooden tableware, bowls, and ergonomic cooking utensils made of salvaged walnut and cherry.', 'Woodworking', '+1 (555) 456-7890', 'approved', '2026-02-10 11:25:00'),
(4, 5, 'Maya Fine Silver', 'Artisanal 925 sterling silver filigree and gemstone handcrafted jewelry.', 'Handmade Jewelry', '+1 (555) 567-8901', 'pending', '2026-03-01 14:05:00'),
(5, 10, 'Old Town Leather Craft', 'Hand-stitched full-grain vegetable-tanned leather journals, wallets, and bags.', 'Leathercraft', '+1 (555) 678-9012', 'pending', '2026-03-25 10:05:00');

-- 3. Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `created_at`) VALUES
(1, 'Ceramics & Pottery', 'ceramics-pottery', 'Hand-thrown stoneware, porcelain mugs, glazed vases, and handmade clay planters.', 'fa-mug-hot', '2026-01-01 00:00:00'),
(2, 'Paintings & Wall Art', 'paintings-art', 'Original oil, acrylic, watercolor paintings, and fine art linocut prints.', 'fa-palette', '2026-01-01 00:00:00'),
(3, 'Handmade Jewelry', 'handmade-jewelry', 'Artisanal rings, earrings, pendants, and wire-wrapped gemstone pieces.', 'fa-gem', '2026-01-01 00:00:00'),
(4, 'Knitting & Fiber Art', 'knitting-fiber', 'Cozy hand-knit wool scarves, macrame wall hangings, and crochet blankets.', 'fa-mitten', '2026-01-01 00:00:00'),
(5, 'Woodworking & Carving', 'woodworking-carving', 'Solid wood kitchenware, hand-carved decorative bowls, and rustic wood sculptures.', 'fa-tree', '2026-01-01 00:00:00'),
(6, 'Leather Goods', 'leather-goods', 'Hand-stitched full-grain leather wallets, bespoke notebooks, and passport covers.', 'fa-wallet', '2026-01-01 00:00:00');

-- 4. Products (Approved and Pending)
INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `image_url`, `craft_material`, `status`, `created_at`) VALUES
(1, 2, 1, 'Artisan Stoneware Speckled Ceramic Mug', 'artisan-stoneware-speckled-ceramic-mug', 'Hand-thrown on the potter wheel with high-fire speckled stoneware. Finished in a soft matte oatmeal glaze with raw toasted clay bottom. Holds approx 350ml of your favorite coffee or tea. Microwave and dishwasher safe.', 28.50, 15, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80', 'Stoneware Clay & Food-safe Glaze', 'approved', '2026-01-20 10:00:00'),
(2, 2, 1, 'Rustic Terracotta Ikebana Flower Vase', 'rustic-terracotta-ikebana-flower-vase', 'Minimalist hand-coiled ceramic vase with an organic sculpted silhouette. Designed for Japanese Ikebana single-stem arrangements. Sealed interior holds water effortlessly.', 42.00, 8, 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80', 'Natural Terracotta Clay', 'approved', '2026-02-05 11:30:00'),
(3, 3, 4, 'Chunky Merino Wool Hand-Knit Blanket', 'chunky-merino-wool-hand-knit-blanket', 'Extra-soft arm-knitted throw blanket crafted from 100% natural cruelty-free Merino wool roving. Ultra-breathable, thermo-regulating, and adds luxurious warmth to any sofa or bedroom.', 135.00, 5, 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=800&q=80', '100% Pure Merino Wool', 'approved', '2026-02-15 14:20:00'),
(4, 3, 4, 'Hand-Dyed Alpaca Cable Knit Winter Beanie', 'hand-dyed-alpaca-cable-knit-winter-beanie', 'Seamless classic cable beanie knit with baby alpaca and silk yarn. Naturally botanical-dyed using avocado pits for a gentle dusky rose hue.', 36.00, 20, 'https://images.unsplash.com/photo-1576871337622-98d48d1cf531?auto=format&fit=crop&w=800&q=80', 'Baby Alpaca & Mulberry Silk', 'approved', '2026-02-22 16:00:00'),
(5, 4, 5, 'Live-Edge Black Walnut Serving Platter', 'live-edge-black-walnut-serving-platter', 'Carved from salvaged American black walnut timber with natural live bark accents. Finished with organic food-safe beeswax and cold-pressed mineral oil.', 68.00, 7, 'https://images.unsplash.com/photo-1615865417491-9941019fbc00?auto=format&fit=crop&w=800&q=80', 'Salvaged Black Walnut & Beeswax', 'approved', '2026-02-28 13:45:00'),
(6, 4, 5, 'Hand-Carved Cherry Wood Cooking Spoon Set', 'hand-carved-cherry-wood-cooking-spoon-set', 'Trio of hand-carved cooking spoons with ergonomic teardrop handles and deep stir bowls. Carved using traditional gouges and finished smoothly.', 34.00, 12, 'https://images.unsplash.com/photo-1590736969955-71cc94801759?auto=format&fit=crop&w=800&q=80', 'Pennsylvania Cherry Wood', 'approved', '2026-03-05 17:10:00'),
-- Pending approval products for Admin approval tab
(7, 2, 1, 'Porcelain Pour-Over Coffee Dripper Cone', 'porcelain-pour-over-coffee-dripper-cone', 'Wheel-thrown porcelain coffee dripper featuring spiral interior extraction ridges. Requires standard #2 cone paper filters.', 38.00, 10, 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=800&q=80', 'Limoges Porcelain Clay', 'pending', '2026-03-24 09:15:00'),
(8, 3, 4, 'Bohemian Macrame Botanical Wall Hanging', 'bohemian-macrame-botanical-wall-hanging', 'Intricate bohemian wall art knotted with unbleached 4mm cotton rope on a weathered ocean driftwood branch.', 55.00, 6, 'https://images.unsplash.com/photo-1528458988180-60b64d6db875?auto=format&fit=crop&w=800&q=80', 'Organic Cotton & Ocean Driftwood', 'pending', '2026-03-25 15:40:00');

-- 5. Orders (Completed and Processing)
INSERT INTO `orders` (`id`, `order_number`, `buyer_id`, `total_amount`, `status`, `shipping_address`, `created_at`) VALUES
(1, 'ORD-2026-1001', 6, 99.00, 'completed', '742 Evergreen Terrace, Springfield, OR 97477', '2026-02-10 14:00:00'),
(2, 'ORD-2026-1002', 7, 57.00, 'completed', '221B Baker Street, London, NW1 6XE', '2026-02-20 16:30:00'),
(3, 'ORD-2026-1003', 8, 135.00, 'completed', '42 Wallaby Way, Sydney, NSW 2000', '2026-03-02 11:15:00'),
(4, 'ORD-2026-1004', 6, 68.00, 'completed', '742 Evergreen Terrace, Springfield, OR 97477', '2026-03-10 15:45:00'),
(5, 'ORD-2026-1005', 7, 28.50, 'processing', '221B Baker Street, London, NW1 6XE', '2026-03-23 18:20:00');

-- 6. Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `seller_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 2, 2, 28.50, 57.00), -- John bought 2x Mugs
(2, 1, 2, 2, 1, 42.00, 42.00), -- John bought 1x Vase
(3, 2, 1, 2, 2, 28.50, 57.00), -- Sarah bought 2x Mugs
(4, 3, 3, 3, 1, 135.00, 135.00),-- Alex bought 1x Blanket
(5, 4, 5, 4, 1, 68.00, 68.00),  -- John bought 1x Walnut Platter
(6, 5, 1, 2, 1, 28.50, 28.50);  -- Sarah bought 1x Mug (Processing)

-- 7. Reviews (Detailed review seed with various ratings for Product #1 and others)
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 6, 5, 'Absolutely exquisite craftsmanship! The raw clay texture on the base provides a lovely grip, and the oatmeal glaze has a subtle iridescence in natural light. Keeps my espresso warm for a surprisingly long time.', '2026-02-15 10:25:00'),
(2, 1, 7, 5, 'Bought two of these for our anniversary. The subtle speckling and thumb-rest on the handle make this the most comfortable coffee mug I have ever owned. Shipped in 100% biodegradable packaging too!', '2026-02-28 14:10:00'),
(3, 1, 8, 4, 'Very sturdy ceramic with lovely weight. The color is slightly more beige than the studio photos, but it gives it an authentic handmade charm. Highly recommended artisan work.', '2026-03-10 18:40:00'),
(4, 3, 8, 5, 'Worth every single penny. It feels like sleeping inside a cloud of soft warm wool. No chemical smells, pure natural merino yarn. Superb quality.', '2026-03-12 12:00:00'),
(5, 5, 6, 5, 'The grain on this black walnut platter is breathtaking. Used it for a weekend charcuterie board and our guests would not stop complimenting it. Smooth velvet finish.', '2026-03-18 16:35:00');
