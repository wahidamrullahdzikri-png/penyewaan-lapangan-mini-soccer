<?php
session_start();
require_once '../lib/functions.php';
require_once '../lib/auth.php';

requireAuth();
requireModuleAccess('booking');
require_once '../config/database.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id) {
    $stmt = mysqli_prepare($connection, "UPDATE booking SET status_pembayaran = 'LUNAS' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
redirect('index.php');
?>