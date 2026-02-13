<?php
session_start();
session_destroy();
header("Location: index.php"); // Redirect ke home setelah logout
exit();
?>