-- Create database (run once manually if needed)
CREATE DATABASE IF NOT EXISTS `hollow_mountain` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hollow_mountain`;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Administrator','Manager','Monteur','Medewerker') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Attractions
CREATE TABLE IF NOT EXISTS attractions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location VARCHAR(150) NOT NULL,
  type VARCHAR(100) NOT NULL,
  photos JSON NULL,
  technical_specs JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Maintenance schedules (planned recurring)
CREATE TABLE IF NOT EXISTS maintenance_schedules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attraction_id INT NOT NULL,
  frequency ENUM('daily','weekly','monthly','quarterly','yearly') NOT NULL,
  next_date DATE NOT NULL,
  notes TEXT NULL,
  FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE CASCADE
);

-- Maintenance tasks (instances)
CREATE TABLE IF NOT EXISTS maintenance_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attraction_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  status ENUM('Openstaand','In behandeling','Afgerond') NOT NULL DEFAULT 'Openstaand',
  assigned_to INT NULL,
  scheduled_for DATETIME NULL,
  completed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (attraction_id) REFERENCES attractions(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Seed users (passwords will be set via PHP seed script or replace with hashes)
INSERT IGNORE INTO users (name, email, password_hash, role) VALUES
  ('Admin User', 'admin@hm.local', '$2y$10$placeholder', 'Administrator'),
  ('Manager User', 'manager@hm.local', '$2y$10$placeholder', 'Manager'),
  ('Worker User', 'worker@hm.local', '$2y$10$placeholder', 'Medewerker');


