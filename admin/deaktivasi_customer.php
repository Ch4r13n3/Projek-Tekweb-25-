<?php
// deaktivasi_customer.php
session_start();
require '../koneksi.php';

// Cek apakah sudah login DAN rolenya 'admin'
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['current_status'])) {
    $id_user = $_GET['id'];
    $current_status = $_GET['current_status'];
    
    // Tentukan status baru (toggle/bolak-balik)
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';
    
    // Siapkan pesan notifikasi
    $action_word = ($new_status === 'inactive') ? 'dinonaktifkan' : 'diaktifkan kembali';
    
    // Query untuk update status
    $query = "UPDATE users SET status = ? WHERE id_user = ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("si", $new_status, $id_user);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Akun Customer dengan ID {$id_user} berhasil {$action_word}.";
    } else {
        $_SESSION['error_message'] = "Gagal mengubah status akun: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    $_SESSION['error_message'] = "Parameter ID atau status tidak ditemukan.";
}

// Redirect kembali ke halaman daftar customer
header("Location: daftar_kelola_dataCustomer.php");
exit;
?>