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
    
    try {
        // Get user's WhatsApp number
        $stmt = $conn->prepare("SELECT whatsapp_number FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (empty($user['whatsapp_number'])) {
            $_SESSION['error'] = "WhatsApp nömrəniz təyin edilməyib. Profil parametrlərinizdə nömrənizi təyin edin.";
            header("Location: messages.php");
            exit();
        }
        
        // Start transaction
        $conn->beginTransaction();
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($contact_ids as $contact_id) {
            // Verify contact belongs to user
            $stmt = $conn->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
            $stmt->execute([(int)$contact_id, $_SESSION['user_id']]);
            $contact = $stmt->fetch();
            
            if ($contact) {
                // Log message to history
                $stmt = $conn->prepare("INSERT INTO message_history (user_id, contact_id, template_id, message) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $contact_id, $template_id, $message]);
                
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
        
        // Prepare contacts for JavaScript to send messages
        $contactsForWhatsApp = [];
        foreach ($contact_ids as $contact_id) {
            $stmt = $conn->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ?");
            $stmt->execute([(int)$contact_id, $_SESSION['user_id']]);
            $contact = $stmt->fetch();
            
            if ($contact) {
                $contactsForWhatsApp[] = [
                    'id' => $contact['id'],
                    'name' => $contact['name'],
                    'phone' => $contact['phone']
                ];
            }
        }
        
        // Set message data in session for JavaScript to use
        $_SESSION['whatsapp_message_data'] = [
            'message' => $message,
            'contacts' => $contactsForWhatsApp
        ];
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error'] = "Xəta baş verdi: " . $e->getMessage();
    }
}

// Redirect to the message sending page with WhatsApp window opening instructions
header("Location: whatsapp_redirect.php");
exit();
?>
