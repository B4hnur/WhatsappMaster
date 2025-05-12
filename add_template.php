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
    $name = trim($_POST['name']);
    $content = trim($_POST['content']);
    
    // Validate input
    if (empty($name) || empty($content)) {
        $_SESSION['error'] = "Şablon adı və mətni doldurulmalıdır.";
        header("Location: templates.php");
        exit();
    }
    
    try {
        // Insert template
        $stmt = $conn->prepare("INSERT INTO message_templates (user_id, name, content) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $name, $content]);
        
        $_SESSION['success'] = "Şablon uğurla əlavə edildi.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Xəta baş verdi: " . $e->getMessage();
    }
}

// Redirect back to templates page
header("Location: templates.php");
exit();
?>
