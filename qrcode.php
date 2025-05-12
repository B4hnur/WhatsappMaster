<?php
require_once 'phpqrcode.php';

// Check if a text parameter is provided
if (isset($_GET['text']) && !empty($_GET['text'])) {
    $text = $_GET['text'];
    $size = isset($_GET['size']) ? intval($_GET['size']) : 8;
    $margin = isset($_GET['margin']) ? intval($_GET['margin']) : 4;
    
    // Generate QR code and output directly to browser
    header('Content-Type: image/png');
    $qrCode = QRcode::png($text, false, 0, $size, $margin, true);
    exit;
} else {
    // If no text is provided, return an error image
    header('Content-Type: image/png');
    $img = imagecreate(200, 200);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $textcolor = imagecolorallocate($img, 255, 0, 0);
    imagestring($img, 5, 50, 90, 'Error: No text provided', $textcolor);
    imagepng($img);
    imagedestroy($img);
    exit;
}
?>