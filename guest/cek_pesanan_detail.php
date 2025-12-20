<?php
// guest/cek_pesanan_detail.php
session_start();
require '../koneksi.php'; 

// Fungsi format Rupiah
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

// PERBAIKAN: Gunakan $_POST (karena form index.php menggunakan method="POST")
// Atau gunakan $_REQUEST agar fleksibel menerima POST maupun GET
$kode_booking = $_REQUEST['kode'] ?? null;
$kontak = $_REQUEST['kontak'] ?? null; 

$reservasi = null;
$error_message = null;

// Validasi input
if (!$kode_booking || !$kontak) {
    $error_message = "Kode Booking dan Kontak Pemesan harus diisi untuk melihat detail pesanan.";
} else {
    // Ambil data reservasi dengan JOIN ke tipe_kamar dan kamar
    $query = "SELECT r.*, tk.nama_tipe, k.nomor_kamar, k.lantai 
              FROM reservasi r 
              JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar 
              LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar 
              WHERE r.kode_booking = ? 
              AND (r.email_pemesan = ? OR r.telp_pemesan = ?)";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $error_message = "Terjadi kesalahan sistem: " . $conn->error;
    } else {
        $stmt->bind_param("sss", $kode_booking, $kontak, $kontak); 
        $stmt->execute();
        $result = $stmt->get_result();
        $reservasi = $result->fetch_assoc();
        $stmt->close();

        if (!$reservasi) {
            $error_message = "Data tidak ditemukan. Pastikan Kode Booking dan Kontak (Email/HP) sudah benar.";
        }
    }
}

function get_status_class($status) {
    switch ($status) {
        case 'Lunas':
        case 'Check-in':
            return ['bg' => 'bg-green-100', 'text' => 'text-green-800'];
        case 'Menunggu Verifikasi':
            return ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'];
        case 'Belum Bayar':
            return ['bg' => 'bg-red-100', 'text' => 'text-red-800'];
        default:
            return ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - Cloud Nine In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-blue-600">Cloud Nine In</a>
            <ul class="flex space-x-6 items-center">
                <li><a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium">Home</a></li>
                <li><a href="index.php#populer" class="text-gray-700 hover:text-blue-600 font-medium">Kamar Populer</a></li>
                <li><a href="index.php#cek-pesanan" class="text-gray-700 hover:text-blue-600 font-medium">Cek Pesanan Saya</a></li>
                <li><a href="../logout.php"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center shadow-md">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </a></li>
            </ul>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-xl mx-auto">
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="p-4 mb-6 text-green-800 bg-green-50 rounded-lg border border-green-200">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="p-8 bg-white rounded-xl shadow-lg text-center border-t-4 border-red-500">
                    <div class="text-red-500 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Pencarian Gagal</h1>
                    <p class="text-gray-600 mb-6"><?php echo $error_message; ?></p>
                    <a href="index.php#cek-pesanan" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Coba Lagi</a>
                </div>
            <?php else: ?>
                
                <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-100">
                    <div class="bg-blue-600 p-6 text-white text-center">
                        <p class="text-sm opacity-80 uppercase tracking-widest mb-1">Kode Booking Anda</p>
                        <h2 class="text-3xl font-black"><?php echo htmlspecialchars($reservasi['kode_booking']); ?></h2>
                    </div>

                    <div class="p-6">
                        <div class="flex gap-2 mb-6">
                            <?php $s_pay = get_status_class($reservasi['status_pembayaran']); ?>
                            <div class="flex-1 text-center p-2 rounded-lg <?php echo $s_pay['bg'] . ' ' . $s_pay['text']; ?>">
                                <p class="text-[10px] uppercase font-bold">Pembayaran</p>
                                <p class="font-bold"><?php echo $reservasi['status_pembayaran']; ?></p>
                            </div>
                            <div class="flex-1 text-center p-2 rounded-lg bg-blue-50 text-blue-800">
                                <p class="text-[10px] uppercase font-bold">Status Reservasi</p>
                                <p class="font-bold"><?php echo $reservasi['status_reservasi']; ?></p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-500">Tipe Kamar</span>
                                <span class="font-semibold"><?php echo $reservasi['nama_tipe']; ?></span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-500">No. Kamar / Lantai</span>
                                <span class="font-semibold"><?php echo ($reservasi['nomor_kamar'] ?? '-') . ' / ' . ($reservasi['lantai'] ?? '-'); ?></span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-500">Check-in</span>
                                <span class="font-semibold text-blue-600"><?php echo date('d M Y', strtotime($reservasi['tanggal_checkin'])); ?></span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-gray-500">Check-out</span>
                                <span class="font-semibold text-red-600"><?php echo date('d M Y', strtotime($reservasi['tanggal_checkout'])); ?></span>
                            </div>
                            <div class="flex justify-between pt-2">
                                <span class="text-gray-800 font-bold">Total Bayar</span>
                                <span class="text-xl font-bold text-blue-600"><?php echo formatRupiah($reservasi['total_bayar']); ?></span>
                            </div>
                        </div>

                        <div class="mt-8 p-4 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            <p class="text-sm font-bold text-gray-700 mb-2">Informasi Penting:</p>
                            <div class="text-sm text-gray-600 italic">
                                <?php if ($reservasi['status_pembayaran'] === 'Belum Bayar' && $reservasi['metode_pembayaran'] === 'Transfer Bank'): ?>
                                    Silakan transfer ke rekening hotel dan 
                                    <a href="upload_bukti_bayar.php?kode=<?= urlencode($kode_booking) ?>&kontak=<?= urlencode($kontak) ?>" class="text-blue-600 font-bold underline">Upload Bukti di Sini</a>.
                                <?php elseif ($reservasi['status_pembayaran'] === 'Belum Bayar'): ?>
                                    Silakan lakukan pembayaran langsung saat tiba di resepsionis.
                                <?php else: ?>
                                    Simpan halaman ini atau catat Kode Booking untuk proses Check-in.
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="index.php" class="block w-full text-center mt-6 bg-gray-800 text-white py-3 rounded-lg font-bold hover:bg-gray-900 transition">Kembali ke Beranda</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="text-center py-10 text-gray-400 text-sm">
        &copy; 2025 Cloud Nine In. All rights reserved.
    </footer>
</body>
</html>