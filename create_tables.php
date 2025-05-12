<?php
// DB connection
require_once 'db_config.php';

try {
    // Create users table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            whatsapp_number VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create contacts table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create templates table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS message_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Create messages table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            phone VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Create whatsapp_sessions table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS whatsapp_sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_data TEXT,
            status VARCHAR(20) DEFAULT 'disconnected',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    // Create whatsapp_logs table if not exists
    $conn->exec("
        CREATE TABLE IF NOT EXISTS whatsapp_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            log_type VARCHAR(50) NOT NULL,
            log_data TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Demo hesabı əlavə et
    $checkUser = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = 'demo'");
    $checkUser->execute();
    if ($checkUser->fetchColumn() == 0) {
        $password_hash = password_hash('demo123', PASSWORD_DEFAULT);
        $insertDemo = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $insertDemo->execute(['demo', 'demo@example.com', $password_hash]);
        echo "Demo hesabı yaradıldı. İstifadəçi adı: demo, şifrə: demo123<br>";
    }

    echo "Tables created or verified successfully!";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>