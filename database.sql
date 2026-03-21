-- ══════════════════════════════════════════════
--   FoodBridge Database Setup
--   Run this in phpMyAdmin or MySQL CLI
-- ══════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS food_donation_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE food_donation_db;

-- ─── TABLE: users ───
CREATE TABLE IF NOT EXISTS users (
    user_id    INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    phone      VARCHAR(15),
    password   VARCHAR(255) NOT NULL,
    role       ENUM('donor','ngo') NOT NULL,
    address    TEXT,
    ngo_name   VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─── TABLE: donations ───
CREATE TABLE IF NOT EXISTS donations (
    donation_id    INT AUTO_INCREMENT PRIMARY KEY,
    donor_id       INT NOT NULL,
    food_title     VARCHAR(150) NOT NULL,
    description    TEXT,
    quantity       VARCHAR(50) NOT NULL,
    food_type      ENUM('veg','non-veg') DEFAULT 'veg',
    expiry_time    DATETIME NOT NULL,
    pickup_address TEXT NOT NULL,
    status         ENUM('available','requested','completed') DEFAULT 'available',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ─── TABLE: requests ───
CREATE TABLE IF NOT EXISTS requests (
    request_id     INT AUTO_INCREMENT PRIMARY KEY,
    donation_id    INT NOT NULL,
    ngo_id         INT NOT NULL,
    request_status ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
    request_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE,
    FOREIGN KEY (ngo_id)      REFERENCES users(user_id) ON DELETE CASCADE
);

-- ─── TABLE: contact_messages ───
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id   INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100),
    email        VARCHAR(100),
    message      TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ══════════════════════════════════════════════
--   SAMPLE DATA
-- ══════════════════════════════════════════════

-- Sample donor (password: password123)
INSERT INTO users (name, email, phone, password, role, address) VALUES
('Rahul Sharma',   'rahul@example.com',   '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Andheri West, Mumbai'),
('Priya Bakery',   'priya@example.com',   '9123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Bandra, Mumbai');

-- Sample NGOs (password: password123)
INSERT INTO users (name, email, phone, password, role, address, ngo_name) VALUES
('Asha Welfare',   'asha@ngo.com',        '9000011111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ngo', 'Dharavi, Mumbai', 'Asha Welfare Foundation'),
('Feed Mumbai',    'feed@ngo.com',        '9000022222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ngo', 'Kurla, Mumbai',   'Feed Mumbai NGO');

-- Sample donations (donor_id = 1 and 2)
INSERT INTO donations (donor_id, food_title, description, quantity, food_type, expiry_time, pickup_address, status) VALUES
(1, 'Cooked Rice & Dal',     'Freshly cooked, enough for 20 people',   '20 meals',  'veg',     NOW() + INTERVAL 6 HOUR,  'Flat 4B, Sahar Road, Andheri West, Mumbai', 'available'),
(1, 'Vegetable Biryani',     'Made today, still warm',                  '15 meals',  'veg',     NOW() + INTERVAL 4 HOUR,  'Flat 4B, Sahar Road, Andheri West, Mumbai', 'available'),
(2, 'Bread Loaves',          'Factory surplus, 3-day shelf life',       '50 loaves', 'veg',     NOW() + INTERVAL 2 DAY,   'Priya Bakery, Hill Road, Bandra, Mumbai',   'available'),
(2, 'Birthday Cake Slices',  'Extra cake from event, good for today',   '30 pieces', 'veg',     NOW() + INTERVAL 8 HOUR,  'Priya Bakery, Hill Road, Bandra, Mumbai',   'available'),
(1, 'Chicken Curry',         'Non-veg, restaurant surplus',             '10 meals',  'non-veg', NOW() + INTERVAL 3 HOUR,  'Flat 4B, Sahar Road, Andheri West, Mumbai', 'available');

-- ══════════════════════════════════════════════
-- DEFAULT PASSWORD FOR ALL SAMPLE USERS: password123
-- ══════════════════════════════════════════════
