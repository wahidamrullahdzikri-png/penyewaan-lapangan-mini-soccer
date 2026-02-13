<?php

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function showAlert($message, $type = 'danger') {
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
             $safeMessage
             <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function base_url($path = '') {
    $url = BASE_URL . '/' . $path;
    return $url;
}

function generateNumericBookId() {
    // Format nomor booking: BOOKYYYYMMDDHHMMSSXXXX
    return 'BOOK' . date('YmdHis') . mt_rand(1000, 9999);
}

// Definisikan UPLOAD_DIR_LAPANGAN sebagai path absolut ke folder uploads di root project
define('UPLOAD_DIR_LAPANGAN', __DIR__ . '/../uploads/'); // __DIR__ adalah direktori lib/, .. naik satu level ke root

function handle_file_upload($file) {
    error_log("handle_file_upload called.");

    // Check if file was uploaded
    if (!isset($file['name']) || empty($file['name'])) { // Perbaikan: Tambahkan kurung tutup
        error_log("No file provided or empty name.");
        return ''; // No file uploaded
    }

    // Tambahkan pengecekan error upload PHP
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'File melebihi upload_max_filesize di php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'File melebihi MAX_FILE_SIZE yang ditentukan di form HTML.',
            UPLOAD_ERR_PARTIAL    => 'File hanya diupload sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diupload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload file dihentikan oleh ekstensi PHP.',
        ];
        $error_message = $upload_errors[$file['error']] ?? 'Error upload file yang tidak diketahui.';
        error_log("File upload error: " . $error_message);
        return false; // Upload failed due to PHP error
    }

    $target_dir = UPLOAD_DIR_LAPANGAN; // Sekarang ini adalah path absolut
    error_log("Target directory: " . $target_dir);

    // Create upload directory if not exists
    if (!file_exists($target_dir)) {
        error_log("Upload directory does not exist, attempting to create: " . $target_dir);
        if (!mkdir($target_dir, 0777, true)) {
            error_log("Failed to create upload directory: " . $target_dir);
            return false;
        }
        error_log("Successfully created upload directory: " . $target_dir);
    }

    // Generate unique filename
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    error_log("File extension: " . $file_extension);
    $filename = 'lapangan_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
    $target_file = $target_dir . $filename; // Path absolut ke file
    error_log("Generated target filename: " . $target_file);

    // Check file size (max 2MB) - Ini adalah batas aplikasi, bukan php.ini
    if ($file['size'] > 2097152) {
        error_log("File size exceeds application limit (2MB): " . $file['size'] . " bytes.");
        return false; // File too large according to app rules
    }

    // Allow certain file formats
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($file_extension, $allowed_types)) {
        error_log("File type not allowed: " . $file_extension);
        return false; // Invalid file type
    }

    // Check if file is actually an image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        error_log("File is not a valid image.");
        return false; // Not an image
    }

    // Upload file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        error_log("File successfully uploaded to: " . $target_file);
        return $filename; // Kembalikan nama file saja, bukan path
    } else {
        error_log("move_uploaded_file failed. Target: " . $target_file . ", Error: " . error_get_last()['message']);
    }

    return false;
}


//--- CSRF Functions---
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function validatePassword($password, $enabled = true) {
    // If validation is disabled, always return valid
    if (!$enabled) {
        return []; // Always valid when disabled
    }

    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    return $errors; // empty array = valid
}

// Fungsi untuk mengecek apakah user memiliki akses ke modul tertentu (untuk admin)
function userCanAccess($allowedRoles = ['admin']) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $userRole = $_SESSION['role'] ?? '';
    return in_array($userRole, $allowedRoles);
}

// Fungsi untuk menampilkan halaman akses ditolak (untuk admin)
function showAccessDenied($allowedRoles = ['admin']) {
    $roleLabels = getRoleLabels();
    $allowedLabels = array_map(fn($r) => $roleLabels[$r] ?? $r, $allowedRoles);
    $allowedText = implode(' atau ', $allowedLabels);

    $THEME = THEME_NAME; // Ambil tema dari konstanta

    include __DIR__ . '/../views/' . $THEME . '/header.php';
    include __DIR__ . '/../views/' . $THEME . '/topnav.php';
?>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../views/' . $THEME . '/sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="alert alert-danger">
                    <h4>🔒 Akses Ditolak</h4>
                    <p> Halaman ini hanya dapat diakses oleh: <strong><?= htmlspecialchars($allowedText) ?></strong>.</p>
                    <p> Anda login sebagai <strong><?= htmlspecialchars(getRoleLabel($_SESSION['role'] ?? 'user')) ?></strong>.</p>
                    <a href="../admin/index.php" class="btn btn-primary"> Kembali ke Dashboard </a>
                </div>
            </main>
        </div>
    </div>
<?php
    include __DIR__ . '/../views/' . $THEME . '/footer.php';
    exit();
}

// Fungsi untuk memerlukan akses ke modul admin
function requireModuleAccess($moduleName) {
    $menuConfig = loadMenuConfig();
    $moduleConfig = $menuConfig['modules'][$moduleName] ?? null;

    if (!$moduleConfig) {
        showAccessDenied(['admin']); // Default deny jika modul tidak dikonfigurasi
        return;
    }

    $allowedRoles = $moduleConfig['allowed_roles'] ?? ['admin'];

    if (!userCanAccess($allowedRoles)) {
        showAccessDenied($allowedRoles);
    }
}

function loadMenuConfig() {
    $configFile = __DIR__ . '/../config/menu.json';

    if (file_exists($configFile)) {
        $jsonContent = file_get_contents($configFile);
        return json_decode($jsonContent, true) ?: [];
    }
    return [];
}

function getRoleLabel($role) {
    $menuConfig = loadMenuConfig();
    return $menuConfig['roles'][$role]['label'] ?? $role;
}

function getRoleLabels() {
    $menuConfig = loadMenuConfig();
    $labels = [];

    foreach ($menuConfig['roles'] as $role => $config) {
        $labels[$role] = $config['label'];
    }

    return $labels;
}

// --- Fungsi Baru untuk Halaman Publik ---
function isAdminLoggedIn() {
    return isLoggedIn() && getUserRole() === 'admin';
}

function getAvailableFields($conn) {
    $query = "SELECT * FROM lapangan WHERE status_lapangan = 'Tersedia' ORDER BY nama_lapangan ASC"; // Tampilkan lapangan tersedia
    $result = mysqli_query($conn, $query);
    $fields = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $fields[] = $row;
        }
    }
    return $fields;
}

// --- Fungsi Baru untuk Form Generator (Dropdown) ---
/**
 * Generates an HTML <select> element populated with data from a database table.
 *
 * @param string $table_name The name of the database table to fetch data from.
 * @param string $value_field The column name to use as the option's value attribute.
 * @param string $label_field The column name to use as the option's displayed text.
 * @param string $selected_value The value that should be pre-selected in the dropdown.
 * @param string $name The name attribute of the <select> element.
 * @param string $placeholder Optional placeholder text for the first option (value will be empty).
 * @param string $order_by Optional column name to order the results by.
 * @param string $where_clause Optional WHERE clause to filter the results (e.g., "status = 'active'").
 * @return string The HTML string for the <select> element.
 */
function dropdownFromTable(
    $table_name,
    $value_field,
    $label_field,
    $selected_value = '',
    $name = '',
    $placeholder = '',
    $order_by = '',
    $where_clause = ''
) {
    global $connection; // Use the global database connection

    $sql = "SELECT `$value_field`, `$label_field` FROM `$table_name`";

    // Add WHERE clause if provided
    if ($where_clause) {
        $sql .= " WHERE $where_clause";
    }

    // Add ORDER BY clause if provided
    if ($order_by) {
        $sql .= " ORDER BY `$order_by` ASC"; // Default to ascending order
    }

    $options_html = '';

    if ($placeholder !== '') {
        $selected_attr = ($selected_value === '') ? ' selected' : '';
        $options_html .= '<option value=""' . $selected_attr . '>' . htmlspecialchars($placeholder) . '</option>';
    }

    $result = mysqli_query($connection, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $value = $row[$value_field];
            $label = $row[$label_field];
            $selected_attr = ($value == $selected_value) ? ' selected' : ''; // Use loose comparison for simplicity
            $options_html .= '<option value="' . htmlspecialchars($value) . '"' . $selected_attr . '>' . htmlspecialchars($label) . '</option>';
        }
        mysqli_free_result($result);
    } else {
        // Optionally log the error or handle it gracefully
        error_log("Dropdown query failed: " . mysqli_error($connection));
        $options_html .= '<option value="">Error loading options</option>';
    }

    $select_html = '<select name="' . htmlspecialchars($name) . '" class="form-control">';
    $select_html .= $options_html;
    $select_html .= '</select>';

    return $select_html;
}

// --- Fungsi Baru untuk Deteksi Bentrok Jadwal (Tanpa Endpoint) ---
function isSlotAvailable($conn, $fieldId, $date, $startTime, $endTime) {
    // Query untuk memeriksa apakah ada booking yang *menumpang* dengan slot baru
    // Logika: Tidak ada bentrok jika
    // (slot_baru_selesai <= slot_lama_mulai) OR (slot_baru_mulai >= slot_lama_selesai)
    // Secara logika negasi (untuk pencarian bentrok):
    // NOT ((new_end <= old_start) OR (new_start >= old_end))
    // = (new_end > old_start) AND (new_start < old_end)

    // Kita cari booking yang bentrok
    $sql = "
        SELECT COUNT(*) as count
        FROM booking_detail bd
        JOIN booking b ON bd.booking_id = b.id
        JOIN lapangan l ON bd.lapangan_id = l.id
        WHERE l.id = ?
        AND b.tanggal_main = ?
        AND (
            (? < b.jam_selesai) AND (? > b.jam_mulai) -- Bentrok jika start baru < end lama DAN end baru > start lama
        )
        AND b.status_pembayaran != 'LUNAS'
    ";

    $stmt = mysqli_prepare($conn, $sql);
    // Bind parameter: fieldId, date, startTime, endTime
    // Urutan dalam query: WHERE l.id = ? (1-i) AND b.tanggal_main = ? (2-s) AND ( ( ? (3-s) < b.jam_selesai) AND ( ? (4-s) > b.jam_mulai) )
    // Parameter binding: i, s, s, s
    mysqli_stmt_bind_param($stmt, "isss", $fieldId, $date, $startTime, $endTime);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    // Jika count > 0, berarti ada bentrok -> return false
    // Jika count == 0, berarti tidak ada bentrok -> return true
    return $row['count'] == 0;
}

// --- END Fungsi Dropdown ---
?>