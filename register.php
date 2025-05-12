<?php
require_once 'db_config.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Check if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Təhlükəsizlik xətası! Zəhmət olmasa yenidən cəhd edin.";
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $whatsapp_number = trim($_POST['whatsapp_number']);
        
        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "Zəhmət olmasa bütün vacib sahələri doldurun.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Zəhmət olmasa düzgün email ünvanı daxil edin.";
        } elseif (strlen($password) < 6) {
            $error = "Şifrə ən azı 6 simvol olmalıdır.";
        } elseif ($password !== $confirm_password) {
            $error = "Şifrələr uyğun gəlmir.";
        } else {
            try {
                // Check if username already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->rowCount() > 0) {
                    $error = "Bu istifadəçi adı artıq istifadə olunur.";
                } else {
                    // Check if email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->rowCount() > 0) {
                        $error = "Bu email ünvanı artıq istifadə olunur.";
                    } else {
                        // Hash the password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user
                        $stmt = $conn->prepare("INSERT INTO users (username, email, password, whatsapp_number) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $email, $hashed_password, $whatsapp_number]);
                        
                        $success = "Qeydiyyat uğurla tamamlandı! İndi <a href='login.php'>giriş</a> edə bilərsiniz.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Xəta baş verdi: " . $e->getMessage();
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Qeydiyyat
                    </h3>
                </div>
                <div class="card-body p-5">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">İstifadəçi adı</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email ünvanı</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="whatsapp_number" class="form-label">WhatsApp nömrəsi (məs. 994501234567)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number">
                            </div>
                            <div class="form-text">Bu nömrənizdən mesajlar göndəriləcək</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifrə</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-text">Şifrə ən azı 6 simvol olmalıdır</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Şifrəni təsdiqlə</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Qeydiyyatdan keç
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <p class="mb-0">Hesabınız var? <a href="login.php">Giriş edin</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
