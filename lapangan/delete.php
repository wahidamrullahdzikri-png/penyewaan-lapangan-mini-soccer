<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('lapangan');
require_once '../config/database.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id) {
    // Ambil data lapangan sebelum menghapus
    $stmt = mysqli_prepare($connection, "SELECT nama_lapangan, foto FROM lapangan WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $lapangan = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($lapangan) {
        $nama_lapangan = $lapangan['nama_lapangan'];
        $foto_nama = $lapangan['foto'];

        // Hapus record dari database
        $stmt = mysqli_prepare($connection, "DELETE FROM lapangan WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            // Hapus file foto jika bukan default
            if ($foto_nama && $foto_nama !== 'default.jpg' && file_exists('../uploads/' . $foto_nama)) {
                unlink('../uploads/' . $foto_nama);
            }

            // Set success message
            $_SESSION['success_message'] = "Lapangan '<strong>" . htmlspecialchars($nama_lapangan) . "</strong>' berhasil dihapus.";
        } else {
            mysqli_stmt_close($stmt);
            // Set error message
            $_SESSION['error_message'] = "Gagal menghapus lapangan. Silakan coba lagi.";
        }
    } else {
        $_SESSION['error_message'] = "Data lapangan tidak ditemukan.";
    }
} else {
    $_SESSION['error_message'] = "ID lapangan tidak valid.";
}

redirect('index.php');
?>