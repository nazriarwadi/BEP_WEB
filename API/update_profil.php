<?php
header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "bep_db");
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi gagal: ' . $conn->connect_error]));
}

// Ambil data dari request
$id_karyawan = isset($_POST['id_karyawan']) ? $_POST['id_karyawan'] : null;
$nama = isset($_POST['nama']) ? $_POST['nama'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$tempat_lahir = isset($_POST['tempat_lahir']) ? $_POST['tempat_lahir'] : null;
$tanggal_lahir = isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
$alamat = isset($_POST['alamat']) ? $_POST['alamat'] : null;
$no_hp = isset($_POST['no_hp']) ? $_POST['no_hp'] : null;
$jabatan = isset($_POST['jabatan']) ? $_POST['jabatan'] : null; // Ambil jabatan jika diperlukan

// Validasi input
if (!$id_karyawan || !$nama || !$password || !$tempat_lahir || !$tanggal_lahir || !$alamat || !$no_hp) {
    echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi']);
    exit;
}

// Hash password sebelum menyimpannya
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Query untuk memperbarui data karyawan
$query = "UPDATE karyawan SET 
    nama = ?, 
    password = ?, 
    tempat_lahir = ?, 
    tanggal_lahir = ?, 
    alamat = ?, 
    no_hp = ? 
    WHERE id_karyawan = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssssssi", $nama, $hashedPassword, $tempat_lahir, $tanggal_lahir, $alamat, $no_hp, $id_karyawan);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui profil']);
}

$stmt->close();
$conn->close();
?>
