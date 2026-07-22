CREATE DATABASE IF NOT EXISTS mydb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mydb;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO users (name, email) VALUES
    ('May Cherry Aung', 'maycherryaung@gmail.com'),
    ('Aw Ming Jie', 'awmingjie@gmail.com'),
    ('Abin Aneesh', 'abinaneesh@gmail.com'),
    ('Gandhimathi Murugavel Dhushyanth', 'gmdhush@gmail.com');
