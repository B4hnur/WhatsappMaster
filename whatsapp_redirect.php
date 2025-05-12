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

// Clear the message data from session to prevent repeated openings
unset($_SESSION['whatsapp_message_data']);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fab fa-whatsapp me-2"></i>WhatsApp Mesajları</h4>
                </div>
                <div class="card-body">
                    <h5 class="mb-4">Mesajlar göndərilir</h5>
                    
                    <div class="alert alert-info">
                        <p><i class="fas fa-info-circle me-2"></i>WhatsApp pəncərələri açılacaq. Hər bir əlaqə üçün "Göndər" düyməsini klikləməlisiniz.</p>
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
                                <tr id="contact-row-<?php echo $contact['id']; ?>">
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
                        <a href="messages.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>Mesajlara qayıt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // WhatsApp message sending queue
    const contacts = <?php echo json_encode($contacts); ?>;
    const message = <?php echo json_encode($message); ?>;
    let currentIndex = 0;
    const totalContacts = contacts.length;
    
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
    
    // Send WhatsApp message one by one
    function sendNextMessage() {
        if (currentIndex < totalContacts) {
            const contact = contacts[currentIndex];
            
            // Update status to processing
            updateContactStatus(contact.id, 'Göndərilir...', 'bg-info');
            
            // Send to WhatsApp
            const phone = contact.phone.replace(/\D/g, '');
            const encodedMessage = encodeURIComponent(message);
            const whatsappUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodedMessage}`;
            
            // Open WhatsApp in new window
            const whatsappWindow = window.open(whatsappUrl, '_blank');
            
            // Update status after sending
            updateContactStatus(contact.id, 'Göndərildi', 'bg-success');
            
            // Increment index and update progress
            currentIndex++;
            updateProgress(currentIndex, totalContacts);
            
            // Schedule next message with delay
            if (currentIndex < totalContacts) {
                setTimeout(sendNextMessage, 1000);
            }
        }
    }
    
    // Start sending messages when page loads
    window.addEventListener('load', function() {
        setTimeout(sendNextMessage, 1000);
    });
</script>

<?php include 'includes/footer.php'; ?>
