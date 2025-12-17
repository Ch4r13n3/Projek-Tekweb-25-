<?php
// resepsionis/checkin_process.php
session_start();
require_once '../koneksi.php';

// 1. Autentikasi Keamanan
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

// Fungsi helper untuk pesan
function notify($kode, $msg, $class = 'danger') {
    $_SESSION['msg'] = $msg;
    $_SESSION['alert_class'] = $class;
    header("Location: reservasi_detail.php?kode=" . urlencode($kode));
    exit;
}

$kode_booking = $_GET['kode'] ?? null;
if (!$kode_booking) {
    $_SESSION['msg'] = "Kode Booking tidak valid.";
    header("Location: reservasi_list.php");
    exit;
}

// 2. Ambil data reservasi
$sql_fetch = "SELECT r.id_reservasi, r.status_reservasi, r.id_kamar_ditempati, k.nomor_kamar
              FROM reservasi r
              LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar
              WHERE r.kode_booking = ? LIMIT 1";

$stmt = $conn->prepare($sql_fetch);
$stmt->bind_param("s", $kode_booking);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    notify($kode_booking, "Data reservasi tidak ditemukan.");
}

// 3. Validasi: Harus status 'Confirmed' dan sudah ada alokasi kamar
if ($reservation['status_reservasi'] !== 'Confirmed') {
    notify($kode_booking, "Hanya reservasi berstatus 'Confirmed' yang bisa Check-in.", 'warning');
}
if (empty($reservation['id_kamar_ditempati'])) {
    notify($kode_booking, "Kamar fisik belum ditentukan. Silakan alokasikan kamar dulu.", 'warning');
}

// 4. Proses Transaksi
$conn->begin_transaction();
try {
    // Update Reservasi
    $stmt1 = $conn->prepare("UPDATE reservasi SET status_reservasi = 'Check-in' WHERE id_reservasi = ?");
    $stmt1->bind_param("i", $reservation['id_reservasi']);
    $stmt1->execute();

    // Update Status Kamar (Konsisten menggunakan 'Terisi')
    $stmt2 = $conn->prepare("UPDATE kamar SET status = 'Terisi' WHERE id_kamar = ?");
    $stmt2->bind_param("i", $reservation['id_kamar_ditempati']);
    $stmt2->execute();

    $conn->commit();
    notify($kode_booking, "Check-in Berhasil! Kamar " . $reservation['nomor_kamar'] . " sekarang Terisi.", 'success');
} catch (Exception $e) {
    $conn->rollback();
    notify($kode_booking, "Sistem Error: " . $e->getMessage());
}