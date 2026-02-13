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
if (!$id) redirect('index.php');

$stmt = mysqli_prepare($connection, "SELECT * FROM `booking` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$booking) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid CSRF token.');

    $nomor_booking_post = trim($_POST['nomor_booking'] ?? '');
    $user_id_post = trim($_POST['user_id'] ?? '');
    $tanggal_booking_post = trim($_POST['tanggal_booking'] ?? '');
    $tanggal_main_post = trim($_POST['tanggal_main'] ?? '');
    $jam_mulai_post = trim($_POST['jam_mulai'] ?? '');
    $jam_selesai_post = trim($_POST['jam_selesai'] ?? '');
    $total_harga_post = trim($_POST['total_harga'] ?? '');
    $status_pembayaran_post = trim($_POST['status_pembayaran'] ?? '');
    $catatan_post = trim($_POST['catatan'] ?? '');
    $created_at_post = trim($_POST['created_at'] ?? '');

    if (empty($nomor_booking_post) || empty($tanggal_booking_post) || empty($tanggal_main_post) || empty($jam_mulai_post) || empty($jam_selesai_post)) {
        $error = "Field Nomor Booking, Tanggal Booking, Tanggal Main, Jam Mulai, dan Jam Selesai wajib diisi.";
    }

    if (!$error) {
        if ($user_id_post === '') {
            $stmt = mysqli_prepare($connection, "UPDATE `booking` SET `nomor_booking` = ?, `user_id` = NULL, `tanggal_booking` = ?, `tanggal_main` = ?, `jam_mulai` = ?, `jam_selesai` = ?, `total_harga` = ?, `status_pembayaran` = ?, `catatan` = ?, `created_at` = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssssdsssi", $nomor_booking_post, $tanggal_booking_post, $tanggal_main_post, $jam_mulai_post, $jam_selesai_post, $total_harga_post, $status_pembayaran_post, $catatan_post, $created_at_post, $id);
        } else {
            $stmt = mysqli_prepare($connection, "UPDATE `booking` SET `nomor_booking` = ?, `user_id` = ?, `tanggal_booking` = ?, `tanggal_main` = ?, `jam_mulai` = ?, `jam_selesai` = ?, `total_harga` = ?, `status_pembayaran` = ?, `catatan` = ?, `created_at` = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sissssdsssi", $nomor_booking_post, $user_id_post, $tanggal_booking_post, $tanggal_main_post, $jam_mulai_post, $jam_selesai_post, $total_harga_post, $status_pembayaran_post, $catatan_post, $created_at_post, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($connection, "SELECT * FROM `booking` WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            $success = "Booking berhasil diperbarui.";
        } else {
            $error = "Gagal memperbarui booking: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    }
}
$csrfToken = generateCSRFToken();
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
    .page-header-edit {
        background: linear-gradient(135deg, var(--accent-orange) 0%, #d97706 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .page-header-edit h2 {
        margin: 0;
        font-weight: 700;
        font-size: 28px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header-edit p {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    /* Form Container */
    .form-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 35px;
        margin-bottom: 25px;
    }

    /* Form Section Headers */
    .form-section {
        margin-bottom: 30px;
    }

    .form-section-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--dark-bg);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 3px solid var(--accent-orange);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Form Enhancements */
    .form-label {
        font-weight: 600;
        color: var(--dark-bg);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label i {
        color: var(--accent-orange);
    }

    .required-indicator {
        color: #ef4444;
        margin-left: 3px;
    }

    .form-control,
    .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--accent-orange);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }

    .form-control[readonly] {
        background-color: #f3f4f6;
        cursor: not-allowed;
    }

    /* Alert Enhancements */
    .alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert i {
        font-size: 24px;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .alert-success {
        background: var(--light-green);
        color: var(--dark-green);
    }

    /* Helper Text */
    .form-text {
        font-size: 13px;
        color: #6b7280;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-text i {
        font-size: 14px;
    }

    /* Status Badge in Form */
    .status-badge-preview {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        margin-left: 10px;
    }

    .status-badge-preview.menunggu {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge-preview.lunas {
        background: var(--light-green);
        color: var(--dark-green);
    }

    /* Button Enhancements */
    .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 12px 25px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: var(--accent-orange);
        border-color: var(--accent-orange);
    }

    .btn-primary:hover {
        background: #d97706;
        border-color: #d97706;
    }

    .btn-secondary {
        background: #6b7280;
        border-color: #6b7280;
    }

    .btn-secondary:hover {
        background: #4b5563;
        border-color: #4b5563;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 2px dashed #e5e7eb;
    }

    /* Grid Layout for Form Fields */
    .form-row-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .form-row-2col {
            grid-template-columns: 1fr;
        }
    }

    /* Info Box */
    .info-box {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .info-box i {
        color: #3b82f6;
        font-size: 20px;
        margin-right: 10px;
    }

    .info-box p {
        margin: 0;
        color: #1e40af;
        font-size: 14px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../views/'.$THEME.'/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php include '../views/'.$THEME.'/upper_block.php'; ?>

            <!-- Page Header -->
            <div class="page-header-edit">
                <h2>
                    <i class="bi bi-pencil-square"></i> Edit Booking
                </h2>
                <p>Perbarui informasi booking #<?= $booking['id'] ?> - <?= htmlspecialchars($booking['nomor_booking']) ?></p>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                <strong>Perhatian:</strong> Pastikan data yang Anda ubah sudah benar sebelum menyimpan perubahan.
            </div>

            <!-- Alerts -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?= $success ?></div>
                </div>
            <?php endif; ?>

            <!-- Form Container -->
            <div class="form-container">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= $booking['id'] ?>">

                    <!-- Section 1: Informasi Booking -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-bookmark-fill"></i> Informasi Booking
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-hash"></i> Nomor Booking
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="text" name="nomor_booking" class="form-control" value="<?= htmlspecialchars($booking['nomor_booking']) ?>" required>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Nomor unik untuk identifikasi booking
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-person-badge"></i> User ID
                            </label>
                            <input type="number" name="user_id" class="form-control" value="<?= $booking['user_id'] ?? '' ?>" placeholder="Biarkan kosong jika tidak ada user">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Biarkan kosong jika booking dibuat oleh user anonim
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Jadwal & Waktu -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-calendar-range"></i> Jadwal & Waktu
                        </div>

                        <div class="form-row-2col mb-3">
                            <div>
                                <label class="form-label">
                                    <i class="bi bi-calendar-plus"></i> Tanggal Booking
                                    <span class="required-indicator">*</span>
                                </label>
                                <input type="date" name="tanggal_booking" class="form-control" value="<?= $booking['tanggal_booking'] ?>" required>
                            </div>
                            <div>
                                <label class="form-label">
                                    <i class="bi bi-calendar-event"></i> Tanggal Main
                                    <span class="required-indicator">*</span>
                                </label>
                                <input type="date" name="tanggal_main" class="form-control" value="<?= $booking['tanggal_main'] ?>" required>
                            </div>
                        </div>

                        <div class="form-row-2col mb-3">
                            <div>
                                <label class="form-label">
                                    <i class="bi bi-clock"></i> Jam Mulai
                                    <span class="required-indicator">*</span>
                                </label>
                                <input type="time" name="jam_mulai" class="form-control" value="<?= htmlspecialchars($booking['jam_mulai']) ?>" required>
                            </div>
                            <div>
                                <label class="form-label">
                                    <i class="bi bi-clock-fill"></i> Jam Selesai
                                    <span class="required-indicator">*</span>
                                </label>
                                <input type="time" name="jam_selesai" class="form-control" value="<?= htmlspecialchars($booking['jam_selesai']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Pembayaran & Status -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-cash-stack"></i> Pembayaran & Status
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-currency-dollar"></i> Total Harga
                            </label>
                            <input type="number" step="0.01" name="total_harga" class="form-control" value="<?= $booking['total_harga'] ?>">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Dalam Rupiah (Rp)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-credit-card"></i> Status Pembayaran
                            </label>
                            <select name="status_pembayaran" class="form-select" id="status-select">
                                <option value="MENUNGGU" <?= $booking['status_pembayaran'] === 'MENUNGGU' ? 'selected' : '' ?>>MENUNGGU</option>
                                <option value="LUNAS" <?= $booking['status_pembayaran'] === 'LUNAS' ? 'selected' : '' ?>>LUNAS</option>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Status pembayaran saat ini: 
                                <span class="status-badge-preview <?= strtolower($booking['status_pembayaran']) ?>" id="status-preview">
                                    <?= $booking['status_pembayaran'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Informasi Tambahan -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-chat-square-text"></i> Informasi Tambahan
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-chat-left-text"></i> Catatan
                            </label>
                            <textarea name="catatan" class="form-control" rows="3" placeholder="Tambahkan catatan atau informasi tambahan..."><?= htmlspecialchars($booking['catatan']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-clock-history"></i> Created At (Tidak Dapat Diubah)
                            </label>
                            <input type="datetime-local" name="created_at" class="form-control" value="<?= date('Y-m-d\TH:i:s', strtotime($booking['created_at'])) ?>" readonly>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Waktu pembuatan booking pertama kali
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Perbarui Booking
                        </button>
                        <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>

            <?php include '../views/'.$THEME.'/lower_block.php'; ?>
        </main>
    </div>
</div>

<script>
// Update status badge preview when select changes
document.getElementById('status-select')?.addEventListener('change', function() {
    const statusPreview = document.getElementById('status-preview');
    const selectedValue = this.value;
    
    statusPreview.textContent = selectedValue;
    statusPreview.className = 'status-badge-preview ' + selectedValue.toLowerCase();
});

// Form validation
document.querySelector('form')?.addEventListener('submit', function(e) {
    const jamMulai = document.querySelector('input[name="jam_mulai"]').value;
    const jamSelesai = document.querySelector('input[name="jam_selesai"]').value;
    
    if (jamMulai && jamSelesai && jamMulai >= jamSelesai) {
        e.preventDefault();
        alert('Jam selesai harus lebih besar dari jam mulai!');
        return false;
    }
});
</script>

<?php include '../views/'.$THEME.'/footer.php'; ?>