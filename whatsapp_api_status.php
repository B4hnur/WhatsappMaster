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

// WhatsApp Cloud API obyektini yarat
$whatsapp = new WhatsAppCloudAPI();

// API məlumatlarını yoxla
$apiStatus = $whatsapp->hasValidCredentials();

// CSRF tokenini yoxla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token yoxlama uğursuz oldu");
    }
    
    // Test mesajını göndər
    if (isset($_POST['send_test']) && !empty($_POST['test_phone'])) {
        $phone = $_POST['test_phone'];
        $result = $whatsapp->sendTestMessage($phone);
        
        if ($result['success']) {
            $success = "Test mesajı uğurla göndərildi!";
        } else {
            $error = "Test mesajı göndərilmədi: " . ($result['error'] ?? "Xəta məlumatı yoxdur");
        }
    }
}

// Yeni CSRF tokeni yarat
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Başlıq sənədini əlavə et
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1><i class="fab fa-whatsapp text-success"></i> WhatsApp Cloud API Statusu</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> API Məlumatları</h5>
                </div>
                
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">API Statusu:</h6>
                        <?php if ($apiStatus): ?>
                            <span class="badge bg-success"><i class="fas fa-check"></i> Aktiv</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times"></i> İnaktiv</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">WhatsApp API Token:</h6>
                        <?php if (getenv('WHATSAPP_API_TOKEN')): ?>
                            <span class="badge bg-success"><i class="fas fa-check"></i> Mövcuddur</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times"></i> Yoxdur</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Telefon Nömrə ID:</h6>
                        <?php if (getenv('WHATSAPP_PHONE_NUMBER_ID')): ?>
                            <span class="badge bg-success"><i class="fas fa-check"></i> Mövcuddur</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times"></i> Yoxdur</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <small class="text-muted">Meta for Developers portalında alınan WhatsApp Business API məlumatları.</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-paper-plane"></i> Test Mesajı Göndər</h5>
                </div>
                
                <div class="card-body">
                    <?php if ($apiStatus): ?>
                        <form action="whatsapp_api_status.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="mb-3">
                                <label for="test_phone" class="form-label">Test Nömrəsi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="test_phone" name="test_phone" placeholder="994501234567" required>
                                </div>
                                <div class="form-text">Bu nömrəyə test mesajı göndəriləcək</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="send_test" class="btn btn-success">
                                    <i class="fab fa-whatsapp"></i> Test Mesajı Göndər
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Test mesajı göndərmək üçün əvvəlcə API məlumatlarını təyin edin.
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer bg-light">
                    <small class="text-muted">Bu formadan WhatsApp API bağlantısını sınaya bilərsiniz.</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0"><i class="fas fa-list-alt"></i> WhatsApp Cloud API İstifadə Təlimatları</h5>
        </div>
        
        <div class="card-body">
            <h5>WhatsApp Cloud API-ni necə istifadə etməli?</h5>
            <ol class="mb-4">
                <li>Meta for Developers portalında <a href="https://developers.facebook.com/" target="_blank">developer hesabı yaradın</a></li>
                <li>WhatsApp Business API üçün bir tətbiq yaradın</li>
                <li>Test üçün bir telefon nömrəsi əlavə edin</li>
                <li>API Token və Phone Number ID əldə edin</li>
                <li>Bu məlumatları sistemə əlavə edin</li>
                <li>Test mesajı göndərməyi sınayın</li>
                <li>Hər şey düzgün işləyirsə, sistemdən istifadə etməyə başlayın</li>
            </ol>
            
            <div class="alert alert-info">
                <strong>Məhdudiyyətlər:</strong> WhatsApp Cloud API-nin pulsuz versiyasında, göndərilən mesajlar üçün gündəlik limit var. Meta for Developers portalındakı təfərrüatlara baxın.
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> İdarə Panelinə Qayıt
        </a>
        <a href="send_whatsapp_api.php" class="btn btn-success ms-2">
            <i class="fab fa-whatsapp"></i> Mesaj Göndər
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>