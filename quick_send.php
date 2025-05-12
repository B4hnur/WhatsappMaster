<?php
// Lazımi faylları daxil edin
require_once 'db_config.php';
require_once 'whatsapp_cloud_api.php';

// Session-u başlat və istifadəçi girişini yoxla
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Mesaj göndərmə xəta və uğur mesajları
$error = null;
$success = null;

// CSRF tokenini yoxla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token yoxlama uğursuz oldu");
    }
    
    // Birden çox telefon nömrəsi və mesaj əldə et
    $phones = isset($_POST['phones']) ? trim($_POST['phones']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Telefonları ayır - vergüllə və ya yeni xətlə ayrılmış olmalıdır
    $phoneList = preg_split('/[\s,]+/', $phones);
    $phoneList = array_filter($phoneList); // Boş elementləri sil
    
    if (empty($phoneList)) {
        $error = "Ən azı bir telefon nömrəsi daxil etməlisiniz";
    } elseif (empty($message)) {
        $error = "Mesaj mətni daxil etməlisiniz";
    } else {
        // WhatsApp API-ni çağır
        $whatsapp = new WhatsAppCloudAPI();
        
        // API məlumatlarını yoxla
        if (!$whatsapp->hasValidCredentials()) {
            $error = "WhatsApp API məlumatları tənzimlənməyib. Lütfən admin ilə əlaqə saxlayın.";
        } else {
            $successCount = 0;
            $failCount = 0;
            $failedNumbers = [];
            
            // Hər bir nömrəyə mesaj göndər
            foreach ($phoneList as $phone) {
                $phone = trim($phone);
                if (empty($phone)) continue;
                
                $result = $whatsapp->sendTextMessage($phone, $message);
                
                if ($result['success']) {
                    $successCount++;
                    
                    // Mesaj tarixçəsini yadda saxla
                    try {
                        $stmt = $conn->prepare("INSERT INTO messages (user_id, phone, message, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $status = 'sent';
                        $stmt->execute([$_SESSION['user_id'], $phone, $message, $status]);
                    } catch (PDOException $e) {
                        error_log("Message history save error: " . $e->getMessage());
                    }
                } else {
                    $failCount++;
                    $failedNumbers[] = $phone;
                }
            }
            
            // Nəticəni göstər
            if ($successCount > 0) {
                $success = "{$successCount} nömrəyə mesaj uğurla göndərildi!";
                
                if ($failCount > 0) {
                    $error = "{$failCount} nömrəyə göndərmə uğursuz oldu: " . implode(", ", $failedNumbers);
                }
            } else {
                $error = "Bütün nömrələrə mesaj göndərmə uğursuz oldu.";
            }
        }
    }
}

// Yeni CSRF tokeni yarat
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Başlıq sənədini əlavə et
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1><i class="fab fa-whatsapp text-success me-2"></i>Sürətli Mesaj Göndər</h1>
            <p class="lead">Mesajınızı bir və ya bir neçə nömrəyə göndərin</p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Çoxlu Mesaj Göndərmə</h5>
                </div>
                
                <div class="card-body">
                    <form action="quick_send.php" method="post" id="quickSendForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="mb-3">
                            <label for="phones" class="form-label">Telefon Nömrələri</label>
                            <textarea class="form-control" id="phones" name="phones" rows="4" placeholder="Nömrələri hər sətirə bir ədəd və ya vergüllə ayıraraq daxil edin. Məsələn:&#10;+994501234567&#10;+994551234567&#10;və ya: +994501234567, +994551234567"></textarea>
                            <div class="form-text">Telefon nömrələrini beynəlxalq formatda daxil edin (məs. +994501234567)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Mesaj Mətni</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Göndərmək istədiyiniz mesajı daxil edin..."></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fab fa-whatsapp me-2"></i>Mesajı Göndər
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Məsləhətlər</h5>
                </div>
                
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex">
                            <div class="me-3 text-info"><i class="fas fa-info-circle fa-lg"></i></div>
                            <div>Birdən çox nömrəyə mesaj göndərmək üçün onları ayrı sətirlərə və ya vergüllə ayıraraq yazın.</div>
                        </li>
                        <li class="list-group-item d-flex">
                            <div class="me-3 text-warning"><i class="fas fa-exclamation-triangle fa-lg"></i></div>
                            <div>Telefon nömrələrini beynəlxalq formatda daxil etdiyinizə əmin olun.</div>
                        </li>
                        <li class="list-group-item d-flex">
                            <div class="me-3 text-success"><i class="fas fa-check-circle fa-lg"></i></div>
                            <div>Göndərilən bütün mesajlar avtomatik olaraq tarixçədə saxlanılır.</div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Ana Səhifəyə Qayıt
                </a>
                <a href="messages.php" class="btn btn-outline-primary">
                    <i class="fas fa-history me-2"></i>Mesaj Tarixçəsi
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Form təsdiqlənməsi
    document.getElementById('quickSendForm').addEventListener('submit', function(e) {
        const phones = document.getElementById('phones').value.trim();
        const message = document.getElementById('message').value.trim();
        
        if (!phones) {
            e.preventDefault();
            alert('Xahiş edirik ən azı bir telefon nömrəsi daxil edin');
            return false;
        }
        
        if (!message) {
            e.preventDefault();
            alert('Xahiş edirik bir mesaj daxil edin');
            return false;
        }
    });
</script>

<?php include 'includes/footer.php'; ?>