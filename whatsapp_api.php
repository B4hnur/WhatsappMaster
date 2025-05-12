<?php
require_once 'db_config.php';
requireLogin();

// WhatsApp Web Integration
class WhatsAppClient {
    private $user_id;
    private $status = 'disconnected';
    private $session_data = null;
    
    public function __construct($user_id) {
        $this->user_id = $user_id;
        $this->loadSession();
    }
    
    private function loadSession() {
        global $conn;
        
        try {
            // Check if user has an active WhatsApp session
            $stmt = $conn->prepare("SELECT session_data, status FROM whatsapp_sessions WHERE user_id = ?");
            $stmt->execute([$this->user_id]);
            $session = $stmt->fetch();
            
            if ($session) {
                $this->status = $session['status'];
                $this->session_data = json_decode($session['session_data'], true);
            }
        } catch (PDOException $e) {
            error_log("WhatsApp session load error: " . $e->getMessage());
        }
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function generateQRCode() {
        // Generate a proper QR code for WhatsApp connection
        $session_id = bin2hex(random_bytes(16)); // Generate session identifier
        
        // Data to be encoded in QR
        $qr_data = [
            'session' => $session_id,
            'user_id' => $this->user_id,
            'timestamp' => time(),
            'client' => 'whatsapp_web',
            'action' => 'connect'
        ];
        
        // Encode the data for QR code
        $encoded_data = base64_encode(json_encode($qr_data));
        
        try {
            global $conn;
            
            // First ensure the table exists for backward compatibility
            try {
                // Check if table exists or create it
                $check_table = $conn->query("SHOW TABLES LIKE 'whatsapp_sessions'");
                if ($check_table->rowCount() == 0) {
                    // Create the table if it doesn't exist
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
                }
            } catch (Exception $e) {
                // Just log and continue
                error_log("Table check error: " . $e->getMessage());
            }
            
            // Update session data with new QR code
            $session_data = json_encode([
                'qr_code' => $encoded_data, 
                'session_id' => $session_id,
                'timestamp' => time()
            ]);
            
            // Check if session exists using a more reliable method
            try {
                $check_stmt = $conn->prepare("SELECT COUNT(*) FROM whatsapp_sessions WHERE user_id = ?");
                $check_stmt->execute([$this->user_id]);
                $session_exists = (int)$check_stmt->fetchColumn() > 0;
                
                if ($session_exists) {
                    $stmt = $conn->prepare("UPDATE whatsapp_sessions SET session_data = ?, status = 'pending' WHERE user_id = ?");
                    $stmt->execute([$session_data, $this->user_id]);
                } else {
                    $stmt = $conn->prepare("INSERT INTO whatsapp_sessions (user_id, session_data, status) VALUES (?, ?, 'pending')");
                    $stmt->execute([$this->user_id, $session_data]);
                }
                
                $this->status = 'pending';
                $this->session_data = ['qr_code' => $encoded_data, 'session_id' => $session_id];
                
                return $encoded_data;
            } catch (PDOException $e) {
                // If there's an error with the whatsapp_sessions table
                error_log("WhatsApp QR code database error: " . $e->getMessage());
                
                // Return success even if DB fails - we can still generate QR code
                $this->status = 'pending';
                $this->session_data = ['qr_code' => $encoded_data, 'session_id' => $session_id];
                return $encoded_data;
            }
        } catch (Exception $e) {
            error_log("WhatsApp QR code generation error: " . $e->getMessage());
            
            // Even on error, generate a fallback QR code
            $fallback_data = [
                'session' => 'fallback_' . bin2hex(random_bytes(4)),
                'timestamp' => time(),
                'client' => 'whatsapp_web',
                'action' => 'connect',
                'fallback' => true
            ];
            
            return base64_encode(json_encode($fallback_data));
        }
    }
    
    public function simulateConnection() {
        try {
            global $conn;
            // In a real implementation, this would be called when a WebSocket confirms connection
            $session_data = json_encode([
                'connected_at' => time(),
                'device_info' => [
                    'platform' => 'Web',
                    'browser' => 'Chrome',
                ]
            ]);
            
            // Make sure table exists
            try {
                $check_table = $conn->query("SHOW TABLES LIKE 'whatsapp_sessions'");
                if ($check_table->rowCount() == 0) {
                    // Create the table if it doesn't exist
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
                }
            } catch (Exception $e) {
                error_log("Table check error: " . $e->getMessage());
            }
            
            try {
                // Check if record exists
                $check_stmt = $conn->prepare("SELECT COUNT(*) FROM whatsapp_sessions WHERE user_id = ?");
                $check_stmt->execute([$this->user_id]);
                $session_exists = (int)$check_stmt->fetchColumn() > 0;
                
                if ($session_exists) {
                    $stmt = $conn->prepare("UPDATE whatsapp_sessions SET session_data = ?, status = 'connected' WHERE user_id = ?");
                    $stmt->execute([$session_data, $this->user_id]);
                } else {
                    $stmt = $conn->prepare("INSERT INTO whatsapp_sessions (user_id, session_data, status) VALUES (?, ?, 'connected')");
                    $stmt->execute([$this->user_id, $session_data]);
                }
            } catch (PDOException $e) {
                error_log("WhatsApp connection update error: " . $e->getMessage());
                // Continue even if DB fails
            }
            
            $this->status = 'connected';
            $this->session_data = json_decode($session_data, true);
            
            return true;
        } catch (Exception $e) {
            error_log("WhatsApp connection simulation error: " . $e->getMessage());
            // Still mark as connected in memory for demo purposes
            $this->status = 'connected';
            return true;
        }
    }
    
    public function disconnect() {
        try {
            global $conn;
            
            try {
                $stmt = $conn->prepare("UPDATE whatsapp_sessions SET status = 'disconnected' WHERE user_id = ?");
                $stmt->execute([$this->user_id]);
            } catch (PDOException $e) {
                error_log("WhatsApp disconnect DB error: " . $e->getMessage());
                // Continue even if DB fails
            }
            
            $this->status = 'disconnected';
            $this->session_data = null;
            
            return true;
        } catch (Exception $e) {
            error_log("WhatsApp disconnect error: " . $e->getMessage());
            // Still mark as disconnected in memory
            $this->status = 'disconnected';
            return true;
        }
    }
    
    public function sendMessage($phone, $message) {
        if ($this->status !== 'connected') {
            return ['success' => false, 'error' => 'WhatsApp session disconnected'];
        }
        
        // In a real implementation, this would use the WhatsApp Web API or WebSocket
        // to send the message directly through the connected WhatsApp Web instance
        // For this simulation, we'll just log it and return success
        
        try {
            global $conn;
            
            // Make sure table exists
            try {
                $check_table = $conn->query("SHOW TABLES LIKE 'whatsapp_logs'");
                if ($check_table->rowCount() == 0) {
                    // Create the table if it doesn't exist
                    $conn->exec("
                        CREATE TABLE IF NOT EXISTS whatsapp_logs (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            user_id INT NOT NULL,
                            log_type VARCHAR(50) NOT NULL,
                            log_data TEXT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                }
            } catch (Exception $e) {
                error_log("Table check error: " . $e->getMessage());
            }
            
            // Log this message send attempt
            $log_data = json_encode([
                'phone' => $phone,
                'message_preview' => mb_substr($message, 0, 100),
                'timestamp' => time()
            ]);
            
            try {
                $stmt = $conn->prepare("INSERT INTO whatsapp_logs (user_id, log_type, log_data) VALUES (?, 'message_sent', ?)");
                $stmt->execute([$this->user_id, $log_data]);
            } catch (PDOException $e) {
                error_log("WhatsApp log error: " . $e->getMessage());
                // Continue even if logging fails
            }
            
            // Always return success for demo purposes
            return ['success' => true];
        } catch (Exception $e) {
            error_log("WhatsApp message send error: " . $e->getMessage());
            // Still return success for demo to prevent blocking the UI
            return ['success' => true, 'note' => 'Demo mode'];
        }
    }
}

// API endpoint logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Make sure it's a valid AJAX request
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die(json_encode(['success' => false, 'error' => 'Invalid request']));
    }
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die(json_encode(['success' => false, 'error' => 'Invalid CSRF token']));
    }
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $client = new WhatsAppClient($_SESSION['user_id']);
    
    switch ($action) {
        case 'get_status':
            echo json_encode(['success' => true, 'status' => $client->getStatus()]);
            break;
            
        case 'generate_qr':
            $qr_code = $client->generateQRCode();
            if ($qr_code) {
                echo json_encode(['success' => true, 'qr_code' => $qr_code]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Could not generate QR code']);
            }
            break;
            
        case 'connect':
            // For simulation purposes only
            $result = $client->simulateConnection();
            echo json_encode(['success' => $result]);
            break;
            
        case 'disconnect':
            $result = $client->disconnect();
            echo json_encode(['success' => $result]);
            break;
            
        case 'send_message':
            if (!isset($_POST['phone']) || !isset($_POST['message'])) {
                echo json_encode(['success' => false, 'error' => 'Missing phone or message']);
                break;
            }
            
            $phone = trim($_POST['phone']);
            $message = trim($_POST['message']);
            
            // Validate
            if (empty($phone) || empty($message)) {
                echo json_encode(['success' => false, 'error' => 'Phone or message cannot be empty']);
                break;
            }
            
            // Format phone
            $phone = preg_replace('/\D/', '', $phone);
            if (!preg_match('/^994/', $phone)) {
                $phone = "994" . ltrim($phone, '0');
            }
            
            $result = $client->sendMessage($phone, $message);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }
    
    exit();
}

// If not a POST request, redirect to messages page
header("Location: messages.php");
exit();
?>