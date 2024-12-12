<?php
header("Content-Type: application/json");
include 'db_connection.php';

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Variabel untuk menyimpan response
    $response = array();

    try {
        // Ambil data dari form
        $id_karyawan = $_POST['id_karyawan'];
        $nama_karyawan = $_POST['nama_karyawan'];
        $jenis_surat = $_POST['jenis_surat'];
        $judul = $_POST['judul'];
        $deskripsi = $_POST['deskripsi'];
        $tanggal = $_POST['tanggal'];

        // Proses upload file (opsional)
        $file_path = null;
        if (isset($_FILES['file_surat'])) {
            $upload_dir = 'uploads/';
            
            // Buat direktori jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = uniqid() . '_' . basename($_FILES['file_surat']['name']);
            $file_path = $upload_dir . $file_name;

            // Pindahkan file yang di-upload
            if (!move_uploaded_file($_FILES['file_surat']['tmp_name'], $file_path)) {
                throw new Exception('Gagal upload file');
            }
        }

        // Query untuk menyimpan data surat
        $query = "INSERT INTO surat_masuk (
            id_karyawan, 
            nama_karyawan, 
            jenis_surat, 
            judul, 
            deskripsi, 
            tanggal, 
            file_path, 
            status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, 'Pending'
        )";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt, 
            'sssssss', 
            $id_karyawan, 
            $nama_karyawan, 
            $jenis_surat, 
            $judul, 
            $deskripsi, 
            $tanggal, 
            $file_path
        );

        // Eksekusi query
        if (mysqli_stmt_execute($stmt)) {
            $response = [
                'status' => 'success',
                'message' => 'Surat berhasil dikirim'
            ];
            http_response_code(200);
        } else {
            throw new Exception('Gagal menyimpan surat: ' . mysqli_error($conn));
        }

    } catch (Exception $e) {
        $response = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
        http_response_code(500);
    }

    // Kirim response JSON
    echo json_encode($response);
    echo json_encode(["status" => "connected"]);

} else {
    // Jika bukan method POST
    http_response_code(405);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Method Not Allowed'
    ]);
}

// Tutup koneksi database
mysqli_close($conn);
?>