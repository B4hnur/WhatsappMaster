<?php
require_once 'db_config.php';

try {
    // Check if whatsapp_sessions table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'whatsapp_sessions'");
    if ($stmt->rowCount() == 0) {
        // Create whatsapp_sessions table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                session_data TEXT,
                status ENUM('disconnected', 'pending', 'connected') DEFAULT 'disconnected',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "WhatsApp sessions table created!<br>";
    } else {
        echo "WhatsApp sessions table already exists!<br>";
    }
    
    // Check if whatsapp_logs table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'whatsapp_logs'");
    if ($stmt->rowCount() == 0) {
        // Create whatsapp_logs table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS whatsapp_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                log_type VARCHAR(50) NOT NULL,
                log_data TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        echo "WhatsApp logs table created!<br>";
    } else {
        echo "WhatsApp logs table already exists!<br>";
    }
    
    // Create indexes
    $conn->exec("
        CREATE INDEX IF NOT EXISTS idx_whatsapp_sessions_user_id ON whatsapp_sessions(user_id);
        CREATE INDEX IF NOT EXISTS idx_whatsapp_logs_user_id ON whatsapp_logs(user_id);
        CREATE INDEX IF NOT EXISTS idx_whatsapp_logs_type ON whatsapp_logs(log_type);
    ");
    
    echo "All tables created successfully!";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>