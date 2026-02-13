<?php
// booking/index.php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('booking');
require_once '../config/database.php';

// Query Booking: Hanya status MENUNGGU (Belum Lunas)
$query = "
    SELECT b.id, b.nomor_booking, b.user_id, b.tanggal_booking, b.tanggal_main, b.jam_mulai, b.jam_selesai, b.total_harga, b.status_pembayaran, b.catatan, b.created_at,
           GROUP_CONCAT(l.nama_lapangan SEPARATOR ', ') as lapangan_terpesan
    FROM booking b
    LEFT JOIN booking_detail bd ON b.id = bd.booking_id
    LEFT JOIN lapangan l ON bd.lapangan_id = l.id
    WHERE b.status_pembayaran != 'LUNAS'
    GROUP BY b.id
    ORDER BY b.tanggal_main ASC, b.jam_mulai ASC
";

$result = mysqli_query($connection, $query);

// Hitung statistik Khusus Booking Aktif
$stats_query = "
    SELECT 
        COUNT(*) as total_pending,
        SUM(total_harga) as potensi_pendapatan
    FROM booking
    WHERE status_pembayaran != 'LUNAS'
";
$stats_result = mysqli_query($connection, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>

<?php include '../views/' . THEME_NAME . '/header.php'; ?>
<?php include '../views/' . THEME_NAME . '/topnav.php'; ?>

<style>
    /* Gunakan style yang sama, saya sederhanakan untuk fokus logika */
    :root {
        --primary-green: #10b981;
        --accent-orange: #f59e0b;
        --dark-bg: #1f2937;
    }
    .stats-card {
        border-radius: 12px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border: none;
    }
    .card-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .card-blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
    
    .page-header {
        background: linear-gradient(135deg, var(--accent-orange) 0%, #d97706 100%);
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
                        <h2 class="m-0"><i class="bi bi-calendar-range"></i> Booking Aktif</h2>
                        <p class="m-0 mt-2 op-8">Daftar booking yang menunggu pembayaran atau verifikasi</p>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="stats-card card-orange">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p>Menunggu Pembayaran</p>
                                <h3><?= $stats['total_pending'] ?? 0 ?> Transaksi</h3>
                            </div>
                            <div class="fs-1"><i class="bi bi-hourglass-split"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="stats-card card-blue">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p>Potensi Pendapatan</p>
                                <h3>Rp <?= number_format($stats['potensi_pendapatan'] ?? 0, 0, ',', '.') ?></h3>
                            </div>
                            <div class="fs-1"><i class="bi bi-cash"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-dark text-white">
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
                                                <strong>#<?= htmlspecialchars($row['nomor_booking']) ?></strong><br>
                                                <small class="text-muted">ID: <?= $row['id'] ?></small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold"><i class="bi bi-calendar3 text-primary"></i> <?= date('d/m/Y', strtotime($row['tanggal_main'])) ?></span>
                                                    <small class="text-muted"><i class="bi bi-clock"></i> <?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?></small>
                                                </div>
                                            </td>
                                            <td><span class="text-success fw-bold">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></span></td>
                                            <td><span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle"></i> MENUNGGU</span></td>
                                            <td><?= htmlspecialchars($row['lapangan_terpesan']) ?: '-' ?></td>
                                            <td class="text-end px-3">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="set_lunas.php?id=<?= $row['id'] ?>" class="btn btn-success" onclick="return confirm('Verifikasi pembayaran dan set LUNAS?')" title="Set Lunas">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                    <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-info text-white" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning text-white" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Hapus booking ini?')" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-50">
                                            <p class="text-muted mb-0">Tidak ada booking yang sedang menunggu.</p>
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