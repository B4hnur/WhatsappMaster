<?php
require_once 'db_config.php';
requireLogin();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Təhlükəsizlik xətası! Zəhmət olmasa yenidən cəhd edin.";
        header("Location: contacts.php");
        exit();
    }
    
    // Get form data
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    // Validate input
    if (empty($name) || empty($phone)) {
        $_SESSION['error'] = "Ad və telefon nömrəsi doldurulmalıdır.";
        header("Location: contacts.php");
        exit();
    }
    
    // Format phone number - ensure it starts with country code
    $phone = preg_replace('/\D/', '', $phone); // Remove all non-digits
    if (!preg_match('/^994/', $phone)) {
        $phone = "994" . ltrim($phone, '0'); // Add country code if missing
    }
    
    try {
        // Insert contact
        $stmt = $conn->prepare("INSERT INTO contacts (user_id, name, phone, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $name, $phone, $notes]);
        
        $_SESSION['success'] = "Əlaqə uğurla əlavə edildi.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Xəta baş verdi: " . $e->getMessage();
    }
}

// Redirect back to contacts page
header("Location: contacts.php");
exit();
?>
