<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan</title>
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
        <h1 class="text-4xl font-extrabold text-gray-800 mb-6 text-center">Detail Pesanan Kamar</h1>
        
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-xl">
            
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <div>
                    <p class="text-sm text-gray-500">Kode Booking</p>
                    <p class="text-3xl font-extrabold text-blue-600">CNI20254321</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Status Saat Ini</p>
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-lg font-bold bg-green-100 text-green-800">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        CONFIRMED
                    </span>
                </div>
            </div>

            <div class="space-y-6">
                <h2 class="text-2xl font-bold text-gray-800">Rincian Reservasi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                    <div>
                        <p class="font-semibold">Check-in</p>
                        <p>Senin, 01 Desember 2025</p>
                    </div>
                    <div>
                        <p class="font-semibold">Check-out</p>
                        <p>Rabu, 03 Desember 2025</p>
                    </div>
                    <div>
                        <p class="font-semibold">Lama Menginap</p>
                        <p>2 Malam</p>
                    </div>
                    <div>
                        <p class="font-semibold">Tipe Kamar</p>
                        <p>Family Room</p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 pt-4 border-t">Detail Tamu</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                    <div>
                        <p class="font-semibold">Nama Pemesan</p>
                        <p>Budi Santoso</p>
                    </div>
                    <div>
                        <p class="font-semibold">Nomor KTP</p>
                        <p>3276xxxxxxxxxxxxxx</p>
                    </div>
                    <div>
                        <p class="font-semibold">Kontak (Email/Telp)</p>
                        <p>budi.s@mail.com | 081234567890</p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-gray-800 pt-4 border-t">Rincian Biaya</h2>
                <div class="space-y-2 text-gray-700">
                    <div class="flex justify-between">
                        <span>Biaya Kamar (2 x Rp 850.000)</span>
                        <span>Rp 1.700.000</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tambahan Sarapan (Add-on)</span>
                        <span>Rp 200.000</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 font-bold text-lg text-red-600">
                        <span>TOTAL PEMBAYARAN</span>
                        <span>Rp 1.900.000</span>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <p class="font-semibold text-gray-800">Metode Pembayaran:</p>
                    <p class="text-blue-600 font-medium">Transfer Bank (Lunas)</p>
                </div>

            </div>
            
            <div class="mt-8 text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-700">Silakan tunjukkan halaman ini (atau email konfirmasi) saat Check-in.</p>
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