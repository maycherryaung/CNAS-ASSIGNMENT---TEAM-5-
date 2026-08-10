-- Create the "mydb" database if it doesn't exist yet, using UTF-8 encoding
-- so it supports emojis and international characters.
CREATE DATABASE IF NOT EXISTS mydb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- This instruction tells the system that all following commands should be applied directly to this specific database.
USE mydb;

-- Define the "users" table if it doesn't exist yet.
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,                                     -- unique row identifier, auto-incremented
    name VARCHAR(100) NOT NULL,                                            -- required, up to 100 characters
    email VARCHAR(191) NOT NULL UNIQUE,                                    -- required and must be unique (no duplicate emails)
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,               -- auto-set to "now" when the row is created
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- auto-refreshed on every update
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the table with the 4 team members.
-- ON DUPLICATE KEY UPDATE makes this safe to re-run: if a row with the same
-- email already exists, it just updates the name instead of erroring out.
INSERT INTO users (name, email) VALUES
    ('May Cherry Aung', 'maycherryaung@connect.np.edu.sg'),
    ('Aw Ming Jie', 'awmingjie@connect.np.edu.sg'),
    ('Abin Aneesh', 'abinaneesh@connect.np.edu.sg'),
    ('Gandhimathi Murugavel Dhushyanth', 'gandhimathimurugaveldhushyanth@connect.np.edu.sg')
ON DUPLICATE KEY UPDATE
-- This is a safety mechanism. If the system tries to add an email that is already stored in the database, 
--it will just update the user's name instead of failing or displaying an error.
    name = VALUES(name);

/* In a cloud native environment, systems are built to be highly automated. Servers and databases are often created, destroyed, and recreated automatically by deployment scripts.

Because this script uses commands like IF NOT EXISTS and ON DUPLICATE KEY UPDATE, it is idempotent. Idempotency means you can run this exact same script one time or one hundred times, and the system will remain in the exact same correct state without crashing or creating duplicate data. This is a mandatory requirement for automated cloud deployments.
*/