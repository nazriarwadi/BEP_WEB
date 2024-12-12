<?php
require 'function.php'; // Pastikan file ini mengandung koneksi ke database

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="pengeluaran_export.csv"'); // Nama file yang akan didownload

// Membuat output buffer
$output = fopen("php://output", "w");

// Menulis header kolom ke file CSV
fputcsv($output, array('ID Pengeluaran', 'Nama Barang', 'Harga', 'Jumlah', 'Tanggal')); // Sesuai dengan nama kolom di database Anda

// Query untuk mengambil data dari database
$query = mysqli_query($conn, "SELECT * FROM pengeluaran ORDER BY idpengeluar"); // Ganti dengan nama tabel dan kolom ID yang sesuai

// Looping data dan menulis ke file CSV
while ($row = mysqli_fetch_assoc($query)) {
    // Format tanggal sesuai kebutuhan, misalnya "Y-m-d H:i:s"
    $row['tanggal'] = date("Y-m-d H:i:s", strtotime($row['tanggal']));
    fputcsv($output, $row);
}

fclose($output);
exit;
?>