<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('lapangan');
require_once '../config/database.php';

$error = $success = '';
$foto_filename = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_lapangan = trim($_POST['kode_lapangan'] ?? '');
    $nama_lapangan = trim($_POST['nama_lapangan'] ?? '');
    $harga_per_jam = trim($_POST['harga_per_jam'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status_lapangan = $_POST['status_lapangan'] ?? 'Tersedia';

    if (empty($kode_lapangan) || empty($nama_lapangan) || empty($harga_per_jam)) {
        $error = "Kode Lapangan, Nama Lapangan, dan Harga per Jam wajib diisi.";
    } else {
        $foto_filename = 'default.jpg';
        
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = handle_file_upload($_FILES['foto']);

            if ($upload_result === false) {
                $error = "Gagal mengupload foto. Pastikan file berupa gambar (JPG, PNG, GIF, WebP) dan ukuran maksimal 2MB.";
            } elseif ($upload_result !== '') {
                $foto_filename = $upload_result;
            }
        }

        if (!$error) {
            $stmt = mysqli_prepare($connection, "INSERT INTO lapangan (kode_lapangan, nama_lapangan, harga_per_jam, deskripsi, foto, status_lapangan) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isssss", $kode_lapangan, $nama_lapangan, $harga_per_jam, $deskripsi, $foto_filename, $status_lapangan);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Lapangan berhasil ditambahkan.";
            } else {
                $error = "Gagal menyimpan data: " . mysqli_error($connection);
                if ($foto_filename !== 'default.jpg' && file_exists('../uploads/' . $foto_filename)) {
                    unlink('../uploads/' . $foto_filename);
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<?php include '../views/' . THEME_NAME . '/header.php'; ?>
<link rel="stylesheet" href="../assets/css/lapangan-style.css">
<?php include '../views/' . THEME_NAME . '/topnav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../views/' . THEME_NAME . '/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Page Header -->
            <div class="mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Lapangan Baru
                </h2>
                <p class="text-muted mt-2 mb-0">Lengkapi form berikut untuk menambahkan lapangan baru</p>
            </div>

            <!-- Alert Messages -->
            <?php if ($error) : ?>
                <?= showAlert($error, 'danger') ?>
            <?php endif; ?>
            
            <?php if ($success) : ?>
                <?= showAlert($success, 'success') ?>
                <div class="d-flex gap-2 mb-4">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-list-ul"></i> Lihat Daftar Lapangan
                    </a>
                    <a href="add.php" class="btn btn-secondary">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Lagi
                    </a>
                </div>
            <?php else : ?>
                <!-- Form Content -->
                <div class="content-wrapper">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        Kode Lapangan <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           name="kode_lapangan" 
                                           class="form-control" 
                                           placeholder="Contoh: 1"
                                           required>
                                    <small class="form-text">Masukkan kode unik untuk lapangan</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">
                                        Nama Lapangan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           name="nama_lapangan" 
                                           class="form-control" 
                                           placeholder="Contoh: Lapangan A - Premium"
                                           required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">
                                        Harga per Jam <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" 
                                               name="harga_per_jam" 
                                               class="form-control" 
                                               placeholder="250000"
                                               min="0"
                                               required>
                                    </div>
                                    <small class="form-text">Tarif sewa per jam dalam Rupiah</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Status Lapangan</label>
                                    <select name="status_lapangan" class="form-select">
                                        <option value="Tersedia">Tersedia</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Non-Aktif">Non-Aktif</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" 
                                              class="form-control" 
                                              rows="5"
                                              placeholder="Deskripsikan fasilitas dan spesifikasi lapangan..."></textarea>
                                    <small class="form-text">Informasi detail tentang lapangan</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Foto Lapangan</label>
                                    <input type="file" 
                                           name="foto" 
                                           id="fotoInput" 
                                           class="form-control" 
                                           accept="image/*" 
                                           onchange="previewImage(event)">
                                    
                                    <div class="image-preview-container" id="previewContainer" style="display: none;">
                                        <p class="mb-2 fw-semibold" style="color: var(--primary-green);">
                                            <i class="bi bi-check-circle-fill"></i> Preview Foto:
                                        </p>
                                        <img id="imagePreview" 
                                             src="#" 
                                             alt="Preview Gambar">
                                    </div>
                                    
                                    <small class="form-text">
                                        <i class="bi bi-info-circle"></i> 
                                        Format: JPG, PNG, GIF, WebP | Maksimal: 2MB
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save-fill"></i> Simpan Lapangan
                            </button>
                            <a href="index.php" class="btn btn-secondary px-4">
                                <i class="bi bi-x-circle-fill"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
function previewImage(event) {
    const reader = new FileReader();
    const preview = document.getElementById('imagePreview');
    const container = document.getElementById('previewContainer');

    reader.onload = function() {
        preview.src = reader.result;
        container.style.display = 'block';
    }

    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    } else {
        container.style.display = 'none';
        preview.src = '#';
    }
}

document.querySelector('form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('fotoInput');
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size / 1024 / 1024;
        if (fileSize > 2) {
            e.preventDefault();
            alert('❌ Ukuran file terlalu besar! Maksimal 2MB.');
            return false;
        }
    }
});
</script>

<?php include '../views/' . THEME_NAME . '/footer.php'; ?>