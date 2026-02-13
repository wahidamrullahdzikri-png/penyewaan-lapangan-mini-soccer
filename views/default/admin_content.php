<?php
// Koneksi database sudah di-load dari admin/index.php via auth.php
// Tidak perlu require lagi, langsung pakai $connection

// ==================== QUERY DATA REAL ====================

// 1. Total Lapangan
$query_lapangan = "SELECT COUNT(*) as total FROM lapangan WHERE status_lapangan = 'Tersedia'";
$result_lapangan = mysqli_query($connection, $query_lapangan);
$total_lapangan = mysqli_fetch_assoc($result_lapangan)['total'];

// Lapangan baru bulan ini (karena tabel lapangan tidak punya created_at, set ke 0)
$lapangan_baru = 0; // Bisa diubah manual atau tambah kolom created_at di database

// 2. Booking Hari Ini
$query_booking_hari_ini = "SELECT COUNT(*) as total FROM booking 
                           WHERE DATE(tanggal_main) = CURDATE()";
$result_booking_hari_ini = mysqli_query($connection, $query_booking_hari_ini);
$booking_hari_ini = mysqli_fetch_assoc($result_booking_hari_ini)['total'];

// Booking Pending
$query_booking_pending = "SELECT COUNT(*) as total FROM booking 
                          WHERE status_pembayaran = 'MENUNGGU'";
$result_booking_pending = mysqli_query($connection, $query_booking_pending);
$booking_pending = mysqli_fetch_assoc($result_booking_pending)['total'];

// 3. Pendapatan Bulan Ini
$query_pendapatan = "SELECT SUM(total_harga) as total FROM booking 
                     WHERE MONTH(tanggal_booking) = MONTH(CURRENT_DATE()) 
                     AND YEAR(tanggal_booking) = YEAR(CURRENT_DATE())
                     AND status_pembayaran = 'LUNAS'";
$result_pendapatan = mysqli_query($connection, $query_pendapatan);
$row_pendapatan = mysqli_fetch_assoc($result_pendapatan);
$pendapatan_bulan_ini = $row_pendapatan['total'] ?? 0;

// Total booking bulan ini
$query_total_booking_bulan = "SELECT COUNT(*) as total FROM booking 
                               WHERE MONTH(tanggal_booking) = MONTH(CURRENT_DATE()) 
                               AND YEAR(tanggal_booking) = YEAR(CURRENT_DATE())";
$result_total_booking_bulan = mysqli_query($connection, $query_total_booking_bulan);
$total_booking_bulan = mysqli_fetch_assoc($result_total_booking_bulan)['total'];

// Pendapatan bulan lalu untuk perbandingan
$query_pendapatan_lalu = "SELECT SUM(total_harga) as total FROM booking 
                          WHERE MONTH(tanggal_booking) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
                          AND YEAR(tanggal_booking) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
                          AND status_pembayaran = 'LUNAS'";
$result_pendapatan_lalu = mysqli_query($connection, $query_pendapatan_lalu);
$row_pendapatan_lalu = mysqli_fetch_assoc($result_pendapatan_lalu);
$pendapatan_bulan_lalu = $row_pendapatan_lalu['total'] ?? 0;

// Hitung persentase kenaikan (cek jika bulan lalu ada data)
$persentase_kenaikan = 0;
if ($pendapatan_bulan_lalu > 0) {
    $persentase_kenaikan = (($pendapatan_bulan_ini - $pendapatan_bulan_lalu) / $pendapatan_bulan_lalu) * 100;
}

// 4. Total Semua Booking
$query_total_booking = "SELECT COUNT(*) as total FROM booking";
$result_total_booking = mysqli_query($connection, $query_total_booking);
$total_booking = mysqli_fetch_assoc($result_total_booking)['total'];

// 5. Aktivitas Terbaru (3 booking terakhir)
$query_aktivitas = "SELECT b.*, l.nama_lapangan, b.created_at
                    FROM booking b
                    LEFT JOIN booking_detail bd ON b.id = bd.booking_id
                    LEFT JOIN lapangan l ON bd.lapangan_id = l.id
                    ORDER BY b.created_at DESC
                    LIMIT 3";
$result_aktivitas = mysqli_query($connection, $query_aktivitas);

// Format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Format waktu relatif
function waktuRelatif($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->d > 0) {
        return $diff->d . ' hari lalu';
    } elseif ($diff->h > 0) {
        return $diff->h . ' jam lalu';
    } elseif ($diff->i > 0) {
        return $diff->i . ' menit lalu';
    } else {
        return 'Baru saja';
    }
}
?>

<div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1" style="color: #212529; font-weight: 700;">
                <i class="bi bi-speedometer2" style="color: #28a745;"></i> Dashboard Admin
            </h2>
            <p class="text-muted mb-0">
                Selamat datang, <strong><?= $_SESSION['username'] ?></strong> 
                <span class="badge bg-success"><?= $_SESSION['role'] ?></span>
            </p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                <i class="bi bi-calendar3"></i> <?= date('d F Y') ?>
            </small>
        </div>
    </div>
</div>

<!-- Statistik Cards - HANYA 3 CARD -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Lapangan -->
    <div class="col-xl-4 col-md-6 stat-card">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 text-white-50" style="font-size: 0.875rem; font-weight: 600;">Total Lapangan</h6>
                        <h2 class="mb-0 fw-bold"><?= $total_lapangan ?></h2>
                        <small class="text-white-50">Lapangan tersedia</small>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-bounding-box-circles" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white bg-opacity-10 border-0 text-white">
                <small>
                    <i class="bi bi-arrow-up-circle"></i> 
                    <?= $lapangan_baru ?> lapangan baru bulan ini
                </small>
            </div>
        </div>
    </div>

    <!-- Card 2: Booking Hari Ini -->
    <div class="col-xl-4 col-md-6 stat-card">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 text-white-50" style="font-size: 0.875rem; font-weight: 600;">Booking Hari Ini</h6>
                        <h2 class="mb-0 fw-bold"><?= $booking_hari_ini ?></h2>
                        <small class="text-white-50">Booking aktif</small>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calendar-check" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white bg-opacity-10 border-0 text-white">
                <small>
                    <i class="bi bi-clock-history"></i> 
                    <?= $booking_pending ?> booking pending
                </small>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Pendapatan -->
    <div class="col-xl-4 col-md-6 stat-card">
        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 text-white-50" style="font-size: 0.875rem; font-weight: 600;">Pendapatan Bulan Ini</h6>
                        <h2 class="mb-0 fw-bold" style="font-size: 1.5rem;">
                            <?= formatRupiah($pendapatan_bulan_ini) ?>
                        </h2>
                        <small class="text-white-50">Dari <?= $total_booking_bulan ?> booking</small>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-currency-dollar" style="font-size: 1.75rem;"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white bg-opacity-10 border-0 text-white">
                <small>
                    <?php if ($pendapatan_bulan_lalu == 0): ?>
                        <i class="bi bi-info-circle"></i> Belum ada data bulan lalu
                    <?php elseif ($persentase_kenaikan >= 0): ?>
                        <i class="bi bi-graph-up-arrow"></i> 
                        +<?= number_format($persentase_kenaikan, 1) ?>% dari bulan lalu
                    <?php else: ?>
                        <i class="bi bi-graph-down-arrow"></i> 
                        <?= number_format($persentase_kenaikan, 1) ?>% dari bulan lalu
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions & Info -->
<div class="row g-3">
    <!-- Quick Actions -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0" style="color: #212529; font-weight: 600;">
                    <i class="bi bi-lightning-charge text-warning"></i> Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <a href="../lapangan/add.php" class="btn btn-outline-success w-100 py-3" style="border: 2px dashed #28a745;">
                            <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                            <strong>Tambah Lapangan</strong>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../booking/index.php" class="btn btn-outline-primary w-100 py-3" style="border: 2px dashed #007bff;">
                            <i class="bi bi-list-check fs-4 d-block mb-2"></i>
                            <strong>Kelola Booking</strong>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../lapangan/index.php" class="btn btn-outline-warning w-100 py-3" style="border: 2px dashed #ffc107;">
                            <i class="bi bi-eye fs-4 d-block mb-2"></i>
                            <strong>Lihat Lapangan</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0" style="color: #212529; font-weight: 600;">
                    <i class="bi bi-activity text-info"></i> Aktivitas Terbaru
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (mysqli_num_rows($result_aktivitas) > 0): ?>
                        <?php while ($aktivitas = mysqli_fetch_assoc($result_aktivitas)): ?>
                            <div class="list-group-item d-flex align-items-center">
                                <div class="bg-<?= $aktivitas['status_pembayaran'] == 'LUNAS' ? 'success' : 'warning' ?> bg-opacity-10 rounded-circle p-2 me-3">
                                    <i class="bi bi-<?= $aktivitas['status_pembayaran'] == 'LUNAS' ? 'check-circle' : 'clock-history' ?> text-<?= $aktivitas['status_pembayaran'] == 'LUNAS' ? 'success' : 'warning' ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0" style="font-size: 0.9rem;">
                                        Booking <?= substr($aktivitas['nomor_booking'], 0, 15) ?>...
                                        <span class="badge bg-<?= $aktivitas['status_pembayaran'] == 'LUNAS' ? 'success' : 'warning' ?>"><?= $aktivitas['status_pembayaran'] ?></span>
                                    </h6>
                                    <small class="text-muted">
                                        <?= $aktivitas['nama_lapangan'] ?? 'Lapangan tidak ditemukan' ?> - 
                                        <?= formatRupiah($aktivitas['total_harga']) ?>
                                    </small>
                                </div>
                                <small class="text-muted"><?= waktuRelatif($aktivitas['created_at']) ?></small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="list-group-item text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada aktivitas
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Panel -->
    <div class="col-lg-4">
        <!-- System Info -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0" style="color: #212529; font-weight: 600;">
                    <i class="bi bi-info-circle text-primary"></i> Info Sistem
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                        <i class="bi bi-server text-success fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Status Server</small>
                        <strong class="text-success">Online</strong>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-info bg-opacity-10 rounded p-2 me-3">
                        <i class="bi bi-calendar-check text-info fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Total Booking</small>
                        <strong><?= $total_booking ?> booking</strong>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="bg-warning bg-opacity-10 rounded p-2 me-3">
                        <i class="bi bi-clipboard-check text-warning fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Booking Pending</small>
                        <strong><?= $booking_pending ?> booking</strong>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-2 me-3">
                        <i class="bi bi-clock-history text-primary fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block">Login terakhir</small>
                        <strong><?= date('H:i, d M Y') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips -->
        <div class="card border-0 shadow-sm mt-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <h6 class="text-white mb-3">
                    <i class="bi bi-lightbulb"></i> Tips Hari Ini
                </h6>
                <p class="mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                    Periksa booking secara berkala untuk memastikan tidak ada jadwal yang bertabrakan. 
                    Konfirmasi pembayaran dengan cepat untuk meningkatkan kepuasan pelanggan.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.list-group-item {
    border-left: none;
    border-right: none;
    transition: background-color 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>