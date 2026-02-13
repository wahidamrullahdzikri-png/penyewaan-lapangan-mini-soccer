<?php
// booking/history.php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('booking');
require_once '../config/database.php';

// Query History: Hanya status LUNAS
$query = "
    SELECT b.id, b.nomor_booking, b.user_id, b.tanggal_booking, b.tanggal_main, b.jam_mulai, b.jam_selesai, b.total_harga, b.status_pembayaran, b.catatan, b.created_at,
           GROUP_CONCAT(l.nama_lapangan SEPARATOR ', ') as lapangan_terpesan
    FROM booking b
    LEFT JOIN booking_detail bd ON b.id = bd.booking_id
    LEFT JOIN lapangan l ON bd.lapangan_id = l.id
    WHERE b.status_pembayaran = 'LUNAS'
    GROUP BY b.id
    ORDER BY b.tanggal_main DESC, b.jam_mulai DESC
";

$result = mysqli_query($connection, $query);

// Hitung statistik Khusus History (Pendapatan Riil)
$stats_query = "
    SELECT 
        COUNT(*) as total_lunas,
        SUM(total_harga) as total_pendapatan
    FROM booking
    WHERE status_pembayaran = 'LUNAS'
";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<?php include '../views/' . THEME_NAME . '/header.php'; ?>
<?php include '../views/' . THEME_NAME . '/topnav.php'; ?>

<style>
    :root {
        --primary-green: #10b981;
        --dark-green: #059669;
    }
    .stats-card {
        border-radius: 12px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
    }
    .card-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .card-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
    
    .page-header {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../views/' . THEME_NAME . '/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="m-0"><i class="bi bi-clock-history"></i> Riwayat Booking</h2>
                        <p class="m-0 mt-2 op-8">Data booking yang telah selesai dan lunas</p>
                    </div>
                    </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="stats-card card-green">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p>Booking Selesai</p>
                                <h3><?= $stats['total_lunas'] ?? 0 ?> Transaksi</h3>
                            </div>
                            <div class="fs-1"><i class="bi bi-check-circle"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="stats-card card-purple">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p>Total Pendapatan Masuk</p>
                                <h3>Rp <?= number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.') ?></h3>
                            </div>
                            <div class="fs-1"><i class="bi bi-wallet2"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="py-3 px-3">No. Booking</th>
                                    <th>Jadwal Main</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Lapangan</th>
                                    <th class="text-end px-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0) : ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                        <tr>
                                            <td class="px-3">
                                                <span class="fw-bold text-dark">#<?= htmlspecialchars($row['nomor_booking']) ?></span>
                                            </td>
                                            <td>
                                                <small class="d-block text-muted">Tanggal Main</small>
                                                <span><?= date('d/m/Y', strtotime($row['tanggal_main'])) ?></span>
                                                <span class="badge bg-light text-dark border ms-1">
                                                    <?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?>
                                                </span>
                                            </td>
                                            <td><span class="fw-bold">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span></td>
                                            <td><span class="badge bg-success"><i class="bi bi-check-lg"></i> LUNAS</span></td>
                                            <td><?= htmlspecialchars($row['lapangan_terpesan']) ?: '-' ?></td>
                                            <td class="text-end px-3">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-primary" title="Lihat Detail & Cetak Struk">
                                                        <i class="bi bi-receipt"></i> Detail
                                                    </a>
                                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('PERINGATAN: Menghapus data riwayat akan mempengaruhi laporan pendapatan. Lanjutkan?')" title="Hapus Permanen">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="bi bi-inbox fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">Belum ada riwayat booking yang lunas.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../views/' . THEME_NAME . '/footer.php'; ?>