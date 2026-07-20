CREATE DATABASE IF NOT EXISTS mydb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mydb;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email) VALUES
    ('Abin Aneesh', 'abinaneesh21@example.edu'),
    ('Aw Ming Jie', 'awmingjie42@example.edu'),
    ('May Cherry Aung', 'maycherryaung78@example.edu')
    ('Gandhimathi Murugavel Dhushyanth', 'gmdhushyanth9@example.edu')
ON DUPLICATE KEY UPDATE
    name = VALUES(name);

