<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "bep_db");

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Check if id_karyawan is provided in the query string
        $id_karyawan = isset($_GET['id_karyawan']) ? $conn->real_escape_string($_GET['id_karyawan']) : null;
        
        // Base SQL query
        $sql = "SELECT j.id, k.nama AS nama_karyawan, j.tanggal_mulai, j.tanggal_selesai, 
                j.jam_mulai, j.jam_selesai, j.lokasi 
                FROM jadwal_kerja j 
                JOIN karyawan k ON j.id_karyawan = k.id_karyawan";
        
        // Add WHERE clause if id_karyawan is provided
        if ($id_karyawan) {
            $sql .= " WHERE j.id_karyawan = '$id_karyawan'";
        }
        
        $sql .= " ORDER BY j.tanggal_mulai DESC";
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $jadwal = [];
            while ($row = $result->fetch_assoc()) {
                $jadwal[] = $row;
            }
            echo json_encode(["status" => "success", "data" => $jadwal]);
        } else {
            echo json_encode(["status" => "success", "data" => []]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['id_karyawan'], $input['tanggal_mulai'], $input['tanggal_selesai'], 
                  $input['jam_mulai'], $input['jam_selesai'], $input['lokasi'])) {
            
            $id_karyawan = $conn->real_escape_string($input['id_karyawan']);
            $tanggal_mulai = $conn->real_escape_string($input['tanggal_mulai']);
            $tanggal_selesai = $conn->real_escape_string($input['tanggal_selesai']);
            $jam_mulai = $conn->real_escape_string($input['jam_mulai']);
            $jam_selesai = $conn->real_escape_string($input['jam_selesai']);
            $lokasi = $conn->real_escape_string($input['lokasi']);

            $sql = "INSERT INTO jadwal_kerja (id_karyawan, tanggal_mulai, tanggal_selesai, 
                    jam_mulai, jam_selesai, lokasi) 
                    VALUES ('$id_karyawan', '$tanggal_mulai', '$tanggal_selesai', 
                    '$jam_mulai', '$jam_selesai', '$lokasi')";

            if ($conn->query($sql) === TRUE) {
                echo json_encode(["status" => "success", "message" => "Jadwal berhasil ditambahkan."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Metode HTTP tidak didukung."]);
        break;
}

$conn->close();
?>