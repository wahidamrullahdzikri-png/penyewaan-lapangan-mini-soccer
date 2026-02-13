<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('produk');

require_once '../config/database.php';

$result = mysqli_query($connection, "SELECT * FROM `produk` ORDER BY id DESC");
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>

<style>
    :root {
        --primary-green: #28a745;
        --dark-green: #20c997;
        --light-green: #d1fae5;
        --dark-bg: #1f2937;
    }

    .page-header-produk {
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

    .page-header-produk h2 {
        margin: 0;
        font-weight: 700;
        font-size: 26px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stats-row {
        display: flex;
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card-small {
        flex: 1;
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 4px solid var(--primary-green);
    }

    .stat-card-small h6 {
        color: #6b7280;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        margin: 0 0 8px 0;
    }

    .stat-card-small .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--dark-bg);
    }

    .table-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 25px;
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
        white-space: nowrap;
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

    .product-name {
        font-weight: 600;
        color: var(--dark-bg);
    }

    .badge {
        padding: 6px 12px;
        font-weight: 600;
        font-size: 11px;
        border-radius: 6px;
    }

    .badge.bg-success {
        background: var(--primary-green) !important;
    }

    .badge-jenis {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 4px;
    }

    .badge-alat {
        background: #3b82f6;
        color: white;
    }

    .badge-minuman {
        background: #06b6d4;
        color: white;
    }

    .badge-snack {
        background: #f59e0b;
        color: white;
    }

    .badge-makanan {
        background: #ef4444;
        color: white;
    }

    .badge-lainnya {
        background: #6b7280;
        color: white;
    }

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
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        border: none;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .empty-state h5 {
        color: var(--dark-bg);
        font-weight: 700;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #9ca3af;
    }

    @media (max-width: 768px) {
        .stats-row {
            flex-direction: column;
        }
        
        .page-header-produk {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../views/'.$THEME.'/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <?php include '../views/'.$THEME.'/upper_block.php'; ?>

            <!-- Page Header -->
            <div class="page-header-produk">
                <h2>
                    <i class="bi bi-box-seam"></i>
                    Kelola Produk
                </h2>
                <a href="add.php" class="btn btn-light">
                    <i class="bi bi-plus-circle"></i>
                    Tambah Produk
                </a>
            </div>

            <!-- Statistics Row -->
            <div class="stats-row">
                <div class="stat-card-small">
                    <h6><i class="bi bi-box-seam"></i> Total Produk</h6>
                    <div class="stat-value"><?= mysqli_num_rows($result) ?></div>
                </div>
                <div class="stat-card-small">
                    <h6><i class="bi bi-check-circle"></i> Aktif</h6>
                    <div class="stat-value" style="color: var(--primary-green);">
                        <?php
                        mysqli_data_seek($result, 0);
                        $aktif = 0;
                        while ($row = mysqli_fetch_assoc($result)) {
                            if ($row['status_produk'] === 'Aktif') $aktif++;
                        }
                        echo $aktif;
                        mysqli_data_seek($result, 0);
                        ?>
                    </div>
                </div>
                <div class="stat-card-small">
                    <h6><i class="bi bi-x-circle"></i> Non-Aktif</h6>
                    <div class="stat-value" style="color: #6b7280;">
                        <?= mysqli_num_rows($result) - $aktif ?>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Kode</th>
                                    <th>Nama Produk</th>
                                    <th>Jenis</th>
                                    <th class="text-end">Harga</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#<?= $row['id'] ?></span>
                                        </td>
                                        <td>
                                            <code style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                                <?= htmlspecialchars($row['kode_produk']) ?>
                                            </code>
                                        </td>
                                        <td>
                                            <div class="product-name">
                                                <?= htmlspecialchars($row['nama_produk']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $jenis = $row['jenis_produk'];
                                            $badge_class = 'badge-lainnya';
                                            $icon = 'bi-tag';
                                            
                                            if ($jenis === 'Alat') {
                                                $badge_class = 'badge-alat';
                                                $icon = 'bi-tools';
                                            } elseif ($jenis === 'Minuman') {
                                                $badge_class = 'badge-minuman';
                                                $icon = 'bi-cup-straw';
                                            } elseif ($jenis === 'Snack') {
                                                $badge_class = 'badge-snack';
                                                $icon = 'bi-bag';
                                            } elseif ($jenis === 'Makanan') {
                                                $badge_class = 'badge-makanan';
                                                $icon = 'bi-egg-fried';
                                            }
                                            ?>
                                            <span class="badge-jenis <?= $badge_class ?>">
                                                <i class="bi <?= $icon ?>"></i>
                                                <?= htmlspecialchars($jenis) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <strong style="color: var(--primary-green);">
                                                Rp <?= number_format((float)$row['harga'], 0, ',', '.') ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php
                                                $desc = htmlspecialchars($row['deskripsi']);
                                                echo strlen($desc) > 50 ? substr($desc, 0, 50) . '...' : $desc;
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($row['status_produk'] === 'Aktif'): ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-x-circle"></i> Non-Aktif
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit.php?id=<?= $row['id'] ?>" 
                                                   class="btn btn-warning"
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $row['id'] ?>" 
                                                   class="btn btn-danger" 
                                                   onclick="return confirm('Yakin hapus produk \"<?= htmlspecialchars($row['nama_produk']) ?>\"?')"
                                                   title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h5>Belum Ada Produk</h5>
                        <p class="mb-3">Mulai tambahkan produk pertama Anda sekarang!</p>
                        <a href="add.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i>
                            Tambah Produk Pertama
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php include '../views/'.$THEME.'/lower_block.php'; ?>
        </main>
    </div>
</div>

<?php include '../views/'.$THEME.'/footer.php'; ?>