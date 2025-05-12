<?php
require_once 'db_config.php';
requireLogin();

include 'includes/header.php';
include 'includes/navbar.php';

// Get templates
try {
    $stmt = $conn->prepare("SELECT * FROM message_templates WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $templates = $stmt->fetchAll();
    
    // Get contacts
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $contacts = $stmt->fetchAll();
    
    // Get selected contact if any
    $selectedContactId = null;
    if (isset($_GET['contact_id'])) {
        $selectedContactId = (int)$_GET['contact_id'];
    }
    
    // Get user's WhatsApp number
    $stmt = $conn->prepare("SELECT whatsapp_number FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $whatsappNumber = $user['whatsapp_number'];
    
} catch (PDOException $e) {
    echo "Xəta: " . $e->getMessage();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1 class="h3 mb-0">Mesajlar Göndər</h1>
            <p class="text-muted">WhatsApp vasitəsilə müştərilərinizə mesajlar göndərin.</p>
        </div>
    </div>
    
    <?php if (empty($whatsappNumber)): ?>
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Diqqət!</strong> WhatsApp nömrəniz qeyd olunmayıb. Mesaj göndərmək üçün profil parametrlərində nömrənizi qeyd edin.
    </div>
    <?php endif; ?>
    
    <form id="send-message-form" action="send_message.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="selected-template-id" id="selected-template-id" value="">
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Mesaj Mətni</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="message-text" class="form-label">Mesajınızı yazın</label>
                            <textarea class="form-control" id="message-text" name="message" rows="5" required></textarea>
                            <div class="d-flex justify-content-between mt-2">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i> Yuxarıda seçdiyiniz şablonları istifadə edə bilərsiniz
                                </div>
                                <div>
                                    <span id="char-count">0</span> simvol
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" id="preview-message">
                                <i class="fas fa-eye me-1"></i> Önizləmə
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fab fa-whatsapp me-1"></i> Mesaj Göndər
                            </button>
                        </div>
                        
                        <div id="preview-container" class="mt-4 d-none">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Mesaj Önizləməsi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="message-bubble message-sent" id="message-preview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Templates Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Şablonlar</h5>
                        <a href="templates.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (count($templates) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($templates as $template): ?>
                                    <div class="list-group-item list-group-item-action template-card" 
                                         data-template-id="<?php echo $template['id']; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($template['name']); ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-file-alt"></i>
                                            </small>
                                        </div>
                                        <div class="d-none" id="template-content-<?php echo $template['id']; ?>">
                                            <?php echo htmlspecialchars($template['content']); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php 
                                                $shortContent = mb_strlen($template['content']) > 50 
                                                    ? mb_substr($template['content'], 0, 50) . '...' 
                                                    : $template['content'];
                                                echo htmlspecialchars($shortContent); 
                                            ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-file-alt fa-2x mb-2 text-muted"></i>
                                <p class="mb-2">Şablon bulunamadı</p>
                                <a href="templates.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> Şablon əlavə et
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Contacts Section -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Əlaqələr</h5>
                        <span class="badge bg-primary" id="selected-count">0</span>
                    </div>
                    <div class="card-body">
                        <?php if (count($contacts) > 0): ?>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all-contacts">
                                    <label class="form-check-label" for="select-all-contacts">
                                        Hamısını seç
                                    </label>
                                </div>
                            </div>
                            
                            <div class="list-group contact-list" style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($contacts as $contact): ?>
                                    <div class="list-group-item">
                                        <div class="form-check custom-checkbox">
                                            <input class="form-check-input contact-checkbox" 
                                                   type="checkbox" 
                                                   name="contact_ids[]" 
                                                   value="<?php echo $contact['id']; ?>"
                                                   id="contact-<?php echo $contact['id']; ?>"
                                                   <?php echo ($selectedContactId && $selectedContactId == $contact['id']) ? 'checked' : ''; ?>>
                                            <label class="form-check-label d-flex justify-content-between" for="contact-<?php echo $contact['id']; ?>">
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($contact['name']); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($contact['phone']); ?></div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-address-book fa-2x mb-2 text-muted"></i>
                                <p class="mb-2">Əlaqə tapılmadı</p>
                                <a href="contacts.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> Əlaqə əlavə et
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <!-- Sending indicator -->
    <div id="sending-indicator" class="position-fixed top-50 start-50 translate-middle bg-white p-4 rounded shadow d-none">
        <div class="d-flex align-items-center">
            <div class="whatsapp-loader me-3"></div>
            <div>
                <h5 class="mb-1">Mesajlar göndərilir</h5>
                <p class="mb-0 text-muted">Zəhmət olmasa gözləyin...</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
