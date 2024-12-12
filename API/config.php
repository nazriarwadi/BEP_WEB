<?php
$host = "localhost";
$user = "root";  // Sesuaikan dengan username MySQL Anda
$pass = "";      // Sesuaikan dengan password MySQL Anda
$db = "bep_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set header untuk mengizinkan CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");