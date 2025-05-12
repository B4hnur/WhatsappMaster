<?php
require_once 'db_config.php';
requireLogin();

// Check if ID and CSRF token are provided
if (isset($_GET['id']) && isset($_GET['csrf_token'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_GET['csrf_token'])) {
        $_SESSION['error'] = "Təhlükəsizlik xətası! Zəhmət olmasa yenidən cəhd edin.";
        header("Location: contacts.php");
        exit();
    }
    
    $id = (int)$_GET['id'];
    
    try {
        // Verify the contact belongs to the current user
        $stmt = $conn->prepare("SELECT id FROM contacts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['error'] = "Əlaqə tapılmadı və ya əməliyyat icazəsi yoxdur.";
            header("Location: contacts.php");
            exit();
        }
        
        // Delete contact
        $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Əlaqə uğurla silindi.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Xəta baş verdi: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Yanlış sorğu.";
}

// Redirect back to contacts page
header("Location: contacts.php");
exit();
?>
