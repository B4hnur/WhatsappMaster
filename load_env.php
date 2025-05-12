<?php
/**
 * Sadə ətraf mühit dəyişənləri yükləyici
 * 
 * Bu fayl .env faylından məlumatları yükləyir və $_ENV və getenv vasitəsilə əlçatan edir
 */

// .env faylını yükləmə funksiyası
function loadEnvFile($filePath) {
    if (!file_exists($filePath)) {
        error_log("Diqqət: {$filePath} faylı mövcud deyil.");
        return false;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Şərh və boş xətləri atlayın
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Dəyişənin artıq təyin edilib-edilmədiyini yoxlayın
        if (!getenv($name)) {
            // Dəyişənləri təyin edin
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    
    return true;
}

// .env faylını yüklə
$envLoaded = loadEnvFile(__DIR__ . '/.env');

// Debug məlumatları
if ($envLoaded) {
    error_log("Environment dəyişənləri .env faylından uğurla yükləndi");
} else {
    error_log("Environment dəyişənlərini yükləmə uğursuz oldu");
}
?>