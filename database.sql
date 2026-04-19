CREATE DATABASE IF NOT EXISTS food_donation_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE food_donation_db;

-- ─── TABLE: donors ───
CREATE TABLE IF NOT EXISTS donors (
    donor_id   INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    phone      VARCHAR(15),
    password   VARCHAR(255) NOT NULL,
    address    TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─── TABLE: ngos ───
CREATE TABLE IF NOT EXISTS ngos (
    ngo_id     INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    ngo_name   VARCHAR(150) NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    phone      VARCHAR(15),
    password   VARCHAR(255) NOT NULL,
    address    TEXT,
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
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE
);

-- ─── TABLE: requests ───
CREATE TABLE IF NOT EXISTS requests (
    request_id     INT AUTO_INCREMENT PRIMARY KEY,
    donation_id    INT NOT NULL,
    ngo_id         INT NOT NULL,
    request_status ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
    request_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE,
    FOREIGN KEY (ngo_id)      REFERENCES ngos(ngo_id) ON DELETE CASCADE
);

-- ─── TABLE: contact_messages ───
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id   INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100),
    email        VARCHAR(100),
    message      TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─── TABLE: remember_tokens ("Remember me" cookies) ───
CREATE TABLE IF NOT EXISTS remember_tokens (
    token_id    INT AUTO_INCREMENT PRIMARY KEY,
    principal_type ENUM('donor','ngo') NOT NULL,
    principal_id   INT NOT NULL,
    token_hash  CHAR(64) NOT NULL UNIQUE,
    expires_at  DATETIME NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME NULL,
    INDEX (principal_type, principal_id),
    INDEX (expires_at)
);

