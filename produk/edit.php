<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('produk');

require_once '../config/database.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

$stmt = mysqli_prepare($connection, "SELECT id, kode_produk, nama_produk, jenis_produk, harga, deskripsi, status_produk FROM `produk` WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$produk) {
    redirect('index.php');
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_produk_post = trim($_POST['kode_produk'] ?? '');
    $nama_produk_post = trim($_POST['nama_produk'] ?? '');
    $jenis_produk_post = trim($_POST['jenis_produk'] ?? '');
    $harga_post = trim($_POST['harga'] ?? '');
    $deskripsi_post = trim($_POST['deskripsi'] ?? '');
    $status_produk_post = trim($_POST['status_produk'] ?? '');
    
    if (empty($kode_produk_post) || empty($nama_produk_post) || empty($jenis_produk_post) || empty($harga_post)) {
        $error = "Kode Produk, Nama Produk, Jenis Produk, dan Harga wajib diisi.";
    }
    
    if (!$error) {
        $stmt = mysqli_prepare($connection, "UPDATE `produk` SET `kode_produk` = ?, `nama_produk` = ?, `jenis_produk` = ?, `harga` = ?, `deskripsi` = ?, `status_produk` = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssdssi", $kode_produk_post, $nama_produk_post, $jenis_produk_post, $harga_post, $deskripsi_post, $status_produk_post, $id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Produk berhasil diperbarui.";
            mysqli_stmt_close($stmt);
            // Refresh data setelah update
            $stmt = mysqli_prepare($connection, "SELECT id, kode_produk, nama_produk, jenis_produk, harga, deskripsi, status_produk FROM `produk` WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        } else {
            $error = "Gagal memperbarui: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>

<style>
    :root {
        --primary-orange: #f59e0b;
        --dark-orange: #f97316;
        --light-orange: #fef3c7;
        --dark-bg: #1f2937;
    }

    .page-header-form {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--dark-orange) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .page-header-form h2 {
        margin: 0;
        font-weight: 700;
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-header-form p {
        margin: 5px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 30px;
        margin-bottom: 25px;
    }

    .form-section {
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 2px dashed #e5e7eb;
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .form-section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--dark-bg);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-label-custom {
        font-weight: 600;
        color: var(--dark-bg);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
    }

    .form-label-custom .required {
        color: #dc3545;
        font-size: 16px;
    }

    .form-label-custom i {
        color: var(--primary-orange);
    }

    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.15);
        outline: none;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .input-group-text {
        background: var(--light-orange);
        border: 2px solid #e5e7eb;
        border-right: none;
        font-weight: 600;
        color: var(--primary-orange);
    }

    .input-group .form-control {
        border-left: none;
    }

    .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 12px 24px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--dark-orange) 100%);
        border: none;
        color: white;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, var(--dark-orange) 0%, var(--primary-orange) 100%);
        color: white;
    }

    .btn-secondary {
        background: #6b7280;
        border: none;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        padding-top: 20px;
        border-top: 2px dashed #e5e7eb;
        margin-top: 20px;
    }

    .alert {
        border-radius: 8px;
        border: none;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-left: 4px solid #dc3545;
    }

    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border-left: 4px solid #28a745;
    }

    .alert i {
        font-size: 24px;
    }

    .helper-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
        .action-buttons .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../views/'.$THEME.'/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php include '../views/'.$THEME.'/upper_block.php'; ?>

            <!-- Page Header -->
            <div class="page-header-form">
                <h2>
                    <i class="bi bi-pencil-square"></i>
                    Edit Produk
                </h2>
                <p><i class="bi bi-info-circle"></i> Perbarui informasi produk "#<?= $produk['id'] ?> - <?= htmlspecialchars($produk['nama_produk']) ?>"</p>
            </div>

            <!-- Error/Success Alert -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Oops! Ada kesalahan</strong>
                        <p class="mb-0 mt-1"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <strong>Berhasil!</strong>
                        <p class="mb-0 mt-1"><?= htmlspecialchars($success) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="form-card">
                <form method="POST" id="productForm">
                    
                    <!-- Informasi Dasar -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-info-circle"></i>
                            Informasi Dasar
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">
                                <i class="bi bi-upc-scan"></i>
                                Kode Produk
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="kode_produk" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($produk['kode_produk']) ?>" 
                                   required
                                   placeholder="Contoh: PRD001">
                            <div class="helper-text">
                                <i class="bi bi-lightbulb"></i>
                                Kode unik untuk identifikasi produk
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">
                                <i class="bi bi-box-seam"></i>
                                Nama Produk
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_produk" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($produk['nama_produk']) ?>" 
                                   required
                                   placeholder="Contoh: Air Mineral Le Minerale (600ml)">
                            <div class="helper-text">
                                <i class="bi bi-lightbulb"></i>
                                Nama lengkap produk yang akan ditampilkan
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">
                                    <i class="bi bi-tag"></i>
                                    Jenis Produk
                                    <span class="required">*</span>
                                </label>
                                <select name="jenis_produk" class="form-select" required>
                                    <option value="Alat" <?= $produk['jenis_produk'] === 'Alat' ? 'selected' : '' ?>>
                                        🛠️ Alat
                                    </option>
                                    <option value="Minuman" <?= $produk['jenis_produk'] === 'Minuman' ? 'selected' : '' ?>>
                                        🥤 Minuman
                                    </option>
                                    <option value="Snack" <?= $produk['jenis_produk'] === 'Snack' ? 'selected' : '' ?>>
                                        🍿 Snack
                                    </option>
                                    <option value="Makanan" <?= $produk['jenis_produk'] === 'Makanan' ? 'selected' : '' ?>>
                                        🍱 Makanan
                                    </option>
                                    <option value="Lainnya" <?= $produk['jenis_produk'] === 'Lainnya' ? 'selected' : '' ?>>
                                        📦 Lainnya
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label-custom">
                                    <i class="bi bi-toggle-on"></i>
                                    Status Produk
                                </label>
                                <select name="status_produk" class="form-select">
                                    <option value="Aktif" <?= $produk['status_produk'] === 'Aktif' ? 'selected' : '' ?>>
                                        ✅ Aktif
                                    </option>
                                    <option value="Non-Aktif" <?= $produk['status_produk'] === 'Non-Aktif' ? 'selected' : '' ?>>
                                        ❌ Non-Aktif
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Harga & Deskripsi -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="bi bi-currency-dollar"></i>
                            Harga & Detail
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">
                                <i class="bi bi-cash-stack"></i>
                                Harga
                                <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" 
                                       name="harga" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($produk['harga']) ?>" 
                                       required
                                       min="0"
                                       step="1"
                                       placeholder="5000">
                            </div>
                            <div class="helper-text">
                                <i class="bi bi-lightbulb"></i>
                                Masukkan harga dalam Rupiah (tanpa titik/koma)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">
                                <i class="bi bi-text-paragraph"></i>
                                Deskripsi Produk
                            </label>
                            <textarea name="deskripsi" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Masukkan deskripsi produk (opsional)..."><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                            <div class="helper-text">
                                <i class="bi bi-lightbulb"></i>
                                Deskripsi lengkap tentang produk (opsional)
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i>
                            Perbarui Produk
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i>
                            Batal
                        </a>
                    </div>
                </form>
            </div>

            <?php include '../views/'.$THEME.'/lower_block.php'; ?>
        </main>
    </div>
</div>

<script>
// Prevent double submit
document.getElementById('productForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn.disabled) {
        e.preventDefault();
        return false;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memperbarui...';
});
</script>

<?php include '../views/'.$THEME.'/footer.php'; ?>