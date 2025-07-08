CREATE DATABASE IF NOT EXISTS golden_dessert CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- supabase c'est de la merde on reviens sur sql et php
USE golden_dessert;

CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    siteweb VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
