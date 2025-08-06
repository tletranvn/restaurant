-- Script d'initialisation MySQL pour le restaurant
CREATE DATABASE IF NOT EXISTS restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'restaurant_user'@'%' IDENTIFIED BY 'restaurant_pass';
GRANT ALL PRIVILEGES ON restaurant_db.* TO 'restaurant_user'@'%';
FLUSH PRIVILEGES;

USE restaurant_db;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    api_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des restaurants
CREATE TABLE IF NOT EXISTS restaurant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description LONGTEXT,
    am_opening_time JSON DEFAULT NULL,
    pm_opening_time JSON DEFAULT NULL,
    max_guest INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des images
CREATE TABLE IF NOT EXISTS picture (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    path VARCHAR(255) NOT NULL,
    alt VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurant(id) ON DELETE CASCADE
);

-- Données de test
INSERT INTO restaurant (name, description, am_opening_time, pm_opening_time, max_guest) VALUES
('Le Petit Bistrot', 'Restaurant traditionnel français', '{"open": "09:00", "close": "14:00"}', '{"open": "19:00", "close": "23:00"}', 50),
('Sushi Zen', 'Restaurant japonais authentique', '{"open": "12:00", "close": "15:00"}', '{"open": "18:00", "close": "22:30"}', 30);

INSERT INTO user (email, roles, password) VALUES 
('admin@restaurant.com', '["ROLE_ADMIN"]', '$2y$13$example_hash_password'),
('user@restaurant.com', '["ROLE_USER"]', '$2y$13$example_hash_password');
