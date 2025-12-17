<?php
// resepsionis/reservasi_detail.php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['kode'])) {
    header("Location: reservasi_list.php");
    exit;
}

$kode = $_GET['kode'];

$sql = "SELECT r.*, u.nama_lengkap as nama_user, u.email as email_user, 
               tk.nama_tipe, tk.harga_per_malam, k.nomor_kamar 
        FROM reservasi r
        LEFT JOIN users u ON r.id_user = u.id_user
        JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar
        LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar
        WHERE r.kode_booking = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $kode);
$stmt->execute();
$result = $stmt->get_result();
$d = $result->fetch_assoc();

if (!$d) {
    die("Data reservasi tidak ditemukan.");
}

// --- PERBAIKAN: HITUNG DURASI SECARA OTOMATIS ---
$checkin = new DateTime($d['tanggal_checkin']);
$checkout = new DateTime($d['tanggal_checkout']);
$durasi = $checkout->diff($checkin)->days; 
if ($durasi <= 0) $durasi = 1; // Minimal 1 malam jika tanggal sama

$nama_tamu = $d['nama_user'] ?: $d['nama_pemesan'];
$email_tamu = $d['email_user'] ?: $d['email_pemesan'];

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function get_status_color($status) {
    $map = [
        'Pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'Confirmed' => 'bg-blue-100 text-blue-700 border-blue-200',
        'Check-in' => 'bg-green-100 text-green-700 border-green-200',
        'Completed' => 'bg-gray-100 text-gray-700 border-gray-200',
        'Batal' => 'bg-red-100 text-red-700 border-red-200',
        'Batal' => 'bg-red-600 text-white',
        'Lunas' => 'bg-emerald-500 text-white',
        'Belum Bayar' => 'bg-rose-500 text-white'
    ];
    return $map[$status] ?? 'bg-gray-50 text-gray-600 border-gray-100';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail #<?= $d['kode_booking'] ?> - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <a href="reservasi_list.php" class="text-gray-500 hover:text-gray-800 transition flex items-center font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                </a>
                <button onclick="window.print()" class="bg-white border border-gray-200 px-4 py-2 rounded-lg hover:bg-gray-50 font-bold">
                    <i class="fas fa-print mr-2"></i> Cetak
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-sky-700 p-8 text-white flex justify-between items-start">
                    <div>
                        <p class="text-sky-200 text-xs font-bold uppercase tracking-widest mb-1">Invoice Reservasi</p>
                        <h1 class="text-3xl font-black"><?= $d['kode_booking'] ?></h1>
                        <p class="mt-2 text-sm opacity-80">Dipesan: <?= date('d M Y', strtotime($d['tanggal_pemesanan'])) ?></p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-bold border-2 <?= get_status_color($d['status_reservasi']) ?>">
                        <?= $d['status_reservasi'] ?>
                    </span>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-10">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase mb-4">Informasi Tamu</h3>
                            <p class="font-bold text-gray-800"><?= htmlspecialchars($nama_tamu) ?></p>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($email_tamu) ?></p>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($d['telp_pemesan']) ?></p>
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase mb-4">Informasi Menginap</h3>
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-500">Durasi</span>
                                    <span class="font-bold text-sky-600"><?= $durasi ?> Malam</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Periode</span>
                                    <span class="font-bold"><?= date('d/m/y', strtotime($d['tanggal_checkin'])) ?> - <?= date('d/m/y', strtotime($d['tanggal_checkout'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="w-full mb-8 text-sm">
                        <thead>
                            <tr class="text-left text-gray-400 uppercase border-b">
                                <th class="pb-4">Deskripsi</th>
                                <th class="pb-4 text-center">Qty</th>
                                <th class="pb-4 text-right">Harga Satuan</th>
                                <th class="pb-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-5">
                                    <p class="font-bold"><?= $d['nama_tipe'] ?></p>
                                    <p class="text-xs text-sky-600">Nomor Kamar: <?= $d['nomor_kamar'] ?: 'Belum dialokasi' ?></p>
                                </td>
                                <td class="py-5 text-center"><?= $durasi ?> Malam</td>
                                <td class="py-5 text-right"><?= formatRupiah($d['harga_per_malam']) ?></td>
                                <td class="py-5 text-right font-bold"><?= formatRupiah($d['total_biaya_kamar']) ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2">
                                <td colspan="3" class="py-4 text-right font-bold text-gray-500 text-lg">Total Bayar</td>
                                <td class="py-4 text-right font-black text-2xl text-emerald-600"><?= formatRupiah($d['total_bayar']) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>