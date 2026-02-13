<?php
require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('Asia/Jakarta'); // Atur zona waktu jika diperlukan

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Now access vars via $_ENV or getenv()
$DB_HOST = $_ENV['DB_HOST'];
$DB_PORT = (int) ($_ENV['DB_PORT']);
$DB_NAME = $_ENV['DB_NAME'];
$DB_USER = $_ENV['DB_USER'];
$DB_PASS = $_ENV['DB_PASS'] ?? ''; // Sesuaikan dengan nama variabel di .env
$BASE_PATH = $_ENV['BASE_PATH'] ?? '';
$THEME = $_ENV['THEME'] ?? 'default'; // Ambil tema dari .env

$connection = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Definisikan BASE_URL berdasarkan BASE_PATH
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . $BASE_PATH);
define('THEME_NAME', $THEME); // Konstanta untuk tema aktif

?>