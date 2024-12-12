<?php
// Set header JSON untuk respon
header('Content-Type: application/json');

// Sertakan file koneksi database
include_once '../function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input POST
        if (!isset($_POST['qr_data'], $_POST['id_karyawan'], $_POST['latitude'], $_POST['longitude'])) {
            throw new Exception('Data input tidak lengkap');
        }

        // Validasi dan sanitasi input
        $qrDataEncoded = $_POST['qr_data'];
        $id_karyawan = intval($_POST['id_karyawan']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);

        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            throw new Exception('Format data lokasi tidak valid');
        }

        // Decode data QR
        $qrDataDecoded = json_decode($qrDataEncoded, true);
        if (!$qrDataDecoded || !isset($qrDataDecoded['id_sesi'], $qrDataDecoded['tipe'], $qrDataDecoded['created_at'])) {
            throw new Exception('QR Code tidak valid');
        }

        // Ambil data dari QR Code
        $idSesi = intval($qrDataDecoded['id_sesi']);
        $tipe = $qrDataDecoded['tipe'];
        $createdAt = $qrDataDecoded['created_at']; // Waktu absen dari QR Code

        // Validasi ID karyawan
        $query = "SELECT * FROM karyawan WHERE id_karyawan = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_karyawan);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('ID karyawan tidak ditemukan');
        }

        // Ambil data sesi_absensi berdasarkan id_sesi dari QR Code
        $query = "SELECT id, status FROM sesi_absensi WHERE id_sesi = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $idSesi);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('ID sesi tidak ditemukan di database');
        }

        // Ambil status sesi dari database
        $row = $result->fetch_assoc();
        $idSesiFromDB = $row['id'];
        $status = $row['status']; // Status bisa 'active' atau 'inactive'

        // Cek apakah sesi masih aktif
        if ($status === 'inactive') {
            throw new Exception('QR Code absensi sudah expired. Mohon minta admin untuk buat QR Code baru untuk melakukan absensi.');
        }

        // Simpan data absensi ke database
        $query = "INSERT INTO absensi (id_karyawan, waktu_absen, latitude, longitude, status, id_sesi) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Gagal mempersiapkan query insert absensi');
        }
        $stmt->bind_param(
            "issssi",
            $id_karyawan,
            $createdAt,
            $latitude,
            $longitude,
            $tipe,
            $idSesiFromDB // Menggunakan id dari sesi_absensi yang didapatkan
        );

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Absensi berhasil dicatat']);
        } else {
            throw new Exception('Gagal mencatat absensi');
        }
    } catch (Exception $e) {
        // Kirim respon error
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    // Metode selain POST tidak diizinkan
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
?>
