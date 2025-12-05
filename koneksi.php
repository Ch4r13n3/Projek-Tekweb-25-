<?php

// Konfigurasi Database
// GANTI DENGAN PENGATURAN DATABASE ANDA
$db_host = 'localhost';     // Biasanya 'localhost'
$db_user = 'root';          // User default XAMPP
$db_pass = '';              // Password default XAMPP (kosong)
$db_name = 'hotel_db'; // GANTI DENGAN NAMA DATABASE ANDA

// Buat Koneksi ke Database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek jika koneksi gagal
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>
