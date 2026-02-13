<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start(); // Start session untuk mengecek login admin

require_once 'config/database.php';
require_once 'lib/functions.php';

$page_title = "Home - Penyewaan Lapangan";
?>

<?php include 'views/' . THEME_NAME . '/header.php'; ?>
<?php include 'views/' . THEME_NAME . '/topnav.php'; ?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-lg-7">
                <div class="hero-content">
                    <span class="badge bg-success mb-3 px-3 py-2" style="font-size: 0.9rem;">
                        <i class="bi bi-trophy-fill me-2"></i>Booking Mudah & Cepat
                    </span>
                    <h1 class="display-4 fw-bold mb-3" style="color: #1a1a1a; line-height: 1.2;">
                        Sewa Lapangan Mini Soccer <span style="color: #28a745;">Terbaik</span>
                    </h1>
                    <p class="lead mb-4" style="color: #6c757d; font-size: 1.1rem;">
                        Temukan lapangan berkualitas premium untuk pertandingan atau latihan tim Anda. 
                        Fasilitas lengkap, harga terjangkau, dan proses booking yang sangat mudah!
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#lapangan-tersedia" class="btn btn-success btn-lg px-4 py-3 smooth-scroll">
                            <i class="bi bi-search me-2"></i>Lihat Lapangan
                        </a>
                        <a href="booking/add.php" class="btn btn-outline-dark btn-lg px-4 py-3">
                            <i class="bi bi-calendar-plus me-2"></i>Booking Sekarang
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="hero-image text-center">
                    <div class="stats-float">
                        <div class="stat-box">
                            <i class="bi bi-bounding-box-circles fs-3 text-success"></i>
                            <h4 class="mb-0 mt-2"><?php
                            $total_fields = count(getAvailableFields($connection));
                            echo $total_fields;
                            ?>+</h4>
                            <small class="text-muted">Lapangan Tersedia</small>
                        </div>
                    </div>
                    <i class="bi bi-trophy" style="font-size: 200px; color: #28a74530;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="features-section py-4 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="feature-item">
                    <div class="feature-icon bg-success bg-opacity-10 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-history text-success fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Booking 24/7</h6>
                    <small class="text-muted">Pesan kapan saja</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-item">
                    <div class="feature-icon bg-success bg-opacity-10 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-cash-stack text-success fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Harga Terbaik</h6>
                    <small class="text-muted">Harga kompetitif</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-item">
                    <div class="feature-icon bg-success bg-opacity-10 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-shield-check text-success fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Aman & Terpercaya</h6>
                    <small class="text-muted">Pembayaran terjamin</small>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="feature-item">
                    <div class="feature-icon bg-success bg-opacity-10 rounded-circle mx-auto mb-3" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-star-fill text-success fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Fasilitas Premium</h6>
                    <small class="text-muted">Lapangan berkualitas</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lapangan Section -->
<div class="container mt-5 mb-5" id="lapangan-tersedia">
    <div class="section-header text-center mb-5">
        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 mb-3">
            <i class="bi bi-bounding-box-circles me-2"></i>Pilihan Lapangan
        </span>
        <h2 class="fw-bold mb-3" style="color: #1a1a1a;">Lapangan Yang Tersedia</h2>
        <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
            Pilih lapangan sesuai kebutuhan Anda. Semua lapangan dilengkapi dengan fasilitas terbaik dan rumput sintetis berkualitas.
        </p>
    </div>

    <div class="row g-4">
        <?php
        $available_fields = getAvailableFields($connection);
        if (count($available_fields) > 0):
            foreach ($available_fields as $index => $field):
        ?>
            <div class="col-lg-4 col-md-6">
                <div class="field-card">
                    <!-- Image Container -->
                    <div class="field-image-wrapper">
                        <?php
                        $foto_path_abs = __DIR__ . '/uploads/' . $field['foto'];
                        $foto_path_rel = 'uploads/' . $field['foto'];
                        $show_default = !($field['foto'] && file_exists($foto_path_abs));
                        $image_src = $show_default ? base_url('uploads/default.jpg') : base_url($foto_path_rel);
                        ?>
                        <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($field['nama_lapangan']) ?>">
                        <div class="field-badge">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Tersedia
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="field-body">
                        <h5 class="field-title"><?= htmlspecialchars($field['nama_lapangan']) ?></h5>
                        
                        <!-- Price -->
                        <div class="field-price mb-3">
                            <span class="price-label">Mulai dari</span>
                            <div class="price-value">
                                Rp <?= number_format($field['harga_per_jam'], 0, ',', '.') ?>
                                <span class="price-unit">/ jam</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="field-description">
                            <?php 
                            $desc = htmlspecialchars($field['deskripsi']);
                            echo strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;
                            ?>
                        </p>

                        <!-- Features -->
                        <div class="field-features mb-3">
                            <div class="feature-tag">
                                <i class="bi bi-rulers text-success"></i>
                                <span>40 x 20 m</span>
                            </div>
                            <div class="feature-tag">
                                <i class="bi bi-stars text-success"></i>
                                <span>Premium</span>
                            </div>
                            <div class="feature-tag">
                                <i class="bi bi-brightness-high text-success"></i>
                                <span>Outdoor</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <a href="booking/add.php?lapangan=<?= $field['id'] ?>" class="btn btn-success w-100 btn-booking">
                            <i class="bi bi-calendar-check me-2"></i>Booking Sekarang
                        </a>
                    </div>
                </div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                    <h5>Belum Ada Lapangan Tersedia</h5>
                    <p class="mb-0">Silakan cek kembali nanti atau hubungi kami untuk informasi lebih lanjut.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CTA Section -->
<div class="cta-section py-5 mb-5">
    <div class="container">
        <div class="cta-box text-center">
            <i class="bi bi-headset fs-1 text-success mb-3"></i>
            <h3 class="fw-bold mb-3">Butuh Bantuan Booking?</h3>
            <p class="text-muted mb-4">Tim kami siap membantu Anda dalam proses pemesanan lapangan</p>
            <a href="https://wa.me/6283120474479" target="_blank" class="btn btn-success btn-lg px-5">
            <i class="bi bi-whatsapp me-2"></i>Hubungi Kami di WhatsApp
            </a>
        
            </a>
        </div>
    </div>
</div>

<?php include 'views/' . THEME_NAME . '/footer.php'; ?>

<style>
/* ============================================
   PALET WARNA KONSISTEN
   ============================================ */
:root {
    --primary-green: #28a745;
    --dark-green: #20c997;
    --light-green: #28a74515;
    --dark-bg: #1a1a1a;
    --gray-text: #6c757d;
    --light-bg: #f8f9fa;
}

/* ============================================
   HERO SECTION
   ============================================ */
.hero-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 50%;
    height: 100%;
    background: linear-gradient(135deg, transparent 0%, var(--light-green) 100%);
    z-index: 0;
}

.hero-content {
    position: relative;
    z-index: 1;
}

.hero-image {
    position: relative;
    z-index: 1;
}

.stats-float {
    position: absolute;
    top: 20%;
    right: 10%;
    z-index: 2;
}

.stat-box {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* ============================================
   BUTTONS
   ============================================ */
.btn-success {
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
}

.btn-outline-dark {
    border: 2px solid var(--dark-bg);
    color: var(--dark-bg);
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-dark:hover {
    background: var(--dark-bg);
    color: white;
    transform: translateY(-2px);
}

.smooth-scroll {
    scroll-behavior: smooth;
}

/* ============================================
   FEATURES SECTION
   ============================================ */
.features-section {
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
}

.feature-item {
    transition: transform 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-5px);
}

.feature-icon {
    transition: all 0.3s ease;
}

.feature-item:hover .feature-icon {
    transform: scale(1.1);
    background: var(--primary-green) !important;
}

.feature-item:hover .feature-icon i {
    color: white !important;
}

/* ============================================
   SECTION HEADER
   ============================================ */
.section-header h2 {
    font-size: 2.5rem;
}

/* ============================================
   FIELD CARD
   ============================================ */
.field-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.field-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(40, 167, 69, 0.2);
}

/* Image */
.field-image-wrapper {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
}

.field-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.field-card:hover .field-image-wrapper img {
    transform: scale(1.1);
}

.field-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
}

.field-badge .badge {
    padding: 0.5rem 1rem;
    font-weight: 600;
    font-size: 0.85rem;
}

/* Body */
.field-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.field-title {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--dark-bg);
    margin-bottom: 1rem;
    line-height: 1.3;
}

/* Price */
.field-price {
    background: var(--light-green);
    padding: 1rem;
    border-radius: 10px;
    border-left: 4px solid var(--primary-green);
}

.price-label {
    font-size: 0.75rem;
    color: var(--gray-text);
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 0.25rem;
}

.price-value {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--primary-green);
}

.price-unit {
    font-size: 1rem;
    font-weight: 500;
    color: var(--gray-text);
}

/* Description */
.field-description {
    color: var(--gray-text);
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 1rem;
    flex: 1;
}

/* Features Tags */
.field-features {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.feature-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--light-bg);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    color: var(--dark-bg);
    font-weight: 500;
}

.feature-tag i {
    font-size: 0.9rem;
}

/* Booking Button */
.btn-booking {
    font-weight: 600;
    padding: 0.875rem;
    font-size: 1rem;
    margin-top: auto;
}

.btn-booking:hover {
    transform: translateY(-2px);
}

/* ============================================
   CTA SECTION
   ============================================ */
.cta-section {
    background: linear-gradient(135deg, var(--light-bg) 0%, white 100%);
}

.cta-box {
    background: white;
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    border: 2px dashed var(--primary-green);
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 768px) {
    .hero-section {
        padding: 2rem 0;
    }
    
    .section-header h2 {
        font-size: 1.75rem;
    }
    
    .field-image-wrapper {
        height: 200px;
    }
    
    .field-title {
        font-size: 1.15rem;
    }
    
    .price-value {
        font-size: 1.5rem;
    }
    
    .cta-box {
        padding: 2rem 1rem;
    }
}

/* ============================================
   SMOOTH SCROLLING
   ============================================ */
html {
    scroll-behavior: smooth;
}
</style>

<!-- Bootstrap Icons CDN (pastikan sudah ada di header, jika belum tambahkan ini) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">