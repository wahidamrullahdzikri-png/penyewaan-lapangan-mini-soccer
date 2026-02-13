<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('lapangan');
require_once '../config/database.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) redirect('index.php');

// Fetch current data
$stmt = mysqli_prepare($connection, "SELECT * FROM lapangan WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lapangan = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$lapangan) {
    redirect('index.php');
}

$error = $success = '';
$foto_filename = $lapangan['foto'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_lapangan = trim($_POST['kode_lapangan'] ?? '');
    $nama_lapangan = trim($_POST['nama_lapangan'] ?? '');
    $harga_per_jam = trim($_POST['harga_per_jam'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status_lapangan = $_POST['status_lapangan'] ?? 'Tersedia';
    $hapus_foto = isset($_POST['hapus_foto']) ? (int) $_POST['hapus_foto'] : 0;

    if (empty($kode_lapangan) || empty($nama_lapangan) || empty($harga_per_jam)) {
        $error = "Kode Lapangan, Nama Lapangan, dan Harga per Jam wajib diisi.";
    } else {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result = handle_file_upload($_FILES['foto']);

            if ($upload_result === false) {
                $error = "Gagal mengupload foto. Pastikan file adalah gambar (JPG, PNG, GIF, WebP) dan ukuran maksimal 2MB.";
            } elseif ($upload_result !== '') {
                if ($foto_filename && $foto_filename !== 'default.jpg' && file_exists('../uploads/' . $foto_filename)) {
                    unlink('../uploads/' . $foto_filename);
                }
                $foto_filename = $upload_result;
            }
        } elseif ($hapus_foto) {
            if ($foto_filename && $foto_filename !== 'default.jpg' && file_exists('../uploads/' . $foto_filename)) {
                unlink('../uploads/' . $foto_filename);
            }
            $foto_filename = 'default.jpg';
        }

        if (!$error) {
            $stmt = mysqli_prepare($connection, "UPDATE lapangan SET kode_lapangan=?, nama_lapangan=?, harga_per_jam=?, deskripsi=?, foto=?, status_lapangan=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "isssssi", $kode_lapangan, $nama_lapangan, $harga_per_jam, $deskripsi, $foto_filename, $status_lapangan, $id);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Lapangan berhasil diperbarui.";
                mysqli_stmt_close($stmt);
                $stmt = mysqli_prepare($connection, "SELECT * FROM lapangan WHERE id=?");
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $lapangan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                $foto_filename = $lapangan['foto'];
            } else {
                $error = "Gagal memperbarui: " . mysqli_error($connection);
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
                    <i class="bi bi-pencil-square"></i> Edit Lapangan
                </h2>
                <p class="text-muted mt-2 mb-0">
                    Perbarui informasi untuk <strong class="text-success"><?= htmlspecialchars($lapangan['nama_lapangan']) ?></strong>
                </p>
            </div>

            <!-- Alert Messages -->
            <?php if ($error) : ?>
                <?= showAlert($error, 'danger') ?>
            <?php endif; ?>
            
            <?php if ($success) : ?>
                <?= showAlert($success, 'success') ?>
            <?php endif; ?>

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
                                       value="<?= htmlspecialchars($lapangan['kode_lapangan']) ?>" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    Nama Lapangan <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="nama_lapangan" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($lapangan['nama_lapangan']) ?>" 
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
                                           value="<?= htmlspecialchars($lapangan['harga_per_jam']) ?>" 
                                           min="0"
                                           required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Status Lapangan</label>
                                <select name="status_lapangan" class="form-select">
                                    <option value="Tersedia" <?= $lapangan['status_lapangan'] === 'Tersedia' ? 'selected' : '' ?>>
                                        Tersedia
                                    </option>
                                    <option value="Maintenance" <?= $lapangan['status_lapangan'] === 'Maintenance' ? 'selected' : '' ?>>
                                        Maintenance
                                    </option>
                                    <option value="Non-Aktif" <?= $lapangan['status_lapangan'] === 'Non-Aktif' ? 'selected' : '' ?>>
                                        Non-Aktif
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" 
                                          class="form-control" 
                                          rows="5"><?= htmlspecialchars($lapangan['deskripsi']) ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Foto Lapangan</label>
                                
                                <?php if ($foto_filename && file_exists('../uploads/' . $foto_filename)) : ?>
                                    <div class="image-preview-container mb-3">
                                        <p class="mb-2 fw-semibold">
                                            <i class="bi bi-image"></i> Foto Saat Ini:
                                        </p>
                                        <img src="../uploads/<?= $foto_filename ?>" 
                                             alt="Foto Lapangan Saat Ini">
                                        
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="hapus_foto" 
                                                   id="hapus_foto" 
                                                   value="1">
                                            <label class="form-check-label text-danger" for="hapus_foto">
                                                <i class="bi bi-trash"></i> Hapus foto dan gunakan default
                                            </label>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <input type="file" 
                                       name="foto" 
                                       id="fotoInput" 
                                       class="form-control" 
                                       accept="image/*" 
                                       onchange="previewImage(event)">
                                
                                <div class="image-preview-container mt-3" id="previewContainer" style="display: none;">
                                    <p class="mb-2 fw-semibold" style="color: var(--primary-green);">
                                        <i class="bi bi-check-circle-fill"></i> Preview Foto Baru:
                                    </p>
                                    <img id="imagePreview" src="#" alt="Preview Gambar Baru">
                                </div>
                                
                                <small class="form-text">
                                    <i class="bi bi-info-circle"></i> 
                                    Format: JPG, PNG, GIF, WebP | Maksimal: 2MB | Kosongkan jika tidak ingin mengganti
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <hr class="my-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save-fill"></i> Perbarui Lapangan
                        </button>
                        <a href="index.php" class="btn btn-secondary px-4">
                            <i class="bi bi-x-circle-fill"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
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