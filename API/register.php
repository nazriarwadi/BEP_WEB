<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Hash password
    $ttl = $conn->real_escape_string(trim($_POST['ttl']));
    $alamat = $conn->real_escape_string(trim($_POST['alamat']));
    $no_hp = $conn->real_escape_string(trim($_POST['no_hp']));

    // Validasi input kosong
    if (empty($username) || empty($email) || empty($password) || empty($ttl) || empty($alamat) || empty($no_hp)) {
        echo json_encode([
            'success' => false,
            'message' => 'Semua kolom harus diisi.'
        ]);
        exit;
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Format email tidak valid.'
        ]);
        exit;
    }

    // Cek jika email sudah terdaftar
    $checkEmail = $conn->query("SELECT * FROM karyawan WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email sudah terdaftar.'
        ]);
        exit;
    }

    // Cek jika username sudah digunakan
    $checkUsername = $conn->query("SELECT * FROM karyawan WHERE username = '$username'");
    if ($checkUsername->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username sudah digunakan.'
        ]);
        exit;
    }

    // Insert data ke database
    $sql = "INSERT INTO karyawan (username, email, password, ttl, alamat, no_hp) 
            VALUES ('$username', '$email', '$password', '$ttl', '$alamat', '$no_hp')";

    if ($conn->query($sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Registrasi berhasil.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registrasi gagal: ' . $conn->error
        ]);
    }
}

$conn->close();
