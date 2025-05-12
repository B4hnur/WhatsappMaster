<?php
require_once 'db_config.php';
requireLogin();

include 'includes/header.php';
include 'includes/navbar.php';

// Get user statistics
try {
    // Get total contacts
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM contacts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalContacts = $stmt->fetch()['total'];
    
    // Get total templates
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM message_templates WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalTemplates = $stmt->fetch()['total'];
    
    // Get total messages sent
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM message_history WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalMessages = $stmt->fetch()['total'];
    
    // Get recent message history
    $stmt = $conn->prepare("
        SELECT m.id, m.message, m.sent_at, c.name as contact_name, c.phone as contact_phone 
        FROM message_history m
        JOIN contacts c ON m.contact_id = c.id
        WHERE m.user_id = ?
        ORDER BY m.sent_at DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recentMessages = $stmt->fetchAll();
    
    // Get user info
    $stmt = $conn->prepare("SELECT whatsapp_number FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $whatsappNumber = $user['whatsapp_number'];
    
} catch (PDOException $e) {
    echo "Xəta: " . $e->getMessage();
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0">Ana Səhifə</h1>
            <p class="text-muted">Xoş gəlmisiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>! Aşağıda sisteminizin ümumi vəziyyətini görə bilərsiniz.</p>
        </div>
    </div>
    
    <?php 
    // Check WhatsApp connection status
    $whatsapp_status = 'disconnected';
    try {
        $stmt = $conn->prepare("SELECT status FROM whatsapp_sessions WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $session = $stmt->fetch();
        if ($session) {
            $whatsapp_status = $session['status'];
        }
    } catch (PDOException $e) {
        error_log("Error checking WhatsApp status: " . $e->getMessage());
    }
    ?>
    
    <?php if ($whatsapp_status == 'connected'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>WhatsApp bağlıdır!</strong> Artıq əlaqələrinizə birbaşa mesaj göndərə bilərsiniz.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php elseif (empty($whatsappNumber)): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Diqqət!</strong> WhatsApp nömrənizi təyin etməmisiniz. Mesaj göndərmək üçün profil parametrlərinizdə nömrənizi təyin edin.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php else: ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>WhatsApp bağlantısı!</strong> Mesajları birbaşa göndərmək üçün <a href="whatsapp_connect.php" class="alert-link">WhatsApp-ı bağlayın</a>.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="card-title mb-0">Əlaqələr</h5>
                            <small class="text-muted">Ümumi əlaqələrin sayı</small>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="fas fa-address-book text-primary fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="display-4 mb-0"><?php echo $totalContacts; ?></h2>
                    <div class="mt-3">
                        <a href="contacts.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Əlaqələri göstər
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="card-title mb-0">Şablonlar</h5>
                            <small class="text-muted">Ümumi şablonların sayı</small>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="fas fa-file-alt text-success fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="display-4 mb-0"><?php echo $totalTemplates; ?></h2>
                    <div class="mt-3">
                        <a href="templates.php" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-eye me-1"></i>Şablonları göstər
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="card-title mb-0">Mesajlar</h5>
                            <small class="text-muted">Göndərilən mesajların sayı</small>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="fas fa-comment-dots text-info fa-2x"></i>
                        </div>
                    </div>
                    <h2 class="display-4 mb-0"><?php echo $totalMessages; ?></h2>
                    <div class="mt-3">
                        <a href="messages.php" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye me-1"></i>Mesaj tarixçəsi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-8 mb-4 mb-md-0">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Son mesajlar</h5>
                </div>
                <div class="card-body">
                    <?php if (count($recentMessages) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Əlaqə</th>
                                        <th>Mesaj</th>
                                        <th>Tarix</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentMessages as $message): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($message['contact_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($message['contact_phone']); ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                    $shortMessage = mb_strlen($message['message']) > 30 
                                                        ? mb_substr($message['message'], 0, 30) . '...' 
                                                        : $message['message'];
                                                    echo htmlspecialchars($shortMessage); 
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $date = new DateTime($message['sent_at']);
                                                    echo $date->format('d.m.Y H:i'); 
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="messages.php" class="btn btn-outline-primary">
                                <i class="fas fa-history me-1"></i>Bütün mesajları göstər
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-comment-slash fa-3x mb-3 text-muted"></i>
                            <p>Hələ heç bir mesaj göndərməmisiniz.</p>
                            <a href="messages.php" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Mesaj göndər
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Sürətli əməliyyatlar</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="contacts.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Əlaqə əlavə et
                        </a>
                        <a href="templates.php" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-file-alt me-2"></i>Şablon əlavə et
                        </a>
                        <a href="messages.php" class="btn btn-outline-info btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Mesaj göndər
                        </a>
                        
                        <?php if ($whatsapp_status != 'connected'): ?>
                        <a href="whatsapp_connect.php" class="btn btn-outline-success btn-lg">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp-ı bağla
                        </a>
                        <?php else: ?>
                        <a href="whatsapp_connect.php" class="btn btn-success btn-lg">
                            <i class="fab fa-whatsapp me-2"></i>WhatsApp bağlıdır
                            <span class="badge bg-light text-success ms-2"><i class="fas fa-check"></i></span>
                        </a>
                        <?php endif; ?>
                        
                        <a href="whatsapp_api_status.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-cloud me-2"></i>WhatsApp API Statusu
                        </a>
                        
                        <a href="send_whatsapp_api.php" class="btn btn-outline-danger btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>API ilə Mesaj Göndər
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
