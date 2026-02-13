<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';
requireAuth();
requireModuleAccess('booking');
require_once '../config/database.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('../booking/index.php');

$stmt = mysqli_prepare($connection, "SELECT * FROM `booking` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$booking) redirect('../booking/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_lunas'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }
    if ($booking['status_pembayaran'] !== 'LUNAS') {
        $updateStmt = mysqli_prepare($connection, "UPDATE `booking` SET `status_pembayaran` = 'LUNAS' WHERE `id` = ?");
        mysqli_stmt_bind_param($updateStmt, "i", $id);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
        $booking['status_pembayaran'] = 'LUNAS';
    }
    redirect($_SERVER['REQUEST_URI']);
}

// Query detail dengan JOIN untuk mendapatkan nama item
$details_query = "
    SELECT bd.*, 
           CASE 
               WHEN bd.item_type = 'lapangan' THEN l.nama_lapangan
               WHEN bd.item_type = 'produk' THEN p.nama_produk
               ELSE 'Item Tidak Dikenal'
           END AS item_name
    FROM `booking_detail` bd
    LEFT JOIN `lapangan` l ON (bd.item_type = 'lapangan' AND bd.item_id = l.id)
    LEFT JOIN `produk` p ON (bd.item_type = 'produk' AND bd.item_id = p.id)
    WHERE bd.`booking_id` = ?
    ORDER BY bd.id
";
$details_stmt = mysqli_prepare($connection, $details_query);
mysqli_stmt_bind_param($details_stmt, "i", $id);
mysqli_stmt_execute($details_stmt);
$details_result = mysqli_stmt_get_result($details_stmt);
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>

<style>
    /* Sport Theme Colors */
    :root {
        --primary-green: #10b981;
        --dark-green: #059669;
        --light-green: #d1fae5;
        --accent-orange: #f59e0b;
        --dark-bg: #1f2937;
        --light-bg: #f9fafb;
    }

    /* Page Header */
    .page-header-detail {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-header-detail h2 {
        margin: 0;
        font-weight: 700;
        font-size: 26px;
    }

    .page-header-detail .badge {
        font-size: 14px;
        padding: 8px 15px;
    }

    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 25px;
        margin-bottom: 25px;
    }

    .info-card h5 {
        color: var(--dark-bg);
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid var(--primary-green);
    }

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #6b7280;
        min-width: 180px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-value {
        color: var(--dark-bg);
        font-weight: 500;
        flex: 1;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #e5e7eb;
    }

    /* Table Section */
    .table-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 25px;
        margin-bottom: 25px;
    }

    .table-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--primary-green);
    }

    .table-section-header h5 {
        margin: 0;
        color: var(--dark-bg);
        font-weight: 700;
    }

    .table thead th {
        background: var(--dark-bg);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        border: none;
        padding: 15px 10px;
    }

    .table tbody tr {
        transition: background 0.2s ease;
    }

    .table tbody tr:hover {
        background: var(--light-green);
    }

    .table tbody td {
        vertical-align: middle;
        padding: 12px 10px;
    }

    /* Enhanced Buttons */
    .btn {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: var(--primary-green);
        border-color: var(--primary-green);
    }

    .btn-primary:hover {
        background: var(--dark-green);
        border-color: var(--dark-green);
    }

    .btn-success {
        background: var(--primary-green);
        border-color: var(--primary-green);
    }

    .btn-success:hover {
        background: var(--dark-green);
        border-color: var(--dark-green);
    }

    .btn-secondary {
        background: #6b7280;
        border-color: #6b7280;
    }

    .btn-secondary:hover {
        background: #4b5563;
        border-color: #4b5563;
    }

    /* Badge Enhancement */
    .badge {
        padding: 6px 12px;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0.3px;
        border-radius: 6px;
    }

    .badge.bg-success {
        background: var(--primary-green) !important;
    }

    .badge.bg-warning {
        background: var(--accent-orange) !important;
        color: white !important;
    }

    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        #struk-for-print,
        #struk-for-print * {
            visibility: visible;
        }
        #struk-for-print {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
        }
        .struk-print-area {
            max-width: 400px;
            margin: 0 auto;
        }
        .struk-print-area h3,
        .struk-print-area h4 {
            text-align: center;
            margin: 10px 0;
        }
        .struk-print-area table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .struk-print-area table th,
        .struk-print-area table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .struk-print-area .struk-footer {
            text-align: center;
            margin-top: 20px;
            border-top: 2px dashed #000;
            padding-top: 15px;
        }
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-state i {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 15px;
    }

    .empty-state p {
        color: #9ca3af;
        margin: 0;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../views/'.$THEME.'/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php include '../views/'.$THEME.'/upper_block.php'; ?>

            <!-- Page Header -->
            <div class="page-header-detail">
                <div>
                    <h2><i class="bi bi-receipt"></i> Detail Booking #<?= $booking['id'] ?></h2>
                    <small style="opacity: 0.9;"><?= htmlspecialchars($booking['nomor_booking']) ?></small>
                </div>
                <span class="badge bg-<?= $booking['status_pembayaran'] === 'LUNAS' ? 'success' : 'warning' ?>">
                    <?= htmlspecialchars($booking['status_pembayaran']) ?>
                </span>
            </div>

            <!-- Booking Information Card -->
            <div class="info-card">
                <h5><i class="bi bi-info-circle"></i> Informasi Booking</h5>
                
                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-hash"></i> Nomor Booking
                    </div>
                    <div class="info-value">
                        <span class="badge bg-secondary"><?= htmlspecialchars($booking['nomor_booking']) ?></span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-calendar-plus"></i> Tanggal Booking
                    </div>
                    <div class="info-value"><?= date('d F Y', strtotime($booking['tanggal_booking'])) ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-calendar-event"></i> Tanggal Main
                    </div>
                    <div class="info-value">
                        <strong><?= date('d F Y', strtotime($booking['tanggal_main'])) ?></strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-clock"></i> Waktu Main
                    </div>
                    <div class="info-value">
                        <strong><?= htmlspecialchars($booking['jam_mulai']) ?> - <?= htmlspecialchars($booking['jam_selesai']) ?></strong>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-person"></i> Nama Pemesan
                    </div>
                    <div class="info-value"><?= htmlspecialchars($booking['nama_pemesan'] ?? '-') ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-phone"></i> No. HP
                    </div>
                    <div class="info-value"><?= htmlspecialchars($booking['no_hp'] ?? '-') ?></div>
                </div>

                <div class="info-row">
                    <div class="info-label">
                        <i class="bi bi-chat-left-text"></i> Catatan
                    </div>
                    <div class="info-value"><?= htmlspecialchars($booking['catatan']) ?: '-' ?></div>
                </div>

                <div class="action-buttons">
                    <?php if ($booking['status_pembayaran'] !== 'LUNAS'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCSRFToken()) ?>">
                            <input type="hidden" name="set_lunas" value="1">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Set LUNAS
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($booking['status_pembayaran'] === 'LUNAS'): ?>
                        <button id="print-receipt-btn" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Cetak Struk
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Detail Items Table -->
            <div class="table-section">
                <div class="table-section-header">
                    <h5><i class="bi bi-list-ul"></i> Daftar Item Booking</h5>
                    <?php if ($booking['status_pembayaran'] !== 'LUNAS'): ?>
                        <a href="detailadd.php?booking_id=<?= $id ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Tambah Item
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (mysqli_num_rows($details_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Jumlah</th>
                                    <th class="text-end">Subtotal Harga</th>
                                    <?php if ($booking['status_pembayaran'] !== 'LUNAS'): ?><th>Aksi</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total = 0;
                                while ($detail = mysqli_fetch_assoc($details_result)): 
                                    $total += (float)($detail['subtotal_harga'] ?? 0);
                                ?>
                                    <tr>
                                        <td>
                                            <!-- Tampilkan nama item yang diambil dari JOIN -->
                                            <span class="badge bg-secondary"><?= ucfirst($detail['item_type']) ?></span>
                                            <?= htmlspecialchars($detail['item_name']) ?>
                                        </td>
                                        <td><i class="bi bi-cart-plus"></i> <?= (int)$detail['jumlah'] ?></td>
                                        <td class="text-end"><strong>Rp <?= number_format((float)($detail['subtotal_harga'] ?? 0), 0, ',', '.') ?></strong></td>
                                        <?php if ($booking['status_pembayaran'] !== 'LUNAS'): ?>
                                            <td>
                                                <a href="detaildelete.php?id=<?= $detail['id'] ?>&master_id=<?=$id;?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus item ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                                <tr style="background: #f9fafb; font-weight: bold;">
                                    <td colspan="2" class="text-end">TOTAL:</td>
                                    <td class="text-end" style="color: var(--primary-green); font-size: 16px;">
                                        Rp <?= number_format($total, 0, ',', '.') ?>
                                    </td>
                                    <?php if ($booking['status_pembayaran'] !== 'LUNAS'): ?><td></td><?php endif; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada data detail booking</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Navigation Buttons -->
            <div class="d-flex gap-2">
                <?php 
                // Logika Penentuan Tombol Kembali
                // Jika status LUNAS, kembali ke history.php. Jika belum, ke index.php
                $back_link = ($booking['status_pembayaran'] === 'LUNAS') ? 'history.php' : 'index.php';
                $back_text = ($booking['status_pembayaran'] === 'LUNAS') ? 'Kembali ke Riwayat' : 'Kembali ke Booking Aktif';
                ?>
                
                <a href="<?= $back_link ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> <?= $back_text ?>
                </a>

                <?php if ($booking['status_pembayaran'] !== 'LUNAS'): ?>
                    <a href="edit.php?id=<?= $id ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Booking
                    </a>
                <?php else: ?>
                    <?php endif; ?>
            </div>

            <?php include '../views/'.$THEME.'/lower_block.php'; ?>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const printBtn = document.getElementById('print-receipt-btn');

    if (printBtn) {
        printBtn.addEventListener('click', function (e) {
            e.preventDefault();

            // 1. Hapus struk lama jika ada agar tidak menumpuk di bawah
            const oldStruk = document.getElementById('struk-for-print');
            if (oldStruk) oldStruk.remove();

            // 2. Buat konten struk (ambil data dari PHP atau dari tabel HTML)
            // Untuk sederhananya, kita ambil dari PHP
            const strukContent = `
                <div class="struk-print-area" style="width: 100%; color: black;">
                    <div style="text-align: center; margin-bottom: 10px;">
                        <h2 style="margin: 0; font-size: 18px; text-transform: uppercase;">Mini Soccer Rental</h2>
                        <p style="margin: 2px 0; font-size: 11px;">Jl. Raya Olahraga No. 123, Sumber</p>
                    </div>
                    <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                    <div style="font-size: 12px;">
                        <table style="width: 100%;">
                            <tr><td>No. Booking</td><td align="right">#<?= htmlspecialchars($booking['nomor_booking']) ?></td></tr>
                            <tr><td>Nama</td><td align="right"><?= htmlspecialchars($booking['nama_pemesan'] ?? '-') ?></td></tr>
                            <tr><td>Jadwal</td><td align="right"><?= date('d/m/Y', strtotime($booking['tanggal_main'])) ?></td></tr>
                            <tr><td>Jam</td><td align="right"><?= substr($booking['jam_mulai'], 0, 5) ?> - <?= substr($booking['jam_selesai'], 0, 5) ?></td></tr>
                        </table>
                    </div>
                    <div style="border-top: 1px dashed #000; margin: 10px 0;"></div>
                    <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 0.5px solid #000;">
                                <th align="left" style="padding: 5px 0;">Item</th>
                                <th align="right" style="padding: 5px 0;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($details_result, 0); // Reset pointer result set
                            while ($detail = mysqli_fetch_assoc($details_result)):
                            ?>
                                <tr>
                                    <td style="padding: 5px 0;">
                                        <!-- Tampilkan nama item yang diambil dari JOIN di struk juga -->
                                        <?= htmlspecialchars($detail['item_name']) ?> (<?= (int)$detail['jumlah'] ?>x)
                                    </td>
                                    <td align="right">Rp <?= number_format((float)$detail['subtotal_harga'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <div style="border-top: 1px double #000; margin: 10px 0;"></div>
                    <div style="font-size: 14px; display: flex; justify-content: space-between; font-weight: bold;">
                        <span>TOTAL</span>
                        <span>Rp <?= number_format((float)$booking['total_harga'], 0, ',', '.') ?></span>
                    </div>
                    <div style="text-align: center; margin-top: 15px; font-size: 10px;">
                        <p>*** LUNAS ***</p>
                        <p>Terima kasih atas kunjungan Anda!</p>
                    </div>
                </div>
            `;

            // 3. Buat elemen kontainer struk
            const strukDiv = document.createElement('div');
            strukDiv.id = 'struk-for-print';
            strukDiv.style.display = 'none'; // Sembunyikan dari layar browser secara default
            strukDiv.innerHTML = strukContent;
            document.body.appendChild(strukDiv);

            // 4. Jalankan perintah print
            window.print();

            // 5. Hapus elemen struk segera setelah jendela print ditutup agar tidak muncul di bawah
            setTimeout(() => {
                strukDiv.remove();
            }, 1000);
        });
    }
});
</script>

<?php include '../views/'.$THEME.'/footer.php'; ?>