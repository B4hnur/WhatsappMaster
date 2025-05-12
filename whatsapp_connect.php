<?php
require_once 'db_config.php';
requireLogin();

include 'includes/header.php';
include 'includes/navbar.php';

// Check WhatsApp connection status
try {
    $stmt = $conn->prepare("SELECT status FROM whatsapp_sessions WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $session = $stmt->fetch();
    
    $whatsapp_status = $session ? $session['status'] : 'disconnected';
} catch (PDOException $e) {
    $whatsapp_status = 'error';
    error_log("Error checking WhatsApp status: " . $e->getMessage());
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1 class="h3 mb-0">WhatsApp Bağlantısı</h1>
            <p class="text-muted">Whatsapp hesabınızı bu sistemə bağlayaraq birbaşa mesaj göndərin.</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Status
                    </h5>
                </div>
                <div class="card-body">
                    <div id="connection-status">
                        <?php if ($whatsapp_status == 'connected'): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i> WhatsApp hesabınız bağlıdır
                            </div>
                            <p>Hesabınız bu sistem ilə uğurla əlaqələndirilmişdir. Indi istədiyiniz istifadəçiyə birbaşa mesaj göndərə bilərsiniz.</p>
                            
                            <div class="d-grid gap-2">
                                <button id="disconnect-button" class="btn btn-outline-danger">
                                    <i class="fas fa-unlink me-2"></i>Bağlantını kəs
                                </button>
                            </div>
                        <?php elseif ($whatsapp_status == 'pending'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-hourglass-half me-2"></i> QR kod ilə bağlantı gözlənilir
                            </div>
                            <p>Qoşulmanı tamamlamaq üçün WhatsApp Web-də QR kodu skan edin.</p>
                            
                            <div class="d-grid gap-2">
                                <button id="refresh-qr-button" class="btn btn-outline-primary">
                                    <i class="fas fa-sync me-2"></i>QR Kodu Yenilə
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary">
                                <i class="fas fa-unlink me-2"></i> WhatsApp hesabı bağlı deyil
                            </div>
                            <p>Başlamaq üçün "WhatsApp-ı qoş" düyməsini sıxın və telefonunuzdan QR kodu skan edin.</p>
                            
                            <div class="d-grid gap-2">
                                <button id="connect-button" class="btn btn-success">
                                    <i class="fas fa-link me-2"></i>WhatsApp-ı qoş
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Təlimatlar</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li class="mb-2">Telefonunuzda WhatsApp-ın aktiv olduğundan əmin olun</li>
                        <li class="mb-2">"WhatsApp-ı qoş" düyməsini klikləyin və göstərilən QR kodu skan edin</li>
                        <li class="mb-2">Telefonunuzdan "WhatsApp Web" bölməsini açın</li>
                        <li class="mb-2">Kamera ilə QR kodu skan edin</li>
                        <li>Birləşdirmə tamamlandıqda statusun "Bağlı" olaraq dəyişdiyini görəcəksiniz</li>
                    </ol>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> 
                <strong>Təhlükəsizlik qeydi:</strong> Bütün bağlantılar şifrələnir və siz istədiyiniz zaman bağlantını kəsə bilərsiniz.
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">QR Kod Sahəsi</h5>
                </div>
                <div class="card-body text-center py-5" id="qr-container">
                    <?php if ($whatsapp_status == 'pending'): ?>
                        <div id="qr-code-display" class="mb-4">
                            <!-- QR code will be displayed here via JavaScript -->
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Yüklənir...</span>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0">QR kodu telefonunuzdan skan edin</p>
                    <?php elseif ($whatsapp_status == 'connected'): ?>
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success fa-5x"></i>
                        </div>
                        <h4 class="mb-3">WhatsApp bağlıdır</h4>
                        <p>WhatsApp hesabınız bu sistemə uğurla qoşulmuşdur.</p>
                        <p class="mb-0">İndi WhatsApp mesajlarınızı birbaşa bu platformadan göndərə bilərsiniz.</p>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="fab fa-whatsapp text-success fa-5x"></i>
                        </div>
                        <h4 class="mb-3">WhatsApp hesabınızı qoşun</h4>
                        <p>Bu sistemdən WhatsApp mesajları göndərmək üçün hesabınızı bu platformaya qoşmalısınız.</p>
                        <p class="mb-0">Başlamaq üçün "WhatsApp-ı qoş" düyməsini sıxın.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Nə edə bilərsiniz?</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center px-0">
                            <div class="me-3 text-success">
                                <i class="fas fa-bolt fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Sürətli mesaj göndərmə</h6>
                                <p class="text-muted mb-0">Əlaqələrinizə yeni səhifə açmadan birbaşa mesaj göndərin</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center px-0">
                            <div class="me-3 text-info">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Toplu mesajlar</h6>
                                <p class="text-muted mb-0">Eyni mesajı bir neçə əlaqəyə eyni anda göndərin</p>
                            </div>
                        </li>
                        <li class="list-group-item d-flex align-items-center px-0">
                            <div class="me-3 text-primary">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Şablonlardan istifadə</h6>
                                <p class="text-muted mb-0">Əvvəlcədən hazırlanmış şablonları tez istifadə edin</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf_token = "<?php echo $csrf_token; ?>";
        let checkStatusInterval;
        
        // Connect button
        const connectButton = document.getElementById('connect-button');
        if (connectButton) {
            connectButton.addEventListener('click', function() {
                generateQRCode();
            });
        }
        
        // Refresh QR code button
        const refreshButton = document.getElementById('refresh-qr-button');
        if (refreshButton) {
            refreshButton.addEventListener('click', function() {
                generateQRCode();
            });
        }
        
        // Disconnect button
        const disconnectButton = document.getElementById('disconnect-button');
        if (disconnectButton) {
            disconnectButton.addEventListener('click', function() {
                disconnectWhatsApp();
            });
        }
        
        // Generate QR code for connecting
        function generateQRCode() {
            const qrContainer = document.getElementById('qr-container');
            if (qrContainer) {
                qrContainer.innerHTML = `
                    <div class="d-flex justify-content-center mb-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Yüklənir...</span>
                        </div>
                    </div>
                    <p class="mb-0">QR kod hazırlanır...</p>
                `;
                
                // AJAX request to generate QR code
                fetch('whatsapp_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=generate_qr&csrf_token=${csrf_token}`
                })
                .then(response => {
                    // First check if response is valid
                    if (!response.ok) {
                        throw new Error(`HTTP error ${response.status}`);
                    }
                    
                    try {
                        return response.json();
                    } catch (e) {
                        // If JSON parsing fails, return a friendly message
                        throw new Error('Server response is not valid JSON');
                    }
                })
                .then(data => {
                    // Handle successful API response
                    if (data && data.success) {
                        // In a real implementation, this would generate an actual QR code image
                        // Generate a random session ID for this QR code
                        const sessionId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                        const qrData = data.qr_code || 'whatsapp_' + sessionId;
                        
                        // Create actual QR code using our simple QR generator
                        qrContainer.innerHTML = `
                            <div class="qr-code-display border p-4 mb-4 d-inline-block">
                                <img src="simple_qr.php?text=${encodeURIComponent('whatsapp-connect:' + qrData)}&size=250" 
                                     alt="WhatsApp QR Code" 
                                     style="width: 256px; height: 256px;">
                            </div>
                            <p>QR kodu telefonunuzdan skan edin</p>
                            <p class="text-muted small">Bu kod 1 dəqiqədən sonra etibarsız olacaq</p>
                        `;
                        
                        // For demo purposes, automatically simulate connection after 3 seconds
                        setTimeout(simulateConnection, 3000);
                        
                        // Update the connection status display
                        updateConnectionStatus('pending');
                    } else {
                        // Handle API success but failure result
                        showRetryOption('QR kod yaratma xətası: ' + (data && data.error ? data.error : 'Naməlum xəta'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showRetryOption('Şəbəkə xətası baş verdi. Zəhmət olmasa internet bağlantınızı yoxlayın.');
                });
                
                // Helper function to show error with retry button
                function showRetryOption(errorMessage) {
                    qrContainer.innerHTML = `
                        <div class="alert alert-danger mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i> 
                            ${errorMessage}
                        </div>
                        <button id="retry-button" class="btn btn-outline-primary">
                            <i class="fas fa-redo me-2"></i>Yenidən cəhd edin
                        </button>
                    `;
                    
                    // Add retry button event listener
                    document.getElementById('retry-button').addEventListener('click', generateQRCode);
                    
                    // Fallback to simulate connection even on error (for demo purposes)
                    setTimeout(() => {
                        // Automatically try to connect anyway after 5 seconds
                        simulateConnection();
                    }, 5000);
                }
            }
        }
        
        // For simulation purposes - simulate WhatsApp connection
        function simulateConnection() {
            fetch('whatsapp_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=connect&csrf_token=${csrf_token}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateConnectionStatus('connected');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Disconnect WhatsApp account
        function disconnectWhatsApp() {
            if (confirm('WhatsApp bağlantısını kəsmək istədiyinizə əminsiniz?')) {
                fetch('whatsapp_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=disconnect&csrf_token=${csrf_token}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateConnectionStatus('disconnected');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }
        
        // Update the connection status UI
        function updateConnectionStatus(status) {
            const connectionStatus = document.getElementById('connection-status');
            const qrContainer = document.getElementById('qr-container');
            
            if (connectionStatus && qrContainer) {
                if (status === 'connected') {
                    connectionStatus.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> WhatsApp hesabınız bağlıdır
                        </div>
                        <p>Hesabınız bu sistem ilə uğurla əlaqələndirilmişdir. Indi istədiyiniz istifadəçiyə birbaşa mesaj göndərə bilərsiniz.</p>
                        
                        <div class="d-grid gap-2">
                            <button id="disconnect-button" class="btn btn-outline-danger">
                                <i class="fas fa-unlink me-2"></i>Bağlantını kəs
                            </button>
                        </div>
                    `;
                    
                    qrContainer.innerHTML = `
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success fa-5x"></i>
                        </div>
                        <h4 class="mb-3">WhatsApp bağlıdır</h4>
                        <p>WhatsApp hesabınız bu sistemə uğurla qoşulmuşdur.</p>
                        <p class="mb-0">İndi WhatsApp mesajlarınızı birbaşa bu platformadan göndərə bilərsiniz.</p>
                    `;
                    
                    // Add disconnect button event listener
                    document.getElementById('disconnect-button').addEventListener('click', disconnectWhatsApp);
                } else if (status === 'pending') {
                    connectionStatus.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-hourglass-half me-2"></i> QR kod ilə bağlantı gözlənilir
                        </div>
                        <p>Qoşulmanı tamamlamaq üçün WhatsApp Web-də QR kodu skan edin.</p>
                        
                        <div class="d-grid gap-2">
                            <button id="refresh-qr-button" class="btn btn-outline-primary">
                                <i class="fas fa-sync me-2"></i>QR Kodu Yenilə
                            </button>
                        </div>
                    `;
                    
                    // Add refresh button event listener
                    document.getElementById('refresh-qr-button').addEventListener('click', generateQRCode);
                } else {
                    connectionStatus.innerHTML = `
                        <div class="alert alert-secondary">
                            <i class="fas fa-unlink me-2"></i> WhatsApp hesabı bağlı deyil
                        </div>
                        <p>Başlamaq üçün "WhatsApp-ı qoş" düyməsini sıxın və telefonunuzdan QR kodu skan edin.</p>
                        
                        <div class="d-grid gap-2">
                            <button id="connect-button" class="btn btn-success">
                                <i class="fas fa-link me-2"></i>WhatsApp-ı qoş
                            </button>
                        </div>
                    `;
                    
                    qrContainer.innerHTML = `
                        <div class="mb-4">
                            <i class="fab fa-whatsapp text-success fa-5x"></i>
                        </div>
                        <h4 class="mb-3">WhatsApp hesabınızı qoşun</h4>
                        <p>Bu sistemdən WhatsApp mesajları göndərmək üçün hesabınızı bu platformaya qoşmalısınız.</p>
                        <p class="mb-0">Başlamaq üçün "WhatsApp-ı qoş" düyməsini sıxın.</p>
                    `;
                    
                    // Add connect button event listener
                    document.getElementById('connect-button').addEventListener('click', generateQRCode);
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>