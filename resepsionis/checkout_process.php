<?php
// resepsionis/checkout_process.php
session_start();
require_once '../koneksi.php';

// 1. Autentikasi Keamanan
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

function notify($kode, $msg, $class = 'danger') {
    $_SESSION['msg'] = $msg;
    $_SESSION['alert_class'] = $class;
    header("Location: reservasi_detail.php?kode=" . urlencode($kode));
    exit;
}

$kode_booking = $_GET['kode'] ?? null;
if (!$kode_booking) {
    header("Location: reservasi_list.php");
    exit;
}

// 2. Ambil data (Hanya bisa checkout jika statusnya 'Check-in')
$sql_fetch = "SELECT id_reservasi, id_kamar_ditempati FROM reservasi 
              WHERE kode_booking = ? AND status_reservasi = 'Check-in' LIMIT 1";

$stmt = $conn->prepare($sql_fetch);
$stmt->bind_param("s", $kode_booking); // Memperbaiki bug parameter "ss" sebelumnya
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    notify($kode_booking, "Data tidak ditemukan atau tamu belum berstatus Check-in.");
}

// 3. Proses Transaksi
$conn->begin_transaction();
try {
    // Update Reservasi jadi Completed
    $stmt1 = $conn->prepare("UPDATE reservasi SET status_reservasi = 'Completed' WHERE id_reservasi = ?");
    $stmt1->bind_param("i", $reservation['id_reservasi']);
    $stmt1->execute();

    // Update Kamar jadi Tersedia (Konsisten dengan Check-in)
    $stmt2 = $conn->prepare("UPDATE kamar SET status = 'Tersedia' WHERE id_kamar = ?");
    $stmt2->bind_param("i", $reservation['id_kamar_ditempati']);
    $stmt2->execute();

    $conn->commit();
    notify($kode_booking, "Check-out Berhasil. Kamar telah dikosongkan.", 'success');
} catch (Exception $e) {
    $conn->rollback();
    notify($kode_booking, "Gagal Check-out: " . $e->getMessage());
}