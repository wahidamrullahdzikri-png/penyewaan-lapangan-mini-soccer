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
$master_id = (int)($_GET['booking_id'] ?? 0);
if (!$master_id) redirect('index.php');

$error = '';
$item_type = $_POST['item_type'] ?? ($_GET['type'] ?? '');
$item_id = $_POST['item_id'] ?? '';
$jumlah = (int)($_POST['jumlah'] ?? 1);

// Ambil data item berdasarkan tipe
$items = [];
if ($item_type === 'lapangan') {
    $result = mysqli_query($connection, "SELECT id, nama_lapangan as name, kode_lapangan as kode, harga_per_jam as harga FROM lapangan WHERE status_lapangan = 'Tersedia' ORDER BY kode_lapangan");
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
} elseif ($item_type === 'produk') {
    $result = mysqli_query($connection, "SELECT id, nama_produk as name, kode_produk as kode, harga FROM produk WHERE status_produk = 'Aktif' ORDER BY kode_produk");
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) die('Invalid CSRF token.');

    $is_add_request = !empty($_POST['item_type']) && !empty($_POST['item_id']) && isset($_POST['jumlah']) && (int)$_POST['jumlah'] > 0;

    if ($is_add_request) {
        $posted_item_type = trim($_POST['item_type'] ?? '');
        $posted_item_id = trim($_POST['item_id'] ?? '');
        $posted_jumlah = (int)($_POST['jumlah'] ?? 1);

        if (empty($posted_item_type) || empty($posted_item_id) || $posted_jumlah <= 0) {
            $error = "Jenis Item, Item, dan Jumlah wajib diisi dan jumlah harus lebih dari 0.";
        } else {
            $harga = 0;
            if ($posted_item_type === 'lapangan') {
                $stmt_price = mysqli_prepare($connection, "SELECT harga_per_jam FROM `lapangan` WHERE id = ?");
            } elseif ($posted_item_type === 'produk') {
                $stmt_price = mysqli_prepare($connection, "SELECT harga FROM `produk` WHERE id = ?");
            } else {
                $error = "Jenis item tidak valid.";
            }

            if (!$error) {
                mysqli_stmt_bind_param($stmt_price, "i", $posted_item_id);
                mysqli_stmt_execute($stmt_price);
                $result_price = mysqli_stmt_get_result($stmt_price);
                $item_data = mysqli_fetch_assoc($result_price);
                mysqli_stmt_close($stmt_price);

                if ($item_data) {
                    $harga = (float)$item_data[$posted_item_type === 'lapangan' ? 'harga_per_jam' : 'harga'];
                    $subtotal_harga = $posted_jumlah * $harga;
                } else {
                    $error = "Data item tidak ditemukan.";
                }
            }

            if (!$error) {
                $stmt = mysqli_prepare($connection, "INSERT INTO `booking_detail` (`item_id`, `item_type`, `jumlah`, `subtotal_harga`, `booking_id`) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "isidi", $posted_item_id, $posted_item_type, $posted_jumlah, $subtotal_harga, $master_id);

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);

                    $total = mysqli_query($connection, "SELECT SUM(`subtotal_harga`) as total FROM `booking_detail` WHERE `booking_id` = $master_id");
                    $total_count = mysqli_fetch_assoc($total)['total'] ?? 0;
                    mysqli_query($connection, "UPDATE `booking` SET `total_harga` = $total_count WHERE id = $master_id");
                    redirect(dirname($_SERVER['SCRIPT_NAME']) . "/detail.php?id=$master_id");
                    exit();
                } else {
                    $error = "Gagal menyimpan item: " . mysqli_error($connection);
                }
            }
        }
    }
}
$csrfToken = generateCSRFToken();
?>

<?php include '../views/'.$THEME.'/header.php'; ?>
<?php include '../views/'.$THEME.'/topnav.php'; ?>

<style>
    /* Sport Theme Colors */
    :root {
        --primary-green: #28a745;
        --dark-green: #20c997;
        --light-green: #d1fae5;
        --accent-orange: #f59e0b;
        --dark-bg: #1f2937;
        --light-bg: #f9fafb;
    }

    /* Page Header */
    .page-header-add {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .page-header-add h2 {
        margin: 0;
        font-weight: 700;
        font-size: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-header-add p {
        margin: 5px 0 0 0;
        opacity: 0.9;
        font-size: 14px;
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 30px;
        margin-bottom: 25px;
    }

    .form-card-header {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--primary-green);
    }

    .form-card-header h5 {
        margin: 0;
        color: var(--dark-bg);
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Form Groups */
    .form-group-custom {
        margin-bottom: 25px;
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
        color: var(--primary-green);
        font-size: 16px;
    }

    /* Form Controls */
    .form-select,
    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 15px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
        outline: none;
    }

    .form-select:disabled {
        background-color: #f3f4f6;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Info Box */
    .info-box {
        background: linear-gradient(135deg, var(--light-green) 0%, #e0f2fe 100%);
        border-left: 4px solid var(--primary-green);
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: start;
        gap: 12px;
    }

    .info-box i {
        color: var(--primary-green);
        font-size: 24px;
        margin-top: 2px;
    }

    .info-box-content h6 {
        margin: 0 0 5px 0;
        color: var(--dark-bg);
        font-weight: 700;
        font-size: 14px;
    }

    .info-box-content p {
        margin: 0;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.5;
    }

    /* Buttons */
    .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 12px 24px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--dark-green) 0%, var(--primary-green) 100%);
    }

    .btn-secondary {
        background: #6b7280;
        border: none;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        padding-top: 20px;
        border-top: 2px dashed #e5e7eb;
        margin-top: 20px;
    }

    /* Alert Enhancement */
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

    .alert i {
        font-size: 24px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-card {
            padding: 20px;
        }
        
        .page-header-add {
            padding: 20px;
        }
        
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
            <div class="page-header-add">
                <h2>
                    <i class="bi bi-plus-circle"></i>
                    Tambah Item ke Booking
                </h2>
                <p><i class="bi bi-info-circle"></i> Tambahkan lapangan atau produk tambahan ke booking ini</p>
            </div>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Oops! Ada kesalahan</strong>
                        <p class="mb-0 mt-1"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Info Box -->
            <div class="info-box">
                <i class="bi bi-lightbulb-fill"></i>
                <div class="info-box-content">
                    <h6>Cara Menambahkan Item</h6>
                    <p>1. Pilih jenis item (Lapangan atau Produk)<br>
                       2. Pilih item yang diinginkan dari dropdown<br>
                       3. Masukkan jumlah/qty<br>
                       4. Klik "Tambah Item" untuk menyimpan</p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <div class="form-card-header">
                    <h5>
                        <i class="bi bi-card-checklist"></i>
                        Formulir Tambah Item
                    </h5>
                </div>

                <form method="POST" id="addItemForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="booking_id" value="<?= $master_id ?>">

                    <!-- Jenis Item -->
                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="bi bi-tag"></i>
                            Jenis Item
                            <span class="required">*</span>
                        </label>
                        <select name="item_type" id="item_type_select" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Pilih Jenis Item --</option>
                            <option value="lapangan" <?= $item_type === 'lapangan' ? 'selected' : '' ?>>
                                🏟️ Lapangan Mini Soccer
                            </option>
                            <option value="produk" <?= $item_type === 'produk' ? 'selected' : '' ?>>
                                🛍️ Produk (Minuman, Snack, Alat, dll)
                            </option>
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-info-circle"></i> Pilih jenis item terlebih dahulu
                        </small>
                    </div>

                    <!-- Item Dropdown -->
                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="bi bi-box-seam"></i>
                            <?= $item_type === 'lapangan' ? 'Pilih Lapangan' : 'Pilih Produk' ?>
                            <span class="required">*</span>
                        </label>
                        <select name="item_id" class="form-select" required <?= empty($item_type) ? 'disabled' : '' ?>>
                            <option value="">-- Pilih Item --</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?= $item['id'] ?>" <?= $item['id'] == $item_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($item['name']) ?> 
                                    (<?= htmlspecialchars($item['kode']) ?>) - 
                                    <strong>Rp <?= number_format($item['harga'], 0, ',', '.') ?></strong>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($item_type)): ?>
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-exclamation-circle"></i> Pilih jenis item terlebih dahulu untuk mengaktifkan dropdown ini
                            </small>
                        <?php else: ?>
                            <small class="text-muted mt-1 d-block">
                                <i class="bi bi-info-circle"></i> 
                                <?= count($items) ?> item tersedia untuk dipilih
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Jumlah/Qty -->
                    <div class="form-group-custom">
                        <label class="form-label-custom">
                            <i class="bi bi-123"></i>
                            <?= $item_type === 'lapangan' ? 'Lama Sewa (Jam)' : 'Jumlah/Qty' ?>
                            <span class="required">*</span>
                        </label>
                        <input type="number" 
                               name="jumlah" 
                               class="form-control" 
                               value="<?= $jumlah ?>" 
                               min="1" 
                               required
                               placeholder="<?= $item_type === 'lapangan' ? 'Contoh: 2 (jam)' : 'Contoh: 5 (pcs)' ?>">
                        <small class="text-muted mt-1 d-block">
                            <i class="bi bi-info-circle"></i> 
                            <?= $item_type === 'lapangan' ? 'Masukkan durasi sewa dalam jam' : 'Masukkan jumlah produk yang ingin ditambahkan' ?>
                        </small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" <?= empty($item_type) ? 'disabled' : '' ?>>
                            <i class="bi bi-check-circle"></i>
                            Tambah Item
                        </button>
                        <a href="detail.php?id=<?= $master_id ?>" class="btn btn-secondary">
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
document.getElementById('addItemForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn.disabled) {
        e.preventDefault();
        return false;
    }
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
});
</script>

<?php include '../views/'.$THEME.'/footer.php'; ?>