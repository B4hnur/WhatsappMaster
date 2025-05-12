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

// CSRF tokenini yoxla
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token yoxlama uğursuz oldu");
    }
    
    // POST məlumatlarını əldə et
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Məlumatları yoxla
    if (empty($phone) || empty($message)) {
        $error = "Telefon nömrəsi və mesaj tələb olunur";
    } else {
        // WhatsApp Cloud API-ni istifadə edərək mesaj göndər
        $whatsapp = new WhatsAppCloudAPI();
        
        // API məlumatlarını yoxla
        if (!$whatsapp->hasValidCredentials()) {
            $error = "WhatsApp API məlumatları tənzimlənməyib. Lütfən admin ilə əlaqə saxlayın.";
        } else {
            // Mesajı göndər
            $result = $whatsapp->sendTextMessage($phone, $message);
            
            if ($result['success']) {
                // Mesaj uğurla göndərildi
                $success = "Mesaj WhatsApp-a göndərildi!";
                
                // Mesaj tarixçəsini yadda saxla
                try {
                    $stmt = $conn->prepare("INSERT INTO messages (user_id, phone, message, status, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $status = 'sent';
                    $stmt->execute([$_SESSION['user_id'], $phone, $message, $status]);
                } catch (PDOException $e) {
                    error_log("Message history save error: " . $e->getMessage());
                }
            } else {
                // Mesaj göndərmə uğursuz oldu
                $error = "Mesaj göndərilmədi: " . ($result['error'] ?? "Xəta məlumatı yoxdur");
            }
        }
    }
}

// Cari istifadəçinin əlaqələrini əldə et
try {
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY name");
    $stmt->execute([$_SESSION['user_id']]);
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Əlaqələr əldə edilərkən xəta baş verdi";
}

// Cari istifadəçinin şablonlarını əldə et
try {
    $stmt = $conn->prepare("SELECT * FROM templates WHERE user_id = ? ORDER BY name");
    $stmt->execute([$_SESSION['user_id']]);
    $templates = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Şablonlar əldə edilərkən xəta baş verdi";
}

// Yeni CSRF tokeni yarat
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Başlıq sənədini əlavə et
include 'includes/header.php';
?>

<div class="container mt-4">
    <h1><i class="fab fa-whatsapp text-success"></i> WhatsApp Mesaj Göndər (Cloud API)</h1>
    
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
    
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="card-title mb-0"><i class="fas fa-paper-plane"></i> Yeni Mesaj</h5>
        </div>
        
        <div class="card-body">
            <form action="send_whatsapp_api.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="mb-3">
                    <label for="contact" class="form-label">Əlaqə Seçin</label>
                    <select class="form-select" id="contact" onchange="selectContact(this.value)">
                        <option value="">Əlaqə seçin...</option>
                        <?php foreach ($contacts as $contact): ?>
                            <option value="<?php echo htmlspecialchars($contact['phone']); ?>" data-name="<?php echo htmlspecialchars($contact['name']); ?>">
                                <?php echo htmlspecialchars($contact['name']); ?> (<?php echo htmlspecialchars($contact['phone']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefon Nömrəsi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="994501234567">
                    </div>
                    <div class="form-text">Azərbaycan formatında nömrəni daxil edin</div>
                </div>
                
                <div class="mb-3">
                    <label for="template" class="form-label">Şablon Seçin (İstəyə bağlı)</label>
                    <select class="form-select" id="template" onchange="selectTemplate(this.value)">
                        <option value="">Şablon seçin...</option>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?php echo htmlspecialchars($template['content']); ?>">
                                <?php echo htmlspecialchars($template['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Mesaj</label>
                    <textarea class="form-control" id="message" name="message" rows="5" placeholder="Mesajınızı daxil edin..."></textarea>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fab fa-whatsapp"></i> WhatsApp ilə Göndər
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="messages.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Mesajlara Qayıt
        </a>
    </div>
</div>

<script>
function selectContact(phone) {
    if (phone) {
        document.getElementById('phone').value = phone;
    }
}

function selectTemplate(content) {
    if (content) {
        document.getElementById('message').value = content;
    }
}
</script>

<?php include 'includes/footer.php'; ?>