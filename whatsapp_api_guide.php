<?php
// Lazımi faylları daxil edin
require_once 'db_config.php';

// Session-u başlat və istifadəçi girişini yoxla
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Başlıq sənədini əlavə et
include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Ana Səhifə</a></li>
                    <li class="breadcrumb-item"><a href="whatsapp_api_status.php">WhatsApp API</a></li>
                    <li class="breadcrumb-item active">API Təlimatı</li>
                </ol>
            </nav>
            <h1><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Cloud API Təlimatı</h1>
            <p class="lead">Meta tərəfindən təqdim olunan pulsuz WhatsApp Business API-ni necə qoşmalı</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Addım-addım WhatsApp API quraşdırma</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Qeyd:</strong> Bu təlimat Meta/Facebook platformasında pulsuz WhatsApp Cloud API əldə etməyi izah edir.
                    </div>
                    
                    <div class="step-guide">
                        <h4 class="mt-3 mb-3">Addım 1: Meta Developer Hesabı yarat</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <ol>
                                    <li>
                                        <a href="https://developers.facebook.com/" target="_blank">Meta for Developers</a> saytına daxil olun
                                    </li>
                                    <li>Əgər Facebook hesabınız varsa, onunla daxil olun. Yoxdursa, qeydiyyatdan keçin.</li>
                                    <li>Qeydiyyat prosesini tamamlayın və təsdiqləyin.</li>
                                </ol>
                                <div class="text-center">
                                    <a href="https://developers.facebook.com/" target="_blank" class="btn btn-primary">
                                        <i class="fas fa-external-link-alt me-2"></i>Meta Developers-ə keçin
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mt-4 mb-3">Addım 2: Tətbiq yaradın</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <ol>
                                    <li>Meta for Developers Dashboard-da "Create App" (Tətbiq Yarat) düyməsinə basın</li>
                                    <li>Tətbiq növü seçimində "Business" seçin</li>
                                    <li>Tətbiqiniz üçün bir ad daxil edin və "Create App" düyməsini basın</li>
                                    <li>Yaratdıqdan sonra, sol paneldə "Add products to your app" bölməsindən "WhatsApp" tapın və "Set Up" düyməsini basın</li>
                                </ol>
                                <div class="text-center mt-3">
                                    <img src="https://i.imgur.com/D2A6TZq.png" alt="Meta tətbiq yaratma" class="img-fluid border rounded" style="max-height: 300px;">
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mt-4 mb-3">Addım 3: Test Nömrəsi əlavə edin</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <ol>
                                    <li>WhatsApp idarəetmə panelində "Getting Started" bölməsinə daxil olun</li>
                                    <li>"Add phone number" düyməsini basın və test üçün istifadə edəcəyiniz nömrəni əlavə edin</li>
                                    <li>Bu nömrəyə gələn kodu daxil etməklə nömrənizi təsdiqləyin</li>
                                </ol>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Diqqət:</strong> Əlavə etdiyiniz nömrə, test etmək istədiyiniz nömrədir. Bu nömrəyə WhatsApp mesajları göndərilməlidir.
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mt-4 mb-3">Addım 4: API Açarı əldə edin</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <ol>
                                    <li>WhatsApp idarəetmə panelində "API Setup" (və ya "Configuration") bölməsinə daxil olun</li>
                                    <li>"Temporary access token" bölməsindən token yaradın (24 saat etibarlıdır)</li>
                                    <li>Phone Number ID-nizi qeyd edin (bu, tətbiqinizə inteqrasiya etmək üçün lazımdır)</li>
                                </ol>
                                <div class="alert alert-primary">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Məsləhət:</strong> Əldə etdiyiniz TOKEN və PHONE NUMBER ID sistemin Environemt dəyişənlərinə əlavə olunmalıdır. Bu sistem avtomatik edəcək.
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mt-4 mb-3">Addım 5: Sisteminizə inteqrasiya edin</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <ol>
                                    <li>Açarları almış olduğunuz API açarını (<code>WHATSAPP_API_TOKEN</code>) və telefon nömrə ID-sini (<code>WHATSAPP_PHONE_NUMBER_ID</code>) sistemə əlavə edin</li>
                                    <li>WhatsApp API Status səhifəsindən sistemin qoşulduğunu yoxlayın</li>
                                    <li>Bir test mesajı göndərərək hər şeyin işlədiyindən əmin olun</li>
                                </ol>
                                <div class="text-center">
                                    <a href="whatsapp_api_status.php" class="btn btn-success">
                                        <i class="fas fa-cogs me-2"></i>API Statusunu yoxlayın
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Qaydalar və Tövsiyələr</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-check-circle text-success me-2"></i>Tövsiyələr</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-check-square text-success me-2"></i>Test telefon nömrələrini əvvəlcədən təsdiqləyin</li>
                                        <li class="mb-2"><i class="fas fa-check-square text-success me-2"></i>Mesaj şablonları yaradın</li>
                                        <li class="mb-2"><i class="fas fa-check-square text-success me-2"></i>Etibarlı bir token əldə edin (hər 24 saatda bir yeniləyin)</li>
                                        <li class="mb-2"><i class="fas fa-check-square text-success me-2"></i>Göndərilən mesajların say limitlərinə diqqət edin</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-times-circle text-danger me-2"></i>Məhdudiyyətlər</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-times-square text-danger me-2"></i>Spam mesajlar göndərməyin</li>
                                        <li class="mb-2"><i class="fas fa-times-square text-danger me-2"></i>META tərəfindən icazə verilməyən kontenti göndərməyin</li>
                                        <li class="mb-2"><i class="fas fa-times-square text-danger me-2"></i>Təsdiq olunmamış şablonlardan kütləvi istifadə etməyin</li>
                                        <li class="mb-2"><i class="fas fa-times-square text-danger me-2"></i>Təsdiqlənməmiş nömrələrə sistemli şəkildə mesaj göndərməyin</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Faydalı Keçidlər</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="https://developers.facebook.com/docs/whatsapp" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-book me-2 text-primary"></i>WhatsApp API Sənədləri
                            </div>
                            <i class="fas fa-external-link-alt text-muted"></i>
                        </a>
                        <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-paper-plane me-2 text-success"></i>Mesaj API Referansı
                            </div>
                            <i class="fas fa-external-link-alt text-muted"></i>
                        </a>
                        <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-rocket me-2 text-danger"></i>Başlanğıc Təlimatı
                            </div>
                            <i class="fas fa-external-link-alt text-muted"></i>
                        </a>
                    </div>
                    
                    <div class="card mt-4 border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-question-circle text-info me-2"></i>Kömək lazımdır?</h5>
                            <p class="card-text small">Meta Developer platforması ilə bağlı suallarınız üçün Meta Developer Support ilə əlaqə saxlayın.</p>
                            <a href="https://developers.facebook.com/support/" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-headset me-1"></i>Dəstək
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid">
                        <a href="whatsapp_api_status.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i>API Statusuna qayıt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>