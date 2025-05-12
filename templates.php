<?php
require_once 'db_config.php';
requireLogin();

include 'includes/header.php';
include 'includes/navbar.php';

// Get message templates for the current user
try {
    $stmt = $conn->prepare("SELECT * FROM message_templates WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $templates = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Xəta: " . $e->getMessage();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Mesaj Şablonları</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
            <i class="fas fa-plus me-1"></i> Yeni şablon
        </button>
    </div>
    
    <div class="row">
        <?php if (count($templates) > 0): ?>
            <?php foreach ($templates as $template): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo htmlspecialchars($template['name']); ?></h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" id="dropdownMenuButton<?php echo $template['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton<?php echo $template['id']; ?>">
                                        <li>
                                            <button class="dropdown-item" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#editTemplateModal"
                                                   data-id="<?php echo $template['id']; ?>"
                                                   data-name="<?php echo htmlspecialchars($template['name']); ?>"
                                                   data-content="<?php echo htmlspecialchars($template['content']); ?>">
                                                <i class="fas fa-edit me-1"></i> Düzəliş et
                                            </button>
                                        </li>
                                        <li>
                                            <a class="dropdown-item delete-confirm" href="delete_template.php?id=<?php echo $template['id']; ?>&csrf_token=<?php echo $csrf_token; ?>">
                                                <i class="fas fa-trash-alt me-1"></i> Sil
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                <?php echo nl2br(htmlspecialchars($template['content'])); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php 
                                        $date = new DateTime($template['created_at']);
                                        echo $date->format('d.m.Y H:i'); 
                                    ?>
                                </small>
                                <a href="messages.php?template_id=<?php echo $template['id']; ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-paper-plane me-1"></i> İstifadə et
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-alt fa-4x mb-3 text-muted"></i>
                        <h4>Şablon tapılmadı</h4>
                        <p>Mesaj göndərmək üçün şablonlar əlavə edin</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTemplateModal">
                            <i class="fas fa-plus me-1"></i> Yeni şablon əlavə et
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Template Modal -->
<div class="modal fade" id="addTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Yeni şablon əlavə et</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_template.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Şablon adı</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Şablon mətni</label>
                        <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
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

<!-- Edit Template Modal -->
<div class="modal fade" id="editTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Şablonu düzəliş et</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="edit_template.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="id" id="edit-id">
                    
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Şablon adı</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit-content" class="form-label">Şablon mətni</label>
                        <textarea class="form-control" id="edit-content" name="content" rows="5" required></textarea>
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
    document.getElementById('editTemplateModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const content = button.getAttribute('data-content');
        
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-content').value = content;
    });
</script>

<?php include 'includes/footer.php'; ?>
