<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);

session_start();

require_once '../lib/auth.php';
require_once '../lib/functions.php';

requireAuth(); // Hanya admin yang bisa masuk

$page_title = "Dashboard Admin";
?>

<?php include '../views/' . THEME_NAME . '/header.php'; ?>
<?php include '../views/' . THEME_NAME . '/topnav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../views/' . THEME_NAME . '/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php include '../views/' . THEME_NAME . '/admin_content.php'; ?>
        </main>
    </div>
</div>

<?php include '../views/' . THEME_NAME . '/footer.php'; ?>

<style>
/* Custom Styles untuk Dashboard */
body {
    background-color: #f4f6f9;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

main {
    min-height: calc(100vh - 56px);
}

/* Animasi untuk card */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card {
    animation: fadeInUp 0.5s ease-out;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
</style>