<?php 
// Mulai sesi
session_start();

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "bep_db");

// Periksa koneksi database
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Konstanta
define('SECRET_KEY', 'RAHASIA'); // Ganti dengan secret key yang aman
define('QR_VALIDITY_MINUTES', 5); // Masa berlaku QR dalam menit

// Fungsi untuk mendapatkan sesi aktif
if (!function_exists('getActiveSession')) {
    function getActiveSession($conn) {
        $query = "SELECT * FROM sesi_absensi WHERE status = 'active' ORDER BY created_at DESC LIMIT 1";
        $result = mysqli_query($conn, $query);
        return mysqli_fetch_assoc($result);
    }
}

// Fungsi untuk membuat sesi baru
if (!function_exists('createAttendanceSession')) {
    function createAttendanceSession($conn, $type) {
        // Nonaktifkan semua sesi yang aktif
        mysqli_query($conn, "UPDATE sesi_absensi SET status = 'inactive' WHERE status = 'active'");

        // Buat sesi baru
        $query = "INSERT INTO sesi_absensi (tipe, status, created_at) VALUES (?, 'active', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $type);
        return $stmt->execute();
    }
}

// Fungsi untuk mendapatkan data karyawan berdasarkan ID
if (!function_exists('getEmployeeById')) {
    function getEmployeeById($conn, $id_karyawan) {
        $query = "SELECT * FROM karyawan WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id_karyawan);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}

// Fungsi untuk mencatat absensi
if (!function_exists('recordAttendance')) {
    function recordAttendance($conn, $id_karyawan, $latitude, $longitude, $status, $session_id) {
        $query = "INSERT INTO absensi (id_karyawan, waktu_absen, latitude, longitude, status, id_sesi) 
                  VALUES (?, NOW(), ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssi", $id_karyawan, $latitude, $longitude, $status, $session_id);
        return $stmt->execute();
    }
}

// Fungsi untuk validasi QR Code
if (!function_exists('validateQRCode')) {
    function validateQRCode($qrData) {
        $decoded = json_decode(base64_decode($qrData), true);

        if (!$decoded || !isset($decoded['timestamp'], $decoded['session_id'], $decoded['secret'])) {
            throw new Exception('QR Code tidak valid');
        }

        // Validasi secret key
        if ($decoded['secret'] !== SECRET_KEY) {
            throw new Exception('QR Code tidak valid');
        }

        // Validasi timestamp QR
        $currentTime = time();
        if ($currentTime - $decoded['timestamp'] > (QR_VALIDITY_MINUTES * 60)) {
            throw new Exception('QR Code sudah kadaluarsa');
        }

        return $decoded;
    }
}

// Fungsi untuk memeriksa status login
if (!function_exists('checkLogin')) {
    function checkLogin($role = null) {
        if (!isset($_SESSION['log'])) {
            header('Location: login.php');
            exit;
        }

        if ($role && $_SESSION['role'] !== $role) {
            header('Location: login.php');
            exit;
        }
    }
}

// Fungsi untuk menghasilkan QR Code data
if (!function_exists('generateQRCodeData')) {
    function generateQRCodeData($session_id) {
        $data = [
            'timestamp' => time(),
            'secret' => SECRET_KEY,
            'session_id' => $session_id,
        ];
        return base64_encode(json_encode($data));
    }
}
?>