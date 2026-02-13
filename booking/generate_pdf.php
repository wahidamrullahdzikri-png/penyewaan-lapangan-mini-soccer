<?php
// booking/generate_pdf.php
require_once '../config/database.php';
require_once '../lib/functions.php';
require_once '../lib/fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die("ID Booking tidak ditemukan.");
}

$booking_id = (int)$_GET['id'];

// Ambil data booking
$query = "SELECT b.*, l.nama_lapangan 
          FROM booking b 
          JOIN booking_detail bd ON b.id = bd.booking_id 
          JOIN lapangan l ON bd.lapangan_id = l.id 
          WHERE b.id = ?";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data tidak ditemukan.");
}

// Inisialisasi FPDF (A5 Portrait)
$pdf = new FPDF('P', 'mm', 'A5');
$pdf->AddPage();

// --- HEADER ---
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'BUKTI BOOKING LAPANGAN', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Harap simpan bukti ini untuk verifikasi pembayaran', 0, 1, 'C');
$pdf->Ln(5);

// --- WAKTU CETAK (DITAMBAHKAN DISINI) ---
$pdf->SetFont('Arial', 'I', 8);
$waktu_cetak = date('d/m/Y H:i:s'); // Format: Tgl/Bln/Thn Jam:Menit:Detik
$pdf->Cell(0, 5, 'Dicetak pada: ' . $waktu_cetak, 0, 1, 'R');

$pdf->Line(10, 35, 138, 35); // Garis pembatas
$pdf->Ln(5);

// --- DETAIL DATA ---
$pdf->SetFont('Arial', '', 12);

$details = [
    'Nomor Booking'   => $data['nomor_booking'],
    'Nama Pemesan'    => $data['nama_pemesan'],
    'No. HP'          => $data['no_hp'],
    'Lapangan'        => $data['nama_lapangan'],
    'Tanggal Main'    => date('d-m-Y', strtotime($data['tanggal_main'])),
    'Waktu Bermain'   => $data['jam_mulai'] . ' - ' . $data['jam_selesai'],
    'Total Harga'     => 'Rp ' . number_format($data['total_harga'], 0, ',', '.'),
    'Status'          => $data['status_pembayaran']
];

foreach ($details as $label => $value) {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, $label, 0, 0);
    $pdf->Cell(5, 10, ':', 0, 0);
    
    $pdf->SetFont('Arial', 'B', 12);

    // 1. Bersihkan karakter dash khusus (panjang/medium) menjadi strip biasa (-)
    // Ini akan menghilangkan tanda tanya (?) yang muncul di Lapangan B
    $clean_value = str_replace(['–', '—', '−'], '-', $value);

    // 2. Gunakan utf8_decode untuk menangani encoding karakter
    $pdf->Cell(0, 10, utf8_decode($clean_value), 0, 1);
}

// --- FOOTER / CATATAN ---
$pdf->Ln(10);
if (!empty($data['catatan'])) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 5, 'Catatan:', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 5, utf8_decode($data['catatan']));
}

$pdf->Ln(15);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, '--- TERIMA KASIH ---', 0, 1, 'C');

// Output PDF
$pdf->Output('I', 'Booking-' . $data['nomor_booking'] . '.pdf');