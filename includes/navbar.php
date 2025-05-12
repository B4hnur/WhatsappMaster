<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fab fa-whatsapp me-2"></i>WhatsApp Mesaj Sistemi
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Ana Səhifə</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php"><i class="fas fa-address-book me-1"></i>Əlaqələr</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php"><i class="fas fa-envelope me-1"></i>Mesajlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="templates.php"><i class="fas fa-file-alt me-1"></i>Şablonlar</a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="whatsappDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="whatsapp_connect.php">
                                    <i class="fas fa-qrcode me-1"></i>QR ilə bağlan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="whatsapp_api_status.php">
                                    <i class="fas fa-cloud me-1"></i>API Statusu
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="send_whatsapp_api.php">
                                    <i class="fas fa-paper-plane me-1"></i>API ilə mesaj göndər
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <?php
                    // Check WhatsApp connection status
                    try {
                        $whatsapp_status = 'disconnected';
                        $stmt = $conn->prepare("SELECT status FROM whatsapp_sessions WHERE user_id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $session = $stmt->fetch();
                        
                        if ($session) {
                            $whatsapp_status = $session['status'];
                        }
                    } catch (PDOException $e) {
                        $whatsapp_status = 'error';
                    }
                    ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="whatsapp_connect.php">
                            <i class="fab fa-whatsapp me-1"></i>
                            <?php if ($whatsapp_status == 'connected'): ?>
                                <span class="badge bg-success">Bağlı</span>
                            <?php elseif ($whatsapp_status == 'pending'): ?>
                                <span class="badge bg-warning text-dark">Gözləyir</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Bağlı deyil</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Çıxış</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Ana Səhifə</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Giriş</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Qeydiyyat</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
