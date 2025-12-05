<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Nine In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php"class="text-2xl font-bold text-blue-600">Cloud Nine In</a>
                <ul class="flex space-x-6 items center">
                    <li><a href="indexGuest.php" class="text-gray-700 hover:text-blue-600 font-medium">Home</a></li>
                    <li><a href="#populer">Kamar Populer</a></li>
                    <li><a href="#cek-pesanan">Cek Pesanan</a></li>
                    <li>
                        <a href="../login.php"
                        class="bg-[#134686] hover:bg-[#27548A] text-white font-bold py-2 px-4 rounded-lg transition duration-300">Login</a></li>
                </ul>
        </div>
    </nav>
    
    <header class="bg-blue-700 text-white py-24 px-6" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?fit=crop&w=1500&q=80'); background-blend-mode: multiply; background-color: rgba(0,0,50,0.6); background-size: cover; background-position: center;">
        <div class="container mx-auto text-center">
            <h1 class="text-5xl font-bold mb-4">Selamat Datang di Cloud Nine In</h1>
            <p class="text-xl mb-10">Temukan kamar impian Anda dengan harga terbaik.</p>

            <form action="hasil_pencarian.php" method="GET" 
                  class="bg-white text-black p-6 rounded-lg shadow-xl max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                
                <div class="text-left">
                    <label for="checkin" class="block text-sm font-medium text-gray-700">Check-in</label>
                    <input type="date" id="checkin" name="checkin" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div class="text-left">
                    <label for="checkout" class="block text-sm font-medium text-gray-700">Check-out</label>
                    <input type="date" id="checkout" name="checkout" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <div class="text-left">
                    <label for="tamu" class="block text-sm font-medium text-gray-700">Jumlah Tamu</label>
                    <input type="number" id="tamu" name="tamu" min="1" value="1" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg w-full h-11">
                    Cari Kamar
                </button>
            </form>
        </div>
    </header>

    <section id="popular" class="py-16">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-10">Kamar Populer Kami</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="https://via.placeholder.com/400x250/C0C0C0/FFFFFF?text=Family+Room" alt="Family Room" class="w-full h-56 object-cover">
                    <div class="p-5">
                        <h3 class="text-2xl font-semibold mb-2">Family Room</h3>
                        <p class="text-gray-600 mb-4">Kamar luas dengan 2 double bed, cocok untuk liburan keluarga Anda.</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-blue-600">Rp 1.500.000</span>
                            <a href="pemesanan.php?tipe=Family" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="https://via.placeholder.com/400x250/C0C0C0/FFFFFF?text=Double+Bed" alt="Double Bed" class="w-full h-56 object-cover">
                    <div class="p-5">
                        <h3 class="text-2xl font-semibold mb-2">Double Bed</h3>
                        <p class="text-gray-600 mb-4">Kamar nyaman dengan pemandangan kota, ideal untuk pasangan.</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-blue-600">Rp 800.000</span>
                            <a href="pemesanan.php?tipe=Double" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Lihat Detail</a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition duration-300">
                    <img src="https://via.placeholder.com/400x250/C0C0C0/FFFFFF?text=Single+Bed" alt="Single Bed" class="w-full h-56 object-cover">
                    <div class="p-5">
                        <h3 class="text-2xl font-semibold mb-2">Single Bed</h3>
                        <p class="text-gray-600 mb-4">Efisien dan modern, sempurna untuk solo traveler atau pebisnis.</p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-xl font-bold text-blue-600">Rp 600.000</span>
                            <a href="pemesanan.php?tipe=Single" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="cek-pesanan" class="py-20 bg-blue-50">
        <div class="container mx-auto px-6 max-w-2xl text-center">
            <h2 class="text-3xl font-bold mb-6">Cek Status Pesanan Anda</h2>
            <p class="text-gray-600 mb-8">Masukkan kode booking dan email Anda untuk melihat detail reservasi Anda saat ini.</p>
            
            <form action="cek_pesanan_detail.php" method="POST" class="flex flex-col md:flex-row gap-4">
                <div class="md:col-span-1">
                    <label for="kode_booking" class="block text-sm font-medium text-gray-700 mb-1 text-left">Kode Booking</label>
                    <input type="text" name="kode_booking" placeholder="Contoh: CNI20251234" 
                        class="flex-1 p-3 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="md:col-span-1">
                    <label for="kode_booking" class="block text-sm font-medium text-gray-700 mb-1 text-left">Email</label>
                    <input type="email" name="email" placeholder="email@contoh.com" 
                        class="flex-1 p-3 border rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="md:col-span-1">
                <button type="submit" 
                        class="bg-green-500 hover:bg-green-600 text-white align-center font-bold py-3 px-6 rounded-md transition duration-300">
                    Cek Sekarang
                </button>
                </div>
            </form>
        </div>
    </section>

    <footer class="bg-gray-800 text-white py-10">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Cloud Nine In. Dibuat oleh Kelompok Tekweb.</p>
            <p class="text-gray-400 text-sm mt-2">Catherine, Charlene, Sheryl, & Rafa</p>
        </div>
    </footer>
</body>
</html>