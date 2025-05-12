<?php
/**
 * Sadə QR kod yaradıcı
 * 
 * Heç bir kitabxanaya ehtiyac olmadan sadə QR kod yaratmaq üçün
 */

// Veriləcək mətn
$text = isset($_GET['text']) ? $_GET['text'] : 'Test QR Code';
$size = isset($_GET['size']) ? (int)$_GET['size'] : 200;

// Google Chart API istifadə edərək QR kod yaradır
$googleChartAPI = "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chl=" . urlencode($text);

// Şəkli endirək və göstərək
$imageContent = file_get_contents($googleChartAPI);

if ($imageContent !== false) {
    // Şəkil məzmununu göstər
    header('Content-Type: image/png');
    echo $imageContent;
} else {
    // Xəta baş verdikdə, sadə bir şəkil yaradın
    header('Content-Type: image/png');
    $img = imagecreate(200, 200);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $textcolor = imagecolorallocate($img, 255, 0, 0);
    imagestring($img, 5, 50, 90, 'QR kod yüklənmədi', $textcolor);
    imagepng($img);
    imagedestroy($img);
}
?>