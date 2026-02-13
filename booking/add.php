<?php
// booking/add.php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.use_strict_mode', 1);
session_start();

require_once '../config/database.php';
require_once '../lib/functions.php';

$page_title = "Booking Lapangan";
$THEME = THEME_NAME ?? 'default';

// Inisialisasi variabel untuk form
$selected_field_id = (int)($_GET['lapangan'] ?? 0);
$available_fields = getAvailableFields($connection);

$error = $success = '';
$tanggal_main = '';
$jam_mulai = '';
$jam_selesai = '';
$nama_pemesan = '';
$no_hp = '';
$catatan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data langsung dari POST di awal blok POST
    $field_id_post = (int)($_POST['lapangan_id'] ?? 0);
    $tanggal_main_post = $_POST['tanggal_main'] ?? '';
    $jam_mulai_post = $_POST['jam_mulai'] ?? '';
    $jam_selesai_post = $_POST['jam_selesai'] ?? '';
    $nama_pemesan_post = sanitize($_POST['nama_pemesan'] ?? '');
    $no_hp_post = sanitize($_POST['no_hp'] ?? '');
    $catatan_post = sanitize($_POST['catatan'] ?? '');

    // Gunakan variabel _post untuk validasi dan proses
    if (!$field_id_post || empty($tanggal_main_post) || empty($jam_mulai_post) || empty($jam_selesai_post) || empty($nama_pemesan_post) || empty($no_hp_post)) {
        $error = "Semua field wajib diisi.";
    } elseif ($jam_mulai_post >= $jam_selesai_post) {
        $error = "Jam selesai harus lebih besar dari jam mulai.";
    } elseif (!isSlotAvailable($connection, $field_id_post, $tanggal_main_post, $jam_mulai_post, $jam_selesai_post)) {
         $error = "Slot waktu pada tanggal dan jam tersebut sudah dipesan. Silakan pilih waktu lain.";
    } else {
        // Jika semua validasi lolos, lanjutkan ke proses INSERT
        $start_time = strtotime($jam_mulai_post);
        $end_time = strtotime($jam_selesai_post);
        $duration_hours = round(($end_time - $start_time) / 3600, 2);

        $field_query = "SELECT harga_per_jam FROM lapangan WHERE id = ?";
        $field_stmt = mysqli_prepare($connection, $field_query);
        mysqli_stmt_bind_param($field_stmt, "i", $field_id_post);
        mysqli_stmt_execute($field_stmt);
        $field_result = mysqli_stmt_get_result($field_stmt);
        $field_data = mysqli_fetch_assoc($field_result);
        mysqli_stmt_close($field_stmt);

        if (!$field_data) {
            $error = "Data lapangan tidak ditemukan.";
        } else {
            $harga_per_jam = $field_data['harga_per_jam'];
            $total_harga = $duration_hours * $harga_per_jam;
            $nomor_booking = generateNumericBookId();
            $user_id_for_booking = null;
            $status_default = 'MENUNGGU';

            $insert_master_sql = "INSERT INTO booking (
                nomor_booking, 
                user_id, 
                nama_pemesan, 
                no_hp, 
                tanggal_booking, 
                tanggal_main, 
                jam_mulai, 
                jam_selesai, 
                total_harga, 
                status_pembayaran, 
                catatan
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";

            $stmt_master = mysqli_prepare($connection, $insert_master_sql);
            mysqli_stmt_bind_param($stmt_master, "sisssssdss", 
                $nomor_booking, 
                $user_id_for_booking, 
                $nama_pemesan_post, 
                $no_hp_post, 
                $tanggal_main_post, 
                $jam_mulai_post, 
                $jam_selesai_post, 
                $total_harga, 
                $status_default, 
                $catatan_post
            );

            if (mysqli_stmt_execute($stmt_master)) {
                $booking_id = mysqli_insert_id($connection);
                $subtotal_detail = $duration_hours * $harga_per_jam;

                $insert_detail_sql = "INSERT INTO booking_detail (booking_id, lapangan_id, jumlah, subtotal_harga) VALUES (?, ?, ?, ?)";
                $stmt_detail = mysqli_prepare($connection, $insert_detail_sql);
                mysqli_stmt_bind_param($stmt_detail, "iidd", $booking_id, $field_id_post, $duration_hours, $subtotal_detail);

                if (mysqli_stmt_execute($stmt_detail)) {
                    $success = "Booking berhasil! Nomor Booking Anda: <strong>" . $nomor_booking . "</strong>. Status: MENUNGGU. Silakan datang ke lokasi untuk verifikasi dan pembayaran.";
                 
                    // TAMBAHKAN VARIABEL INI UNTUK TRIGER JS
                    $pdf_trigger_id = $booking_id; 

                    // Reset form variables after success
                    $selected_field_id = 0;
                } else {
                    $error = "Gagal menyimpan detail booking: " . mysqli_error($connection);
                }
                mysqli_stmt_close($stmt_detail);
            } else {
                 $error = "Gagal menyimpan booking: " . mysqli_error($connection);
            }
            mysqli_stmt_close($stmt_master);
        }
    }
    // Jika $error diisi, loop kembali ke render form dengan error
    // Pastikan nilai-nilai yang ditampilkan di form berasal dari POST jika error
    if ($error) {
        $tanggal_main = $tanggal_main_post;
        $jam_mulai = $jam_mulai_post;
        $jam_selesai = $jam_selesai_post;
        $nama_pemesan = $nama_pemesan_post;
        $no_hp = $no_hp_post;
        $catatan = $catatan_post;
        $selected_field_id = $field_id_post;
    }
}
?>

<?php include '../views/' . $THEME . '/header.php'; ?>
<?php include '../views/' . $THEME . '/topnav.php'; ?>

<style>
    /* ... (gunakan style yang sama seperti sebelumnya) ... */
    /* Sport Theme Colors */
    :root {
        --primary-green: #10b981;
        --dark-green: #059669;
        --light-green: #d1fae5;
        --accent-orange: #f59e0b;
        --dark-bg: #1f2937;
        --light-bg: #f9fafb;
    }

    /* Page Container */
    .booking-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 30px 15px;
    }

    /* Page Header */
    .page-header-booking {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: white;
        padding: 40px 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .page-header-booking h2 {
        margin: 0;
        font-weight: 700;
        font-size: 32px;
    }

    .page-header-booking p {
        margin: 10px 0 0 0;
        opacity: 0.9;
        font-size: 16px;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 35px;
        margin-bottom: 25px;
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
        color: var(--primary-green);
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
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* Required Indicator */
    .required-indicator {
        color: #ef4444;
        margin-left: 3px;
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

    /* Success Card */
    .success-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 40px;
        text-align: center;
    }

    .success-card i {
        font-size: 64px;
        color: var(--primary-green);
        margin-bottom: 20px;
    }

    .success-card h3 {
        color: var(--dark-bg);
        margin-bottom: 15px;
    }

    .success-card .booking-number {
        background: var(--light-green);
        color: var(--dark-green);
        padding: 15px 25px;
        border-radius: 8px;
        font-size: 24px;
        font-weight: 700;
        display: inline-block;
        margin: 20px 0;
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

    .btn-success {
        background: var(--primary-green);
        border-color: var(--primary-green);
    }

    .btn-success:hover {
        background: var(--dark-green);
        border-color: var(--dark-green);
    }

    .btn-primary {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .btn-primary:hover {
        background: #2563eb;
        border-color: #2563eb;
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

    /* Helper Text */
    .form-text {
        font-size: 13px;
        color: #6b7280;
        margin-top: 5px;
    }

    /* Field Info Badge */
    .field-info-badge {
        background: var(--light-green);
        color: var(--dark-green);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 8px;
    }
</style>

<div class="container-fluid">
    <div class="booking-container">
        
        <!-- Page Header -->
        <div class="page-header-booking">
            <h2><i class="bi bi-calendar-plus"></i> Form Booking Lapangan</h2>
            <p>Isi formulir di bawah untuk melakukan reservasi lapangan mini soccer</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= $error ?></div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-card">
                <i class="bi bi-check-circle-fill"></i>
                <h3>Booking Berhasil!</h3>
                <p>Nomor booking Anda adalah:</p>
                <div class="booking-number"><?= explode(':', $success)[1] ?? '' ?></div>
                
                <div class="mt-3 mb-4">
                    <a href="generate_pdf.php?id=<?= $pdf_trigger_id ?>" target="_blank" class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Download Bukti PDF
                    </a>
                </div>

                <div style="background: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <i class="bi bi-info-circle"></i>
                    <strong>Status: MENUNGGU</strong><br>
                    Silakan datang ke lokasi untuk verifikasi dan pembayaran
                </div>
                <a href="../index.php" class="btn btn-primary">
                    <i class="bi bi-house"></i> Kembali ke Home
                </a>

                <script>
                    window.onload = function() {
                        // Membuka PDF di tab baru secara otomatis
                        window.open('generate_pdf.php?id=<?= $pdf_trigger_id ?>', '_blank');
                    }
                </script>
            </div>
        <?php else: ?>
            <!-- Booking Form -->
            <div class="form-card">
                <form method="POST" autocomplete="off"> <!-- Tambahkan autocomplete="off" -->
                    
                    <!-- Pilih Lapangan -->
                    <div class="mb-4">
                        <label for="lapangan_id" class="form-label">
                            <i class="bi bi-geo-alt-fill"></i> Pilih Lapangan
                            <span class="required-indicator">*</span>
                        </label>
                        <select name="lapangan_id" id="lapangan_id" class="form-select" required onchange="toggleFieldSpecificInputs()">
                            <option value="">-- Pilih Lapangan --</option>
                            <?php foreach ($available_fields as $field): ?>
                                <option value="<?= $field['id'] ?>" <?= $selected_field_id == $field['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($field['nama_lapangan']) ?>
                                    <span class="field-info-badge">Rp <?= number_format($field['harga_per_jam'], 0, ',', '.') ?>/jam</span>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Pilih lapangan yang ingin Anda sewa</div>
                    </div>

                    <!-- Tanggal Main -->
                    <div class="mb-4">
                        <label for="tanggal_main" class="form-label">
                            <i class="bi bi-calendar-event"></i> Tanggal Main
                            <span class="required-indicator">*</span>
                        </label>
                        <input type="date" name="tanggal_main" id="tanggal_main" class="form-control" value="<?= htmlspecialchars($tanggal_main) ?>" required min="<?= date('Y-m-d') ?>">
                        <div class="form-text">Pilih tanggal untuk bermain (minimal hari ini)</div>
                    </div>

                    <!-- Waktu Main -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="jam_mulai" class="form-label">
                                <i class="bi bi-clock"></i> Jam Mulai
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="time" name="jam_mulai" id="jam_mulai" class="form-control" value="<?= htmlspecialchars($jam_mulai) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jam_selesai" class="form-label">
                                <i class="bi bi-clock-fill"></i> Jam Selesai
                                <span class="required-indicator">*</span>
                            </label>
                            <input type="time" name="jam_selesai" id="jam_selesai" class="form-control" value="<?= htmlspecialchars($jam_selesai) ?>" required>
                        </div>
                        <div class="col-12">
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle"></i> Durasi sewa akan dihitung otomatis berdasarkan jam mulai dan selesai
                            </div>
                        </div>
                    </div>

                    <!-- Data Pemesan -->
                    <div class="mb-4">
                        <label for="nama_pemesan" class="form-label">
                            <i class="bi bi-person-fill"></i> Nama Pemesan
                            <span class="required-indicator">*</span>
                        </label>
                        <input type="text" name="nama_pemesan" id="nama_pemesan" class="form-control" value="<?= htmlspecialchars($nama_pemesan) ?>" required placeholder="Masukkan nama lengkap">
                    </div>

                    <div class="mb-4">
                        <label for="no_hp" class="form-label">
                            <i class="bi bi-phone-fill"></i> No. HP/WA Aktif
                            <span class="required-indicator">*</span>
                        </label>
                        <input type="tel" name="no_hp" id="no_hp" class="form-control" value="<?= htmlspecialchars($no_hp) ?>" required pattern="[0-9]*" placeholder="Contoh: 081234567890">
                        <div class="form-text">Nomor yang dapat dihubungi untuk konfirmasi booking</div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-4">
                        <label for="catatan" class="form-label">
                            <i class="bi bi-chat-left-text"></i> Catatan (Opsional)
                        </label>
                        <textarea name="catatan" id="catatan" class="form-control" rows="3" placeholder="Tambahkan catatan atau permintaan khusus..."><?= htmlspecialchars($catatan) ?></textarea>
                        <div class="form-text">Contoh: Perlu bola, rompi, atau permintaan khusus lainnya</div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Submit Booking
                        </button>
                        <a href="../index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function toggleFieldSpecificInputs() {
        // Optional: Add field-specific logic here
    }

    // Tambahkan event listener untuk mencegah submit ganda dan validasi sisi klien dasar
    document.querySelector('form')?.addEventListener('submit', function(e) {
        const startTime = document.getElementById('jam_mulai').value;
        const endTime = document.getElementById('jam_selesai').value;

        if (startTime && endTime && startTime >= endTime) {
            e.preventDefault();
            alert('Jam selesai harus lebih besar dari jam mulai.');
            return false;
        }
    });

    // Opsional: Tambahkan script untuk membersihkan nilai form jika error muncul,
    // untuk mencegah browser mengisi ulang dengan nilai lama setelah refresh manual.
    // Namun, ini bisa mengganggu UX jika user hanya ingin mengoreksi sedikit.
    // Kita abaikan dulu, karena logika PHP seharusnya sudah cukup.
</script>

<?php include '../views/' . $THEME . '/footer.php'; ?>