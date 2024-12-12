<?php
header('Content-Type: application/json');

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "bep_db");
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Koneksi gagal: ' . $conn->connect_error]));
}

// Ambil ID karyawan dari request
$id_karyawan = isset($_POST['id_karyawan']) ? $_POST['id_karyawan'] : null;

if ($id_karyawan) {
    // Query untuk mengambil data karyawan
    $query = "SELECT * FROM karyawan WHERE id_karyawan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_karyawan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Karyawan tidak ditemukan']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID karyawan tidak diberikan']);
}

$conn->close();
?>
