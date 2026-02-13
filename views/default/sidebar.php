<?php if (isAdminLoggedIn()): ?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse" style="background: linear-gradient(180deg, #1a1a1a 0%, #2d2d2d 100%); min-height: 100vh; box-shadow: 2px 0 10px rgba(0,0,0,0.1);">
    <div class="position-sticky pt-4">
        <!-- Brand / Logo Area -->
        <div class="text-center mb-4 pb-3 border-bottom border-secondary">
            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                <i class="bi bi-trophy text-success" style="font-size: 1.75rem;"></i>
            </div>
            <h6 class="text-white mb-0 fw-bold">Admin Panel</h6>
            <small class="text-muted">Mini Soccer</small>
        </div>

        <!-- Menu Items -->
        <ul class="nav flex-column px-2">
            <!-- Dashboard -->
            <li class="nav-item mb-1">
                <a class="nav-link sidebar-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'active' : '' ?>" 
                   href="<?= base_url('admin/index.php') ?>">
                    <i class="bi bi-speedometer2 me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <li class="nav-item my-2">
                <hr class="sidebar-divider">
                <small class="text-muted text-uppercase px-3" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;">Manajemen Data</small>
            </li>

            <?php 
            // ... (Kode icon mapping tetap sama) ...
            $moduleIcons = [
                'tempat' => 'bi-geo-alt',
                'lapangan' => 'bi-bounding-box-circles',
                'booking' => 'bi-calendar-check',
                'user' => 'bi-people',
                'report' => 'bi-bar-chart',
            ];

            foreach ($menuConfig['modules'] as $module => $config) : 
                if (userCanAccess($config['allowed_roles'])) : 
                    $icon = $moduleIcons[$module] ?? 'bi-circle';
                    
                    // --- LOGIKA BARU ---
                    
                    // 1. Deteksi apakah kita sedang membuka detail booking yang LUNAS
                    // Variabel $booking didapat dari file detail.php yang meng-include sidebar ini
                    $isLunasDetail = false;
                    if ($module == 'booking' && isset($booking) && 
                        isset($booking['status_pembayaran']) && 
                        $booking['status_pembayaran'] === 'LUNAS') {
                        $isLunasDetail = true;
                    }

                    // 2. Logic Active untuk Menu Utama (Booking Aktif)
                    // Aktif jika: Ada di folder modul, BUKAN halaman history, dan BUKAN detail lunas
                    $isActive = strpos($_SERVER['REQUEST_URI'], '/' . $module . '/') !== false 
                                && strpos($_SERVER['REQUEST_URI'], 'history.php') === false
                                && !$isLunasDetail;
            ?>
                <li class="nav-item mb-1">
                    <a class="nav-link sidebar-link <?= $isActive ? 'active' : '' ?>" 
                       href="../<?= $module ?>/index.php">
                        <i class="bi <?= $icon ?> me-2"></i>
                        <span><?= $config['label'] ?></span>
                    </a>
                </li>

                <?php if ($module == 'booking'): 
                    // 3. Logic Active untuk Menu Riwayat
                    // Aktif jika: Halaman history.php ATAU sedang buka detail yang LUNAS
                    $isHistoryActive = strpos($_SERVER['REQUEST_URI'], '/booking/history.php') !== false || $isLunasDetail;
                ?>
                    <li class="nav-item mb-1">
                        <a class="nav-link sidebar-link <?= $isHistoryActive ? 'active' : '' ?>" 
                           href="../booking/history.php">
                            <i class="bi bi-clock-history me-2"></i>
                            <span>Riwayat Booking</span>
                        </a>
                    </li>
                <?php endif; ?>

            <?php 
                endif;
            endforeach; 
            ?>

            <!-- Divider -->
            <li class="nav-item my-2">
                <hr class="sidebar-divider">
                <small class="text-muted text-uppercase px-3" style="font-size: 0.7rem; font-weight: 600; letter-spacing: 0.5px;">Lainnya</small>
            </li>

            <!-- Settings -->
            <li class="nav-item mb-1">
                <a class="nav-link sidebar-link" href="#">
                    <i class="bi bi-gear me-2"></i>
                    <span>Pengaturan</span>
                </a>
            </li>

            <!-- Logout -->
            <li class="nav-item mb-1">
                <a class="nav-link sidebar-link text-danger" href="<?= base_url('logout.php') ?>">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>

        <!-- User Info at Bottom -->
        <div class="mt-4 pt-3 border-top border-secondary px-3">
            <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-circle text-success" style="font-size: 1.5rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="text-white mb-0" style="font-size: 0.85rem;"><?= $_SESSION['username'] ?></h6>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-check"></i> <?= $_SESSION['role'] ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 56px; /* Height of topnav */
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 0;
    overflow-x: hidden;
    overflow-y: auto;
}

.sidebar-link {
    color: #b8b8b8;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    font-weight: 500;
    font-size: 0.9rem;
}

.sidebar-link:hover {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
    transform: translateX(5px);
}

.sidebar-link.active {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.sidebar-link.active i {
    color: white;
}

.sidebar-link i {
    font-size: 1.1rem;
    width: 24px;
    transition: all 0.3s ease;
}

.sidebar-link.text-danger:hover {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545 !important;
}

.sidebar-divider {
    border-color: rgba(255, 255, 255, 0.1);
    margin: 0.5rem 0;
}

/* Scrollbar Styling */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

/* Responsive */
@media (max-width: 767.98px) {
    .sidebar {
        top: 0;
    }
}
</style>
<?php endif; ?>