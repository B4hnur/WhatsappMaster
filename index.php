<?php
require_once 'db_config.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Redirect to dashboard if logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>

<div class="container py-5">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold text-primary mb-4">WhatsApp Mesaj Sistemi</h1>
            <p class="lead">Müştərilərinizlə tez və effektiv ünsiyyət üçün WhatApp mesaj platforması.</p>
            <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                <a href="login.php" class="btn btn-primary btn-lg px-4 me-md-2">Giriş</a>
                <a href="register.php" class="btn btn-outline-secondary btn-lg px-4">Qeydiyyat</a>
            </div>
            <div class="mt-5">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary p-2 rounded-circle me-3">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Əlaqə İdarə Etmə</h5>
                        <p class="mb-0 text-muted">Bütün əlaqələrinizi bir yerdə saxlayın və idarə edin.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success p-2 rounded-circle me-3">
                        <i class="fas fa-comment-dots text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Mesaj Şablonları</h5>
                        <p class="mb-0 text-muted">Tez-tez istifadə olunan mesajları saxlayın.</p>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-info p-2 rounded-circle me-3">
                        <i class="fas fa-paper-plane text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Bir Kliklə Göndərin</h5>
                        <p class="mb-0 text-muted">Fərqli nömrələrə eyni və ya fərqli mesajlar göndərin.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mt-5 mt-lg-0 text-center">
            <div class="p-4 bg-white rounded shadow">
                <i class="fab fa-whatsapp display-1 text-success mb-4"></i>
                <div class="message-bubble message-sent mb-3">
                    Salam! WhatsApp Mesaj Sisteminə xoş gəlmisiniz!
                </div>
                <div class="message-bubble message-sent mb-3">
                    Müştərilərinizlə ünsiyyəti asanlaşdırın.
                </div>
                <div class="message-bubble message-sent">
                    İndi başlayın!
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <h2 class="text-center mb-5">Funksionallıqlar</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-address-book text-primary fa-3x mb-3"></i>
                    <h4 class="card-title">Əlaqə İdarə Etmə</h4>
                    <p class="card-text">Əlaqələrinizi asanlıqla əlavə edin, redaktə edin və silin. Kateqoriyalara görə təşkil edin.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-envelope-open-text text-success fa-3x mb-3"></i>
                    <h4 class="card-title">Mesaj Şablonları</h4>
                    <p class="card-text">Ən çox istifadə edilən mesajları şablonlar kimi saxlayın və tez istifadə edin.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar text-info fa-3x mb-3"></i>
                    <h4 class="card-title">Mesaj Tarixçəsi</h4>
                    <p class="card-text">Göndərdiyiniz bütün mesajların tarixçəsini izləyin və statistikanı təhlil edin.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
