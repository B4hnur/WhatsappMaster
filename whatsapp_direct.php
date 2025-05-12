<?php
require_once 'db_config.php';
requireLogin();

include 'includes/header.php';
include 'includes/navbar.php';

// Check if we have message data in session
if (!isset($_SESSION['whatsapp_message_data'])) {
    header("Location: messages.php");
    exit();
}

$messageData = $_SESSION['whatsapp_message_data'];
$message = $messageData['message'];
$contacts = $messageData['contacts'];
$directSend = isset($messageData['direct_send']) ? $messageData['direct_send'] : false;

// Check WhatsApp connection status
$whatsapp_connected = false;
try {
    $stmt = $conn->prepare("SELECT status FROM whatsapp_sessions WHERE user_id = ? AND status = 'connected'");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) {
        $whatsapp_connected = true;
    }
} catch (PDOException $e) {
    error_log("Error checking WhatsApp connection: " . $e->getMessage());
}

// If WhatsApp is not connected, redirect to the standard page
if (!$whatsapp_connected) {
    header("Location: whatsapp_redirect.php");
    exit();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Clear the message data from session after processing (will be done via JavaScript)
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fab fa-whatsapp me-2"></i>Birbaşa WhatsApp Mesajları</h4>
                </div>
                <div class="card-body">
                    <h5 class="mb-4">Mesajlar birbaşa göndərilir</h5>
                    
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle me-2"></i>WhatsApp hesabınız bağlı olduğu üçün mesajlar birbaşa göndərilir.</p>
                    </div>
                    
                    <div class="progress mb-4">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="progress-bar" style="width: 0%"></div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Əlaqə</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="status-table">
                                <?php foreach ($contacts as $contact): ?>
                                <tr id="contact-row-<?php echo $contact['id']; ?>" data-contact-id="<?php echo $contact['id']; ?>" data-phone="<?php echo htmlspecialchars($contact['phone']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($contact['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($contact['phone']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Gözləyir</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-4">
                        <div id="sending-container">
                            <span class="whatsapp-loader me-2"></span>
                            <span id="sending-status">Mesajlar göndərilir...</span>
                        </div>
                        <div id="complete-container" class="d-none">
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success fa-3x"></i>
                            </div>
                            <p class="mb-3">Bütün mesajlar uğurla göndərildi!</p>
                            <a href="messages.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i>Mesajlara qayıt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf_token = "<?php echo $csrf_token; ?>";
        const message = <?php echo json_encode($message); ?>;
        const contactRows = document.querySelectorAll('#status-table tr');
        let currentIndex = 0;
        const totalContacts = contactRows.length;
        
        // Update progress bar
        function updateProgress(current, total) {
            const percentage = Math.round((current / total) * 100);
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-bar').setAttribute('aria-valuenow', percentage);
        }
        
        // Update contact status
        function updateContactStatus(contactId, status, badgeClass) {
            const row = document.getElementById('contact-row-' + contactId);
            if (row) {
                const statusCell = row.querySelector('td:last-child');
                statusCell.innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;
            }
        }
        
        // Send direct WhatsApp message via API
        function sendDirectMessage(contactId, phone) {
            updateContactStatus(contactId, 'Göndərilir...', 'bg-info');
            
            // AJAX call to send message
            fetch('whatsapp_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=send_message&csrf_token=${csrf_token}&phone=${encodeURIComponent(phone)}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateContactStatus(contactId, 'Göndərildi', 'bg-success');
                } else {
                    updateContactStatus(contactId, 'Xəta: ' + (data.error || 'Naməlum'), 'bg-danger');
                }
                
                // Process next contact
                currentIndex++;
                updateProgress(currentIndex, totalContacts);
                
                if (currentIndex < totalContacts) {
                    processNextContact();
                } else {
                    // All messages sent
                    document.getElementById('sending-container').classList.add('d-none');
                    document.getElementById('complete-container').classList.remove('d-none');
                    
                    // Clear session data
                    fetch('clear_session_data.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `csrf_token=${csrf_token}&key=whatsapp_message_data`
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                updateContactStatus(contactId, 'Şəbəkə xətası', 'bg-danger');
                
                // Continue with next contact despite error
                currentIndex++;
                updateProgress(currentIndex, totalContacts);
                
                if (currentIndex < totalContacts) {
                    processNextContact();
                } else {
                    document.getElementById('sending-container').classList.add('d-none');
                    document.getElementById('complete-container').classList.remove('d-none');
                }
            });
        }
        
        // Process contacts one by one
        function processNextContact() {
            if (currentIndex < totalContacts) {
                const row = contactRows[currentIndex];
                const contactId = row.getAttribute('data-contact-id');
                const phone = row.getAttribute('data-phone');
                
                // Small delay to avoid too many concurrent requests
                setTimeout(() => {
                    sendDirectMessage(contactId, phone);
                }, 300);
            }
        }
        
        // Start sending messages
        setTimeout(processNextContact, 1000);
    });
</script>

<?php include 'includes/footer.php'; ?>