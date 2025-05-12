import sys
import os
import time
import subprocess
import io
import qrcode
from flask import Flask, request, send_from_directory, render_template_string, Response, redirect, make_response
import logging
from PIL import Image

# Configure logging
logging.basicConfig(level=logging.INFO, 
                   format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = Flask(__name__)

# Start PHP server
php_process = None

def start_php_server():
    global php_process
    
    # First check if PHP is installed
    try:
        version_output = subprocess.check_output(['php', '-v'], stderr=subprocess.STDOUT, text=True)
        logger.info(f"PHP is installed: {version_output.splitlines()[0]}")
    except (subprocess.SubprocessError, FileNotFoundError) as e:
        logger.error(f"PHP is not available: {e}")
        return False
    
    # Run the create_tables.php script to ensure tables exist
    try:
        logger.info("Running create_tables.php to ensure database schema...")
        subprocess.run(['php', 'create_tables.php'], check=True, capture_output=True, text=True)
        logger.info("Tables created or verified successfully")
    except subprocess.SubprocessError as e:
        logger.warning(f"Could not run create_tables.php: {e}")
    
    # Start PHP built-in server
    try:
        logger.info("Starting PHP server...")
        cmd = ['php', '-S', '127.0.0.1:8000', '-t', '.']
        php_process = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        time.sleep(2)  # Give it some time to start
        
        # Check if process is running
        if php_process.poll() is None:
            logger.info("PHP server started successfully")
            return True
        else:
            stdout, stderr = php_process.communicate()
            logger.error(f"PHP server failed to start: {stderr.decode()}")
            return False
    except Exception as e:
        logger.error(f"Error starting PHP server: {e}")
        return False

# Helper function to proxy PHP file
def proxy_php_file(file_path='index.php'):
    try:
        # Simply serve the file
        return send_from_directory('.', file_path)
    except Exception as e:
        logger.error(f"Error serving {file_path}: {e}")
        return f"Error: {str(e)}", 500

# Routes
@app.route('/')
def index():
    return redirect('/info')

@app.route('/info')
def info():
    html = """<!DOCTYPE html>
<html data-bs-theme="dark">
<head>
    <title>WhatsApp Mesaj Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding-top: 20px; }
        .feature-icon { font-size: 2rem; margin-bottom: 1rem; }
        .file-list { font-family: monospace; }
        .highlight-text { color: #25D366; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Demo rejimində:</strong> Bu sayt demo məqsədi ilə hazırlanmışdır. Öz serverinizdə işlətmək üçün PHP və MySQL lazımdır.
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h1><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Mesaj Sistemi</h1>
                <p class="lead">WhatsApp ilə birbaşa inteqrasiya olunan əlaqə və mesaj idarəetmə sistemi.</p>
                
                <div class="mb-4">
                    <h4>Sistem tələbləri:</h4>
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fab fa-php me-3 text-primary"></i>
                            PHP 7.4 və ya daha yüksək
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fas fa-database me-3 text-info"></i>
                            MySQL verilənlər bazası
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="fab fa-whatsapp me-3 text-success"></i>
                            WhatsApp Web əlyetənliyi
                        </li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Təmir edildi:</strong> QR kod xətası həll edildi və sistem daha etibarlı oldu.
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-0 shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Sistemin xüsusiyyətləri</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="text-center text-success feature-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="text-center">Əlaqə İdarəetməsi</h5>
                                <p class="text-center small">Əlaqələr yaradın, redaktə edin, silin və qruplaşdırın.</p>
                            </div>
                            <div class="col-6">
                                <div class="text-center text-info feature-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h5 class="text-center">Mesaj Şablonları</h5>
                                <p class="text-center small">Tez-tez istifadə olunan mesajları saxlayın və tətbiq edin.</p>
                            </div>
                            <div class="col-6">
                                <div class="text-center text-warning feature-icon">
                                    <i class="fas fa-qrcode"></i>
                                </div>
                                <h5 class="text-center">QR Kod İnteqrasiyası</h5>
                                <p class="text-center small">WhatsApp hesabınızı birbaşa qoşun.</p>
                            </div>
                            <div class="col-6">
                                <div class="text-center text-danger feature-icon">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h5 class="text-center">Sürətli Göndərmə</h5>
                                <p class="text-center small">Bir kliklə WhatsApp mesajları göndərin.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-code me-2"></i>Yenilikləri sınayın</h5>
            </div>
            <div class="card-body">
                <p>Sistemin müxtəlif hissələrini test edin:</p>
                <div class="list-group mb-3">
                    <a href="/whatsapp_connect.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fab fa-whatsapp text-success me-2"></i>
                            WhatsApp Bağlantısı
                            <small class="d-block text-muted">QR kod ilə WhatsApp hesabınızı qoşun</small>
                        </div>
                        <span class="badge bg-success rounded-pill">YENİ</span>
                    </a>
                    <a href="/register.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-plus me-2"></i>
                        Qeydiyyat
                        <small class="d-block text-muted">Yeni hesab yaradın</small>
                    </a>
                    <a href="/login.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Giriş
                        <small class="d-block text-muted">Hesabınıza daxil olun</small>
                    </a>
                </div>
            </div>
        </div>
        
        <footer class="border-top pt-4 text-center text-muted">
            <p>WhatsApp Mesaj Sistemi &copy; 2025</p>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>"""
    return render_template_string(html)

# QR Code generator route
@app.route('/generate_qr')
def generate_qr():
    # Get parameters from request
    text = request.args.get('text', 'Default QR Code')
    size = int(request.args.get('size', 200))
    
    # Create QR code directly
    img = qrcode.make(text)
    
    # Save to memory buffer
    img_bytes = io.BytesIO()
    img.save(img_bytes, format='PNG')
    img_bytes.seek(0)
    
    # Return image
    response = make_response(img_bytes.getvalue())
    response.headers.set('Content-Type', 'image/png')
    return response

# Generic route to handle all PHP files
@app.route('/<path:path>')
def serve_file(path):
    if os.path.exists(path):
        return send_from_directory('.', path)
    else:
        return redirect('/info')

if __name__ == '__main__':
    # Start PHP server before Flask
    if start_php_server():
        logger.info("PHP server running, starting Flask app")
    else:
        logger.warning("PHP server not started, some features may not work")

    # Start Flask app
    app.run(host='0.0.0.0', port=5000, debug=True)
