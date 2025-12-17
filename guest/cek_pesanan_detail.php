<?php
// guest/cek_pesanan_detail.php
session_start();
// Sesuaikan path koneksi jika berbeda
require '../koneksi.php'; 

// Fungsi format Rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

$kode_booking = $_GET['kode'] ?? null;
// Ambil variabel 'kontak' yang dikirim dari form index.php
$kontak = $_GET['kontak'] ?? null; 

$reservasi = null;
$error_message = null;

// PERBAIKAN KRITIS #1: Validasi harus mencakup kode booking DAN kontak
if (!$kode_booking || !$kontak) {
    // Jika salah satu (kode atau kontak) hilang
    $error_message = "Kode Booking dan Kontak Pemesan harus diisi. Harap masukkan kedua data yang valid.";
} else {
    // 1. Ambil data reservasi dari database
    // Menggunakan JOIN untuk mendapatkan nama tipe kamar
    $query = "SELECT r.*, tk.nama_tipe 
              FROM reservasi r 
              JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar 
              WHERE r.kode_booking = ? 
              AND (r.email_pemesan = ? OR r.telp_pemesan = ?)";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $error_message = "Terjadi kesalahan pada persiapan query database: " . $conn->error;
    } else {
        // Bind 3 parameter (kode_booking, kontak, kontak)
        $stmt->bind_param("sss", $kode_booking, $kontak, $kontak); 
        $stmt->execute();
        $result = $stmt->get_result();
        $reservasi = $result->fetch_assoc();
        $stmt->close();

        if (!$reservasi) {
            // Pesan error lebih informatif setelah verifikasi kontak
            $error_message = "Data pemesanan dengan kode **" . htmlspecialchars($kode_booking) . "** dan kontak tersebut tidak ditemukan. Pastikan kontak yang Anda masukkan sesuai dengan email atau nomor telepon saat pemesanan.";
        }
    }
}

// Fungsi pembantu untuk menentukan kelas warna berdasarkan status
function get_status_class($status) {
    switch ($status) {
        case 'Lunas':
        case 'Check-in':
            return ['bg' => 'bg-green-50', 'text' => 'text-green-800'];
        case 'Menunggu Verifikasi':
            return ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-800'];
        case 'Belum Bayar':
            return ['bg' => 'bg-red-50', 'text' => 'text-red-800'];
        case 'Batal':
            return ['bg' => 'bg-gray-200', 'text' => 'text-gray-800'];
        default:
            return ['bg' => 'bg-blue-50', 'text' => 'text-blue-800'];
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
                <li><a href="../login.php" class="bg-[#134686] hover:bg-[#27548A] text-white font-bold py-2 px-4 rounded-lg transition duration-300">Login</a></li>
            </ul>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-xl mx-auto">
            <?php 
            // VITAL: BLOK INI MENAMPILKAN PESAN SUKSES DARI UPLOAD_BUKTI_BAYAR.PHP
            if (isset($_SESSION['success_message'])): ?>
                <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-300 font-medium" role="alert">
                    <p class="font-bold">Konfirmasi Berhasil:</p>
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']); // PENTING: Hapus pesan agar tidak muncul lagi
                    ?>
                </div>
            <?php endif;
            ?>
            
            <?php if ($error_message): ?>
                <div class="p-8 bg-white rounded-xl shadow-2xl text-center border-t-8 border-red-500">
                    <h1 class="text-3xl font-extrabold text-gray-800 mb-4">Pencarian Gagal!</h1>
                    <p class="text-red-700 mb-6 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
                    <a href="index.php#cek-pesanan" class="mt-8 inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg">Coba Lagi</a>
                </div>
            <?php else: ?>
                <?php 
                // Amankan data untuk URL 
                $kode_url = urlencode($reservasi['kode_booking']);
                $kontak_url = urlencode($kontak); 
                $status_pembayaran_class = get_status_class($reservasi['status_pembayaran']);
                $status_reservasi_class = get_status_class($reservasi['status_reservasi'] ?? ''); // Asumsi status reservasi juga bisa diwarnai
                ?>
                <div class="p-8 bg-white rounded-xl shadow-2xl border-t-8 border-blue-600">
                    <h1 class="text-3xl font-extrabold text-gray-800 mb-2">Detail Pesanan Anda</h1>
                    <div class="bg-blue-50 p-6 rounded-lg mb-6 border border-blue-300 text-center">
                        <p class="text-sm font-semibold text-blue-800">KODE BOOKING:</p>
                        <p class="text-4xl font-extrabold text-blue-600 mt-2 tracking-wider"><?php echo htmlspecialchars($reservasi['kode_booking']); ?></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="p-4 rounded-lg text-center <?php echo $status_pembayaran_class['bg'] . ' ' . $status_pembayaran_class['text']; ?>">
                            <p class="text-sm font-semibold">Status Pembayaran</p>
                            <p class="text-xl font-bold mt-1"><?php echo htmlspecialchars($reservasi['status_pembayaran']); ?></p>
                        </div>
                        <div class="p-4 rounded-lg text-center <?php echo $status_reservasi_class['bg'] . ' ' . $status_reservasi_class['text']; ?>">
                            <p class="text-sm font-semibold">Status Reservasi</p>
                            <p class="text-xl font-bold mt-1"><?php echo htmlspecialchars($reservasi['status_reservasi']); ?></p>
                        </div>
                    </div>
                    
                    <div class="p-4 bg-gray-100 rounded-lg mb-6 text-center">
                        <p class="text-sm font-semibold text-gray-800">TOTAL PEMBAYARAN</p>
                        <p class="text-3xl font-extrabold text-red-600 mt-1"><?php echo formatRupiah($reservasi['total_bayar']); ?></p>
                    </div>

                    <div class="space-y-3 p-4 bg-gray-50 rounded-lg border mb-6">
                        <p class="font-bold text-gray-700 border-b pb-2">Rincian Kamar & Tanggal</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                            <li><strong>Tipe Kamar:</strong> <?php echo htmlspecialchars($reservasi['nama_tipe']); ?></li>
                            <li><strong>Jumlah Kamar Dipesan:</strong> <?php echo $reservasi['jumlah_tamu']; ?></li>
                            <li><strong>Tanggal Check-in:</strong> <?php echo htmlspecialchars($reservasi['tanggal_checkin']); ?></li>
                            <li><strong>Tanggal Check-out:</strong> <?php echo htmlspecialchars($reservasi['tanggal_checkout']); ?></li>
                            <li><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($reservasi['metode_pembayaran']); ?></li>
                        </ul>
                    </div>

                    <div class="space-y-3 p-4 bg-gray-50 rounded-lg border">
                        <p class="font-bold text-gray-700 border-b pb-2">Informasi Pemesan</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                            <li><strong>Nama:</strong> <?php echo htmlspecialchars($reservasi['nama_pemesan']); ?></li>
                            <li><strong>KTP:</strong> <?php echo htmlspecialchars($reservasi['ktp_pemesan']); ?></li>
                            <li><strong>Email:</strong> <?php echo htmlspecialchars($reservasi['email_pemesan']); ?></li>
                            <li><strong>Telepon:</strong> <?php echo htmlspecialchars($reservasi['telp_pemesan']); ?></li>
                        </ul>
                    </div>
                    
                    <div class="space-y-3 mt-6 p-4 bg-red-100 rounded-lg border border-red-400">
                        <p class="font-bold text-red-800 border-b pb-2">Aksi Selanjutnya</p>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            <?php if ($reservasi['metode_pembayaran'] === 'Transfer Bank' && $reservasi['status_pembayaran'] === 'Belum Bayar'): ?>
                                <li>
                                    **Pembayaran Belum Lunas (Transfer Bank):** Pesanan Anda belum lunas. Silakan lakukan transfer dan 
                                    <a href="upload_bukti_bayar.php?kode=<?php echo $kode_url; ?>&kontak=<?php echo $kontak_url; ?>" class="text-blue-600 font-bold hover:underline">UPLOAD BUKTI TRANSFER DI SINI</a> 
                                    sebelum batas waktu pembayaran.
                                </li>
                            <?php elseif ($reservasi['metode_pembayaran'] === 'Bayar Di Tempat' && $reservasi['status_pembayaran'] === 'Belum Bayar'): ?>
                                <li>
                                    **Bayar Di Tempat:** Pembayaran penuh akan dilakukan saat Anda Check-in di hotel. Pastikan Anda membawa kode booking ini.
                                </li>
                            <?php elseif ($reservasi['status_pembayaran'] === 'Menunggu Verifikasi'): ?>
                                <li>
                                    **Menunggu Verifikasi:** Bukti pembayaran Anda sedang dicek oleh tim kami. Harap tunggu konfirmasi lebih lanjut.
                                </li>
                            <?php else: ?>
                                <li>
                                    **Pembayaran Lunas:** Pesanan Anda telah lunas. Anda dapat langsung Check-in pada tanggal yang ditentukan.
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="mt-8 text-center">
                        <a href="index.php" class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg">
                            Selesai & Kembali ke Beranda
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>