<!-- guest/pemesanan.php -->

<?php
session_start();
// Pastikan path koneksi.php sudah benar (asumsi /guest/pemesanan.php)
require '../koneksi.php'; 

// --- 1. AMBIL DAN HITUNG DATA DARI URL ---
$tipe_id = $_GET['tipe_id'] ?? 1;
$checkin = $_GET['checkin'] ?? date('Y-m-d');
$checkout = $_GET['checkout'] ?? date('Y-m-d', strtotime('+1 day'));
$jml = $_GET['jml'] ?? 1;

// Hitung lama menginap
$dt_in = new DateTime($checkin);
$dt_out = new DateTime($checkout);
$interval = $dt_in->diff($dt_out);
$lama = $interval->days;

if ($lama <= 0) $lama = 1;
$jml = (int)$jml; // Pastikan jumlah kamar adalah integer

// --- 2. AMBIL DETAIL TIPE KAMAR DARI DATABASE ---
$tipe_kamar = null;
$error_fetching = false;

try {
    $stmt = $conn->prepare("SELECT nama_tipe, harga_per_malam FROM tipe_kamar WHERE id_tipe_kamar = ?");
    $stmt->bind_param("i", $tipe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tipe_kamar = $result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    $error_fetching = true;
    // Log error (gunakan ini jika di lingkungan produksi)
    // error_log("Database error fetching room type: " . $e->getMessage());
}

if (!$tipe_kamar) {
    $error_fetching = true;
} else {
    // Hitung total
    $harga_per_malam = $tipe_kamar['harga_per_malam'];
    $total_bayar = $harga_per_malam * $lama * $jml;
}

// --- 3. FUNGSI PELENGKAP ---
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kamar - Cloud Nine In</title>
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
                <li><a href="../login.php" class="bg-[#134686] hover:bg-[#27548A] text-white font-bold py-2 px-4 rounded-lg transition duration-300">Login</a></li>
            </ul>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <?php if ($error_fetching): ?>
            <div class="max-w-3xl mx-auto p-8 bg-white rounded-xl shadow-2xl text-center border-t-8 border-red-500">
                <h1 class="text-3xl font-extrabold text-gray-800 mb-4">Kesalahan Pemesanan</h1>
                <p class="text-red-700 mb-6 font-medium">Detail kamar tidak ditemukan. Harap kembali ke halaman pencarian dan coba lagi.</p>
                <a href="index.php" class="mt-8 inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg">Kembali ke Beranda</a>
            </div>
        <?php else: ?>
            <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-2xl overflow-hidden">
                <div class="p-8 border-b">
                    <h1 class="text-3xl font-extrabold text-blue-600">Konfirmasi Detail Pemesan</h1>
                    <p class="text-gray-500">Lengkapi data di bawah ini untuk menyelesaikan pemesanan kamar Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-8">
                    <div class="md:col-span-1 bg-gray-50 p-6 rounded-lg border border-gray-200 h-fit">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Ringkasan Pesanan</h2>
                        <ul class="space-y-3 text-sm">
                            <li><strong>Tipe Kamar:</strong> <span class="float-right font-medium text-blue-600"><?php echo htmlspecialchars($tipe_kamar['nama_tipe']); ?></span></li>
                            <li><strong>Check-in:</strong> <span class="float-right"><?php echo date('d M Y', strtotime($checkin)); ?></span></li>
                            <li><strong>Check-out:</strong> <span class="float-right"><?php echo date('d M Y', strtotime($checkout)); ?></span></li>
                            <li><strong>Lama Menginap:</strong> <span class="float-right"><?php echo $lama; ?> Malam</span></li>
                            <li><strong>Jumlah Kamar:</strong> <span class="float-right"><?php echo $jml; ?> Unit</span></li>
                            <li class="pt-3 border-t font-bold text-lg">
                                Total Bayar: 
                                <span class="float-right text-red-600"><?php echo formatRupiah($total_bayar); ?></span>
                            </li>
                            <li class="text-xs text-gray-500 pt-2">
                                *Pembayaran dilakukan di halaman selanjutnya (Konfirmasi) atau di tempat.
                            </li>
                        </ul>
                    </div>

                    <div class="md:col-span-2">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Data Diri Pemesan</h2>
                        <form action="konfirmasi.php" method="POST" class="space-y-4">
                            
                            <input type="hidden" name="tipe_id" value="<?php echo $tipe_id; ?>">
                            <input type="hidden" name="checkin" value="<?php echo $checkin; ?>">
                            <input type="hidden" name="checkout" value="<?php echo $checkout; ?>">
                            <input type="hidden" name="lama" value="<?php echo $lama; ?>">
                            <input type="hidden" name="jumlah_kamar" value="<?php echo $jml; ?>">


                            <div>
                                <label for="nama_pemesan" class="block text-sm font-medium text-gray-700">Nama Lengkap (Sesuai ID):</label>
                                <input type="text" id="nama_pemesan" name="nama_pemesan" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="ktp_pemesan" class="block text-sm font-medium text-gray-700">Nomor KTP/Identitas:</label>
                                <input type="text" id="ktp_pemesan" name="ktp_pemesan" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="email_pemesan" class="block text-sm font-medium text-gray-700">Email (Untuk Notifikasi):</label>
                                <input type="email" id="email_pemesan" name="email_pemesan" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="telp_pemesan" class="block text-sm font-medium text-gray-700">Nomor Telepon:</label>
                                <input type="tel" id="telp_pemesan" name="telp_pemesan" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-300 shadow-md">
                                Lanjut ke Konfirmasi Pembayaran
                            </button>
                            
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>