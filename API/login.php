<?php
header('Content-Type: application/json');
require_once '../koneksi.php'; // Pastikan koneksi ke database sudah benar

// Cek apakah metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari request
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $password = $_POST['password'];
    $id_kerja = mysqli_real_escape_string($koneksi, $_POST['id_kerja']);

    // Query untuk mencari karyawan berdasarkan nama dan id_kerja
    $query = "SELECT * FROM karyawan WHERE nama = '$nama' AND id_kerja = '$id_kerja'";
    $result = mysqli_query($koneksi, $query);

    // Cek apakah karyawan ditemukan
    if (mysqli_num_rows($result) > 0) {
        $data_karyawan = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $data_karyawan['password'])) {
            // Jika login berhasil, kirimkan response sukses
            echo json_encode([
                'status' => 'success',
                'message' => 'Login berhasil',
                'data' => [
                    'id_karyawan' => $data_karyawan['id_karyawan'],
                    'nama' => $data_karyawan['nama'],
                    'jabatan' => $data_karyawan['jabatan'],
                ]
            ]);
        } else {
            // Jika password salah
            echo json_encode([
                'status' => 'error',
                'message' => 'Password salah'
            ]);
        }
    } else {
        // Jika karyawan tidak ditemukan
        echo json_encode([
            'status' => 'error',
            'message' => 'Karyawan tidak ditemukan'
        ]);
    }
} else {
    // Jika metode request bukan POST
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode request tidak valid'
    ]);
}

// Tutup koneksi
mysqli_close($koneksi);
?>
