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
    ('May Cherry Aung', 'maycherryaung@connect.np.edu.sg'),
    ('Aw Ming Jie', 'awmingjie@connect.np.edu.sg'),
    ('Abin Aneesh', 'abinaneesh@connect.np.edu.sg'),
    ('Gandhimathi Murugavel Dhushyanth', 'gandhimathimurugaveldhushyanth@connect.np.edu.sg')
ON DUPLICATE KEY UPDATE
    name = VALUES(name);
