<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Authenticates admin user and sets session data.
 * Assumes session_start() was called BEFORE this function.
 */
function login($username, $password) {
    global $connection;
    $username = mysqli_real_escape_string($connection, sanitize($username));
    $sql = "SELECT id, username, password, role FROM users WHERE username=? AND role='admin'";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        // DO NOT call session_start() here!
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        mysqli_stmt_close($stmt);
        return $user['role'];
    }
    mysqli_stmt_close($stmt);
    return false; // Login gagal
}

function requireAuth() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        redirect('login.php');
    }
}

function redirectBasedOnRole($role) {
    if ($role === 'admin') {
        header('Location: admin/index.php');
        exit();
    } else {
        header('Location: login.php'); // Redirect ke login jika bukan admin
        exit();
    }
}
?>