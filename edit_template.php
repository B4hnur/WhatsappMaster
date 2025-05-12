<?php
require_once 'db_config.php';
requireLogin();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Təhlükəsizlik xətası! Zəhmət olmasa yenidən cəhd edin.";
        header("Location: templates.php");
        exit();
    }
    
    // Get form data
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $content = trim($_POST['content']);
    
    // Validate input
    if (empty($name) || empty($content)) {
        $_SESSION['error'] = "Şablon adı və mətni doldurulmalıdır.";
        header("Location: templates.php");
        exit();
    }
    
    try {
        // Verify the template belongs to the current user
        $stmt = $conn->prepare("SELECT id FROM message_templates WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() === 0) {
            $_SESSION['error'] = "Şablon tapılmadı və ya əməliyyat icazəsi yoxdur.";
            header("Location: templates.php");
            exit();
        }
        
        // Update template
        $stmt = $conn->prepare("UPDATE message_templates SET name = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $content, $id, $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Şablon uğurla yeniləndi.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Xəta baş verdi: " . $e->getMessage();
    }
}

// Redirect back to templates page
header("Location: templates.php");
exit();
?>
