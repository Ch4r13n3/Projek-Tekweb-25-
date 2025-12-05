<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil Dibuat</title>
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
        <div class="max-w-xl mx-auto p-8 bg-white rounded-xl shadow-2xl text-center border-t-8 border-green-500">
            <svg class="w-20 h-20 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h1 class="text-3xl font-extrabold text-gray-800 mb-2">PESANAN BERHASIL DIBUAT!</h1>
            <p class="text-gray-600 mb-6">Kami telah menerima pesanan Anda. Status pesanan Anda saat ini adalah **Pending** (Menunggu Konfirmasi Pembayaran).</p>
            
            <div class="bg-yellow-50 p-6 rounded-lg mb-6 border border-yellow-300">
                <p class="text-sm font-semibold text-yellow-800">SIMPAN KODE INI BAIK-BAIK:</p>
                <p id="kode_booking_display" class="text-5xl font-extrabold text-blue-600 mt-2 tracking-wider">CNI20254321</p>
                <p class="text-sm text-gray-500 mt-2">Kode Booking ini diperlukan untuk Check-in dan Cek Status Pesanan.</p>
            </div>

            <div class="space-y-3 text-left p-4 bg-gray-50 rounded-lg">
                <p class="font-bold text-gray-700">Rincian Status:</p>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    <li><strong>Status Pembayaran:</strong> Pending (Bukti transfer sedang diverifikasi/Menunggu pembayaran di hotel)</li>
                    <li><strong>Tipe Kamar:</strong> Family Room (2 Malam)</li>
                    <li><strong>Total Pembayaran:</strong> Rp 2.150.000</li>
                </ul>
            </div>
            
            <a href="cek_pesanan_detail.php?kode=CNI20254321" class="mt-8 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg">
                Cek Detail Pesanan Sekarang
            </a>
            <p class="mt-4 text-sm text-gray-500">Email konfirmasi telah dikirim ke alamat email Anda.</p>
        </div>

    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
