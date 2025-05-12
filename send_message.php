<?php
require_once 'db_config.php';
requireLogin();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Təhlükəsizlik xətası! Zəhmət olmasa yenidən cəhd edin.";
        header("Location: messages.php");
        exit();
    }
    
    // Get form data
    $message = trim($_POST['message']);
    $contact_ids = isset($_POST['contact_ids']) ? $_POST['contact_ids'] : [];
    $template_id = !empty($_POST['selected-template-id']) ? (int)$_POST['selected-template-id'] : null;
    
    // Validate input
    if (empty($message)) {
        $_SESSION['error'] = "Mesaj mətni daxil edilməlidir.";
        header("Location: messages.php");
        exit();
    }
    
    if (empty($contact_ids)) {
        $_SESSION['error'] = "Ən azı bir əlaqə seçilməlidir.";
        header("Location: messages.php");
        exit();
    }
    
    // Check if WhatsApp is connected
    $useDirectSend = false;
    try {
        $stmt = $conn->prepare("SELECT status FROM whatsapp_sessions WHERE user_id = ? AND status = 'connected'");
        $stmt->execute([$_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            $useDirectSend = true;
        }
    } catch (PDOException $e) {
        error_log("Error checking WhatsApp connection: " . $e->getMessage());
    }
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        $successCount = 0;
        $errorCount = 0;
        $contactsForWhatsApp = [];
        
        foreach ($contact_ids as $contact_id) {
            // Verify contact belongs to user
            $stmt = $conn->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
            $stmt->execute([(int)$contact_id, $_SESSION['user_id']]);
            $contact = $stmt->fetch();
            
            if ($contact) {
                // Log message to history
                $stmt = $conn->prepare("INSERT INTO message_history (user_id, contact_id, template_id, message) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $contact_id, $template_id, $message]);
                
                // Add to contacts list for WhatsApp
                $contactsForWhatsApp[] = [
                    'id' => $contact['id'],
                    'name' => $contact['name'],
                    'phone' => $contact['phone']
                ];
                
                $successCount++;
            } else {
                $errorCount++;
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        if ($successCount > 0) {
            $_SESSION['success'] = "$successCount əlaqəyə mesaj göndərildi.";
            
            if ($errorCount > 0) {
                $_SESSION['warning'] = "$errorCount əlaqə tapılmadı və ya məlumatları çıxarıla bilmədi.";
            }
        } else {
            $_SESSION['error'] = "Mesaj göndərmək mümkün olmadı.";
        }
        
        // Set message data in session
        $_SESSION['whatsapp_message_data'] = [
            'message' => $message,
            'contacts' => $contactsForWhatsApp,
            'direct_send' => $useDirectSend
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error'] = "Xəta baş verdi: " . $e->getMessage();
    }
}

// Redirect to appropriate page based on WhatsApp connection
if (isset($_SESSION['whatsapp_message_data']['direct_send']) && $_SESSION['whatsapp_message_data']['direct_send']) {
    header("Location: whatsapp_direct.php");
} else {
    header("Location: whatsapp_redirect.php");
}
exit();
?>
