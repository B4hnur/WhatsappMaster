from flask import Flask, send_from_directory, render_template_string, request, redirect
import os

app = Flask(__name__)

# Simple handler to serve PHP files directly as static files
@app.route('/')
def index():
    return send_from_directory('.', 'index.php')

@app.route('/<path:path>')
def serve_file(path):
    if os.path.exists(path):
        return send_from_directory('.', path)
    else:
        return f"File not found: {path}", 404

# We'll create a simple information page
@app.route('/info')
def info():
    html = """<!DOCTYPE html>
<html>
<head>
    <title>PHP WhatsApp Messaging System</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .alert { padding: 15px; border-radius: 4px; background-color: #d4edda; color: #155724; margin-bottom: 20px; }
        h1 { color: #25D366; }
        p { margin-bottom: 10px; }
        code { background-color: #f1f1f1; padding: 2px 4px; border-radius: 3px; }
        ul { margin-bottom: 20px; }
        .features { display: flex; flex-wrap: wrap; }
        .feature { flex: 1 0 48%; padding: 10px; }
        .highlight { font-weight: bold; color: #25D366; }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert">
            Bu sayt hosting konfiqurasiyası üçün hazırlanmışdır. PHP və MySQL bazası olan serverdə işləməsi üçün nəzərdə tutulub.
        </div>
        
        <h1>WhatsApp Mesaj Sistemi</h1>
        
        <p>Bu sistem .php fayllardan ibarətdir və WhatsApp mesajlarını avtomatlaşdırmaq üçün yaradılmışdır. Öz serverinizə yerləşdirmək üçün:</p>
        
        <ul>
            <li>PHP 7.4 və ya daha yüksək versiya lazımdır</li>
            <li>MySQL verilənlər bazası lazımdır</li>
            <li>Bütün faylları server qovluğuna köçürün</li>
            <li><code>db_config.php</code> faylında verilənlər bazası ayarlarını dəyişdirin</li>
            <li><code>sql/database.sql</code> faylını verilənlər bazasında icra edin</li>
        </ul>
        
        <h2>Sistemin Funksionallikları</h2>
        
        <div class="features">
            <div class="feature">
                <h3>✓ İstifadəçi idarəetməsi</h3>
                <p>Qeydiyyat, giriş, təhlükəsiz sessiyalar</p>
            </div>
            
            <div class="feature">
                <h3>✓ Əlaqələr idarəetməsi</h3>
                <p>Əlaqələri əlavə edin, redaktə edin, silin</p>
            </div>
            
            <div class="feature">
                <h3>✓ Mesaj şablonları</h3>
                <p>Tez-tez istifadə edilən mesajları şablonlar kimi saxlayın</p>
            </div>
            
            <div class="feature">
                <h3>✓ WhatsApp inteqrasiyası</h3>
                <p>Mesajları WhatsApp nömrələrinə göndərin</p>
            </div>
        </div>
        
        <p class="highlight">Bu sistem API istifadə etmir - WhatsApp Web URL sxemindən istifadə edir ki, mesajlar göndərilsin.</p>
    </div>
</body>
</html>"""
    return render_template_string(html)

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
