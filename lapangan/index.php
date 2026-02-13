<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('lapangan');
require_once '../config/database.php';

$result = mysqli_query($connection, "SELECT * FROM lapangan ORDER BY id DESC");
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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">
                            <i class="bi bi-trophy-fill"></i> Daftar Lapangan
                        </h2>
                        <p class="text-muted mt-2 mb-0">Kelola data lapangan mini soccer Anda</p>
                    </div>
                    <a href="add.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle-fill"></i> Tambah Lapangan
                    </a>
                </div>
            </div>

            <!-- Alert Messages from Session -->
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])) : ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Table Content -->
            <?php if (mysqli_num_rows($result) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 5%;" class="text-center">ID</th>
                                <th style="width: 8%;" class="text-center">Kode</th>
                                <th style="width: 17%;">Nama Lapangan</th>
                                <th style="width: 12%;">Harga/Jam</th>
                                <th style="width: 30%;">Deskripsi</th>
                                <th style="width: 10%;" class="text-center">Foto</th>
                                <th style="width: 10%;" class="text-center">Status</th>
                                <th style="width: 8%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr>
                                    <td class="fw-bold text-center"><?= $row['id'] ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-dark"><?= htmlspecialchars($row['kode_lapangan']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_lapangan']) ?></div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">Rp <?= number_format($row['harga_per_jam'], 0, ',', '.') ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted" style="font-size: 0.9rem; line-height: 1.4;">
                                            <?= htmlspecialchars(substr($row['deskripsi'], 0, 120)) ?>
                                            <?= strlen($row['deskripsi']) > 120 ? '...' : '' ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $foto_path_abs = __DIR__ . '/../uploads/' . $row['foto'];
                                        $foto_path_rel = '../uploads/' . $row['foto'];
                                        $show_default = !($row['foto'] && file_exists($foto_path_abs));
                                        $image_src = $show_default ? '../uploads/default.jpg' : $foto_path_rel;
                                        ?>
                                        <img src="<?= $image_src ?>" 
                                             alt="Foto Lapangan" 
                                             class="img-thumbnail"
                                             style="max-width: 90px; height: 65px; object-fit: cover;">
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $status_class = match($row['status_lapangan']) {
                                            'Tersedia' => 'bg-success',
                                            'Maintenance' => 'bg-warning text-dark',
                                            'Non-Aktif' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $status_class ?> px-3 py-2">
                                            <?= htmlspecialchars($row['status_lapangan']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="edit.php?id=<?= $row['id'] ?>" 
                                               class="btn btn-sm btn-warning px-2"
                                               title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger px-2"
                                                    onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nama_lapangan'], ENT_QUOTES) ?>')"
                                                    title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <div class="alert alert-info text-center py-4">
                    <i class="bi bi-info-circle-fill fs-3"></i>
                    <p class="mb-0 mt-2">Belum ada data lapangan. Klik tombol <strong>"Tambah Lapangan"</strong> untuk memulai.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
function confirmDelete(id, namaLapangan) {
    if (confirm('⚠️ Yakin ingin menghapus lapangan "' + namaLapangan + '"?\n\nData yang dihapus tidak dapat dikembalikan!')) {
        window.location.href = 'delete.php?id=' + id;
    }
}
</script>

<?php include '../views/' . THEME_NAME . '/footer.php'; ?>