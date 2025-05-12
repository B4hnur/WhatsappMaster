<?php
require_once 'db_config.php';
requireLogin();

include 'includes/header.php';
include 'includes/navbar.php';

// Get contacts for the current user
try {
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Xəta: " . $e->getMessage();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Əlaqələr</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
            <i class="fas fa-plus me-1"></i> Yeni əlaqə
        </button>
    </div>
    
    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="search-contacts" placeholder="Ad və ya nömrəyə görə axtar...">
            </div>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (count($contacts) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ad</th>
                                <th>Telefon</th>
                                <th>Qeydlər</th>
                                <th>Yaradılma tarixi</th>
                                <th>Əməliyyatlar</th>
                            </tr>
                        </thead>
                        <tbody class="contact-list">
                            <?php foreach ($contacts as $contact): ?>
                                <tr class="contact-item">
                                    <td class="contact-name"><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td class="contact-phone"><?php echo htmlspecialchars($contact['phone']); ?></td>
                                    <td>
                                        <?php
                                            $shortNotes = mb_strlen($contact['notes']) > 50 
                                                ? mb_substr($contact['notes'], 0, 50) . '...' 
                                                : $contact['notes'];
                                            echo htmlspecialchars($shortNotes);
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $date = new DateTime($contact['created_at']);
                                            echo $date->format('d.m.Y H:i'); 
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="messages.php?contact_id=<?php echo $contact['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editContactModal"
                                                data-id="<?php echo $contact['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($contact['name']); ?>"
                                                data-phone="<?php echo htmlspecialchars($contact['phone']); ?>"
                                                data-notes="<?php echo htmlspecialchars($contact['notes']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="delete_contact.php?id=<?php echo $contact['id']; ?>&csrf_token=<?php echo $csrf_token; ?>" 
                                               class="btn btn-sm btn-danger delete-confirm">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-address-book fa-4x mb-3 text-muted"></i>
                    <h4>Əlaqə siyahınız boşdur</h4>
                    <p>Mesaj göndərmək üçün əlaqələrinizi əlavə edin.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                        <i class="fas fa-plus me-1"></i> Yeni əlaqə əlavə et
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Yeni əlaqə əlavə et</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_contact.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Ad</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Telefon nömrəsi</label>
                        <input type="text" class="form-control" id="phone" name="phone" required
                               placeholder="Məs: 994501234567">
                        <div class="form-text">WhatsApp üçün nömrəni beynəlxalq formatda daxil edin (994...)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Qeydlər (istəyə bağlı)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bağla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Saxla
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Əlaqə düzəliş et</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="edit_contact.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="edit-id">
                    
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Ad</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-phone" class="form-label">Telefon nömrəsi</label>
                        <input type="text" class="form-control" id="edit-phone" name="phone" required>
                        <div class="form-text">WhatsApp üçün nömrəni beynəlxalq formatda daxil edin (994...)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-notes" class="form-label">Qeydlər</label>
                        <textarea class="form-control" id="edit-notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bağla</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save me-1"></i> Yenilə
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Set up edit modal values
    document.getElementById('editContactModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const phone = button.getAttribute('data-phone');
        const notes = button.getAttribute('data-notes');
        
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-phone').value = phone;
        document.getElementById('edit-notes').value = notes;
    });
</script>

<?php include 'includes/footer.php'; ?>
