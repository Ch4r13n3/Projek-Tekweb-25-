<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian Kamar</title>
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
                <li>
                    <a href="login.php"
                    class="bg-[#134686] hover:bg-[#27548A] text-white font-bold py-2 px-4 rounded-lg transition duration-300">Login</a></li>
            </ul>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-6">Kamar Tersedia</h1>
        <p class="text-gray-600 mb-8">Hasil untuk Check-in: 01 Des 2025 | Check-out: 03 Des 2025 | Tamu: 2 Orang</p>
        
        <div class="space-y-6">
            
            <div class="bg-white rounded-xl shadow-lg flex flex-col md:flex-row overflow-hidden">
                <img src="https://via.placeholder.com/300x200/38bdf8/ffffff?text=Family+Room" alt="Family Room" class="w-full md:w-1/3 h-64 md:h-auto object-cover">
                <div class="p-6 flex-1 flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Family Room</h2>
                        <p class="text-sm text-gray-600 mt-1 mb-3">Kapasitas: Maks. 4 Orang | Ranjang: 1 Double & 2 Single</p>
                        <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                            <li>Free WiFi & AC</li>
                            <li>Kamar Mandi Pribadi dengan Air Panas</li>
                            <li>Sarapan Tersedia (Add-on)</li>
                        </ul>
                    </div>
                    <div class="text-right">
                        <span class="text-sm text-gray-500 block">Harga / Malam</span>
                        <p class="text-3xl font-extrabold text-red-600 mb-4">Rp 850.000</p>
                        <a href="pemesanan.php?tipe=Family" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md">Pesan Sekarang</a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg flex flex-col md:flex-row overflow-hidden">
                <img src="https://via.placeholder.com/300x200/34d399/ffffff?text=Double-Bed+Room" alt="Double-bed Room" class="w-full md:w-1/3 h-64 md:h-auto object-cover">
                <div class="p-6 flex-1 flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Double-bed Room</h2>
                        <p class="text-sm text-gray-600 mt-1 mb-3">Kapasitas: Maks. 2 Orang | Ranjang: 1 Queen Size</p>
                        <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                            <li>Free WiFi & AC</li>
                            <li>View Kota Terbaik</li>
                            <li>Gratis Akses Kolam Renang</li>
                        </ul>
                    </div>
                    <div class="text-right">
                        <span class="text-sm text-gray-500 block">Harga / Malam</span>
                        <p class="text-3xl font-extrabold text-red-600 mb-4">Rp 550.000</p>
                        <a href="pemesanan.php?tipe=Double" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md">Pesan Sekarang</a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg flex flex-col md:flex-row overflow-hidden">
                <img src="https://via.placeholder.com/300x200/fcd34d/ffffff?text=Single+Bed+Room" alt="Single Bed Room" class="w-full md:w-1/3 h-64 md:h-auto object-cover">
                <div class="p-6 flex-1 flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Single Bed Room</h2>
                        <p class="text-sm text-gray-600 mt-1 mb-3">Kapasitas: Maks. 1 Orang | Ranjang: 1 Single</p>
                        <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                            <li>Free WiFi & AC</li>
                            <li>Meja Kerja</li>
                        </ul>
                    </div>
                    <div class="text-right">
                        <span class="text-sm text-gray-500 block">Harga / Malam</span>
                        <p class="text-3xl font-extrabold text-red-600 mb-4">Rp 300.000</p>
                        <a href="pemesanan.php?tipe=Single" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md">Pesan Sekarang</a>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
