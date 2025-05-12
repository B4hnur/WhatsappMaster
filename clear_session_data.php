<?php
require_once 'db_config.php';
requireLogin();

// Make sure it's a valid AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'error' => 'Invalid request']));
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    die(json_encode(['success' => false, 'error' => 'Invalid CSRF token']));
}

// Get key to clear
$key = isset($_POST['key']) ? $_POST['key'] : '';

if (!empty($key) && isset($_SESSION[$key])) {
    unset($_SESSION[$key]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid session key']);
}
?>