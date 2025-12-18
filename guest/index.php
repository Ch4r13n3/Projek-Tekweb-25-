<?php
// guest/index.php
session_start();
require '../koneksi.php'; 

// Fungsi format Rupiah (Diasumsikan diambil dari file lain atau didefinisikan di sini)
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Pastikan nama kolom 'foto' sesuai dengan yang ada di struktur tabel 'tipe_kamar' Anda
$query_populer = "SELECT id_tipe_kamar, nama_tipe, deskripsi, harga_per_malam, foto FROM tipe_kamar ORDER BY harga_per_malam DESC LIMIT 3";$result_populer = $conn->query($query_populer);
$kamar_populer = $result_populer->fetch_all(MYSQLI_ASSOC);

// Catatan: Jika Anda sudah memiliki koneksi dan query DB, hapus simulasi ini.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Nine In - Pesan Kamar Hotel Terbaik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-room:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-blue-600">Cloud Nine In</a>
            <ul class="flex space-x-6 items-center">
                <li><a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium">Home</a></li>
                <li><a href="#populer" class="text-gray-700 hover:text-blue-600 font-medium">Kamar Populer</a></li>
                <li><a href="#cek-pesanan" class="text-gray-700 hover:text-blue-600 font-medium">Cek Pesanan Saya</a></li>
            </ul>
            <?php 
            // Menggunakan variabel sesi yang sama dengan Admin/Resepsionis: 'loggedin'
            if (isset($_SESSION['loggedin'])): 
            ?>
                <a href="../logout.php"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center shadow-md">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            <?php else: ?>
                <a href="../login.php"
                    class="bg-[#134686] hover:bg-[#27548A] text-white font-bold py-2 px-4 rounded-lg transition duration-300 shadow-md">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <section class="mb-16 p-8 bg-white rounded-xl shadow-lg border-t-4 border-blue-600">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-6 flex items-center">
                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Cari Ketersediaan Kamar
            </h2>
            <form action="hasil_pencarian.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="check_in" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Check-in</label>
                    <input type="date" id="check_in" name="check_in" value="<?php echo date('Y-m-d'); ?>" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="check_out" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Check-out</label>
                    <input type="date" id="check_out" name="check_out" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="jumlah_kamar" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kamar</label>
                    <input type="number" id="jumlah_kamar" name="jumlah_kamar" min="1" value="1" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-300">
                        Cari Kamar
                    </button>
                </div>
            </form>
        </section>

        <hr class="border-gray-300 my-10">

        <section id="populer" class="mb-16">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-8 text-center">
                <span class="text-blue-600">Kamar Populer</span> ✨ Pilihan Terbaik Kami
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (!empty($kamar_populer)): ?>
                    <?php foreach ($kamar_populer as $kamar): ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden card-room transition duration-300">
                            
                            <?php 
                            // 1. Definisikan folder (keluar dari 'guest' masuk ke 'uploads')
                            $folder_uploads = "../uploads/"; 
                            
                            // 2. Gunakan null coalescing (??) agar tidak error jika key tidak ada
                            // GANTI 'foto' di bawah ini dengan nama kolom asli di database Anda (misal: 'gambar')
                            $nama_file = $kamar['foto'] ?? ''; 
                            
                            // 3. Logika pengecekan file
                            if (!empty($nama_file) && file_exists($folder_uploads . $nama_file)) {
                                $sumber_gambar = $folder_uploads . $nama_file;
                            } else {
                                $sumber_gambar = "https://via.placeholder.com/600x400/cbd5e1/64748b?text=Gambar+Tidak+Tersedia";
                            }
                        ?>


                            <div class="relative h-52 overflow-hidden">
                                <img src="<?= $sumber_gambar; ?>" 
     alt="<?= htmlspecialchars($kamar['nama_tipe']?? 'Kamar'); ?>" 
     class="w-full h-52 object-cover object-center transition-transform duration-500 hover:scale-110">
                                <div class="absolute top-4 right-4 bg-blue-600 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md">
                                    Populer
                                </div>
                            </div>

                            <div class="p-5">
                                <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($kamar['nama_tipe']); ?></h3>
                                <p class="text-xs text-gray-500 mb-4 line-clamp-2">
                                    <?= htmlspecialchars($kamar['deskripsi'] ?? 'Deskripsi belum tersedia.'); ?>
                                </p>

                                <div class="flex items-center gap-3 mb-6 text-gray-400 text-[11px]">
                                    <span class="flex items-center gap-1"><i class="fas fa-wifi"></i> WiFi</span>
                                    <span class="flex items-center gap-1"><i class="fas fa-snowflake"></i> AC</span>
                                    <span class="flex items-center gap-1"><i class="fas fa-coffee"></i> Breakfast</span>
                                </div>
                                
                                <div class="flex justify-between items-end border-t border-gray-100 pt-4">
                                    <div>
                                        <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider">Mulai Dari</span>
                                        <span class="text-xl font-extrabold text-blue-700"><?= formatRupiah($kamar['harga_per_malam']); ?></span>
                                        <span class="text-[10px] text-gray-400">/malam</span>
                                    </div>
                                    <a href="pemesanan.php?tipe_id=<?= $kamar['id_tipe_kamar']; ?>" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition duration-300 shadow-lg shadow-blue-100">
                                    Pesan Sekarang
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <hr class="border-gray-300 my-10">

        <section id="cek-pesanan" class="p-8 bg-white rounded-xl shadow-lg">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-6 flex items-center">
                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Cek Pesanan Saya
            </h2>
            <form action="cek_pesanan_detail.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-1">
                    <label for="kode_booking" class="block text-sm font-medium text-gray-700 mb-1">Kode Booking</label>
                    <input type="text" id="kode_booking" name="kode" required placeholder="Contoh: CNI20251234"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="md:col-span-1">
                    <label for="kontak_pesanan" class="block text-sm font-medium text-gray-700 mb-1">Email / No. Telepon</label>
                    <input type="text" id="kontak_pesanan" name="kontak" required placeholder="email@contoh.com atau 0812xxxxxx"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="md:col-span-1">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-300">
                        Cek Status Pesanan
                    </button>
                </div>
            </form>
            <p class="mt-4 text-sm text-gray-500">Masukkan kode booking dan informasi kontak untuk melihat status pesanan Anda.</p>
        </section>
    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
