<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Nine In - Pesan Kamar Hotel Terbaik</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-blue-600">Cloud Nine In</a>
            <ul class="flex space-x-6 items-center">
                <li><a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium">Home</a></li>
                <li><a href="#populer" class="text-gray-700 hover:text-blue-600 font-medium">Kamar Populer</a></li>
                <li><a href="#cek-pesanan" class="text-gray-700 hover:text-blue-600 font-medium">Cek Pesanan Saya</a></li>
                <li>
                    <a href="login.php"
                    class="bg-[#134686] hover:bg-[#27548A] text-white font-bold py-2 px-4 rounded-lg transition duration-300">Login</a></li>
            </ul>
        </div>
    </nav>

    <header class="bg-blue-700 text-white py-24 px-6" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?fit=crop&w=1500&q=80'); background-blend-mode: multiply; background-color: rgba(0,0,50,0.6); background-size: cover; background-position: center;">
        <div class="container mx-auto text-center">
            <h1 class="text-5xl font-bold mb-4">Selamat Datang di Cloud Nine In</h1>
            <p class="text-xl mb-10">Temukan kamar impian Anda dengan harga terbaik.</p>

            <form action="guest/hasil_pencarian.php" method="GET" 
                 class="bg-white text-black p-6 rounded-lg shadow-xl max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                
                <div class="text-left">
                    <label for="check_in" class="block text-sm font-medium text-gray-700">Check-in</label>
                    <input type="date" id="check_in" name="check_in" value="<?php echo date('Y-m-d'); ?>" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" required>
                </div>
                <div class="text-left">
                    <label for="check_out" class="block text-sm font-medium text-gray-700">Check-out</label>
                    <input type="date" id="check_out" name="check_out" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" required>
                </div>
                <div class="text-left">
                    <label for="jumlah_tamu" class="block text-sm font-medium text-gray-700">Jumlah Tamu</label>
                    <input type="number" id="jumlah_tamu" name="jumlah_tamu" min="1" value="1" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2" required>
                </div>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg w-full h-11 transition duration-300">
                    Cari Kamar
                </button>
            </form>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        
        <section id="populer" class="py-16">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-8 text-center">
                <span class="text-blue-600">Kamar Populer</span> ✨ Pilihan Terbaik Kami
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="https://via.placeholder.com/600x400/38bdf8/ffffff?text=Family+Room" alt="Family Room" class="w-full h-48 object-cover">
                    <div class="p-5">
                        <h3 class="text-2xl font-semibold mb-2 text-gray-900">Family Room</h3>
                        <p class="text-gray-600 mb-4">Kamar luas ideal untuk keluarga, dilengkapi 1 ranjang besar dan 2 ranjang single.</p>
                        <div class="flex justify-between items-center mt-4">
                             <span class="text-xl font-bold text-red-600">Rp 850.000 / malam</span>
                            <a href="guest/pemesanan.php?tipe=Family" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition duration-300">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="https://via.placeholder.com/600x400/34d399/ffffff?text=Double-Bed+Room" alt="Double-bed Room" class="w-full h-48 object-cover">
                    <div class="p-5">
                        <h3 class="text-2xl font-semibold mb-2 text-gray-900">Double-bed Room</h3>
                        <p class="text-gray-600 mb-4">Kenyamanan maksimal dengan satu ranjang berukuran besar, ideal untuk pasangan.</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-red-600">Rp 550.000 / malam</span>
                            <a href="guest/pemesanan.php?tipe=Double" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition duration-300">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="https://via.placeholder.com/600x400/a78bfa/ffffff?text=Single+Bed+Room" alt="Single Bed Room" class="w-full h-48 object-cover">
                    <div class="p-5">
                        <h3 class="text-2xl font-semibold mb-2 text-gray-900">Single Bed Room</h3>
                        <p class="text-gray-600 mb-4">Pilihan ekonomis untuk perjalanan solo dengan satu ranjang single.</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-red-600">Rp 300.000 / malam</span>
                            <a href="guest/pemesanan.php?tipe=Single" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg text-sm transition duration-300">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <section id="cek-pesanan" class="py-20 bg-white rounded-xl shadow-lg">
            <div class="container mx-auto px-6 max-w-4xl">
                <h2 class="text-3xl font-extrabold text-gray-800 mb-6 text-center flex items-center justify-center">
                    <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Cek Status Pesanan Anda
                </h2>
                <p class="text-gray-600 mb-8 text-center">Masukkan kode booking dan email/nomor telepon yang terdaftar untuk melihat detail reservasi Anda saat ini.</p>
                
                <form action="guest/cek_pesanan_detail.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end p-6 bg-blue-50 rounded-lg shadow-inner">
                    
                    <div>
                        <label for="kode_booking" class="block text-sm font-medium text-gray-700 mb-1">Kode Booking</label>
                        <input type="text" id="kode_booking" name="kode" required placeholder="Contoh: CNI20251234"
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="kontak_pesanan" class="block text-sm font-medium text-gray-700 mb-1">Email / No. Telepon</label>
                        <input type="text" id="kontak_pesanan" name="kontak" required placeholder="email@contoh.com atau 0812xxxxxx"
                            class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div class="md:col-span-1">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-md shadow-md transition duration-300">
                            Cek Sekarang
                        </button>
                    </div>

                </form>
            </div>
        </section>

    </main>

    <footer class="bg-gray-800 text-white py-10 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
            <p class="text-gray-400 text-sm mt-2">Dibuat oleh Kelompok Tekweb.</p>
        </div>
    </footer>

</body>
</html>