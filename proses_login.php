<?php
/*
 * File: proses_login.php
 * File ini HANYA menggunakan koneksi.php
 */

// 1. Mulai Sesi (HARUS DI BARIS PALING ATAS)
session_start();

// 2. Panggil Koneksi Database (dari file koneksi.php)
// Ini akan terhubung ke 'hotel_db'
require 'koneksi.php';

// 3. Ambil Data dari Form
$username = $_POST['username'];
$password = $_POST['password'];

// 4. Query (Ambil juga id_user)
$stmt = $conn->prepare("SELECT id_user, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // 5. Verifikasi Password
    if (password_verify($password, $user['password'])) {
        // Password BENAR!
        
        // 6. Simpan ke Sesi
        $_SESSION['loggedin'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $user['role'];
        
        // 7. Alihkan (Redirect) berdasarkan Role
        if ($user['role'] == 'admin') {
            header("Location: admin/dashboard_admin.php");
        } elseif ($user['role'] == 'resepsionis') {
            header("Location: resepsionis/dashboard_resepsionis.php");
        } else {
            header("Location: guest/index.php");
        }
        exit; // Hentikan skrip

    } else {
        // Password SALAH
        $_SESSION['login_error'] = "Username atau Password salah.";
        header("Location: login.php");
        exit;
    }
} else {
    // Username TIDAK DITEMUKAN
    // (Pesan ini mungkin muncul jika username tidak ada di 'hotel_db')
    $_SESSION['login_error'] = "Username atau Password salah.";
    header("Location: login.php");
    exit;
}

if ($stmt) {
    $stmt->close();
} elseif ($conn) {
    $conn->close();
}

// $stmt->close();
// $conn->close();
?>