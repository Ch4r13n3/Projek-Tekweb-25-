<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan Kamar</title>
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
        <h1 class="text-4xl font-extrabold text-gray-800 mb-8 text-center">Form Pemesanan Kamar</h1>
        
        <form action="konfirmasi_pembayaran.php" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8 p-6 bg-white rounded-xl shadow-lg">
                
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">1. Data Pemesan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="ktp" class="block text-sm font-medium text-gray-700">Nomor KTP</label>
                            <input type="text" id="ktp" name="ktp" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="telp" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                            <input type="tel" id="telp" name="telp" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">2. Penggunaan Fasilitas Tambahan (Add-on)</h2>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <input id="addon_breakfast" name="addon_breakfast" type="checkbox" value="200000" class="h-4 w-4 text-blue-600 border-gray-300 rounded mt-1">
                            <label for="addon_breakfast" class="ml-3 block text-sm font-medium text-gray-900">
                                Sarapan (Breakfast) - Rp 100.000 / Tamu / Malam (Total Biaya Estimasi: **Rp 400.000**) 
                            </label>
                        </div>
                        <div class="flex items-start">
                            <input id="addon_laundry" name="addon_laundry" type="checkbox" value="50000" class="h-4 w-4 text-blue-600 border-gray-300 rounded mt-1">
                            <label for="addon_laundry" class="ml-3 block text-sm font-medium text-gray-900">
                                Layanan Laundry Ekspres - Rp 50.000 (Biaya Tetap)
                            </label>
                        </div>
                    </div>
                    <input type="hidden" id="total_additional" name="total_additional" value="0">
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg transition duration-300 shadow-md">
                        Lanjut ke Pembayaran
                    </button>
                </div>
            </div>

            <div class="lg:col-span-1 p-6 bg-blue-100 rounded-xl shadow-lg h-fit sticky top-20">
                <h2 class="text-2xl font-bold text-blue-800 border-b border-blue-300 pb-3 mb-4">3. Rincian Pesanan Anda</h2>
                
                <div class="space-y-3 text-gray-700">
                    <p><strong>Tipe Kamar:</strong> Family Room</p>
                    <p><strong>Harga Kamar / Malam:</strong> Rp 850.000</p>
                    <p><strong>Tanggal Check-in:</strong> 01 Des 2025</p>
                    <p><strong>Tanggal Check-out:</strong> 03 Des 2025</p>
                    <p><strong>Lama Menginap:</strong> 2 Malam</p>
                    <p><strong>Jumlah Tamu:</strong> 2 Orang</p>

                    <div class="border-t border-blue-300 pt-3 mt-3">
                        <p class="flex justify-between font-semibold">
                            <span>Total Biaya Kamar (2 Malam):</span>
                            <span>Rp 1.700.000</span>
                        </p>
                        <p class="flex justify-between text-sm text-red-600">
                            <span>Total Biaya Tambahan (Estimasi):</span>
                            <span id="display_additional_cost">Rp 0</span>
                        </p>
                    </div>
                </div>

                <div class="border-t-2 border-blue-800 pt-4 mt-4">
                    <p class="flex justify-between text-xl font-extrabold text-blue-800">
                        <span>TOTAL BIAYA MENGINAP:</span>
                        <span id="display_grand_total">Rp 1.700.000</span>
                    </p>
                    <input type="hidden" id="grand_total" name="grand_total" value="1700000">
                </div>
            </div>
        </form>
    </main>


    <footer class="bg-gray-800 text-white py-10">
        <div class="container mx-auto text-center">
            <p>&copy; 2025 Cloud Nine In. Dibuat oleh Kelompok Tekweb.</p>
            <p class="text-gray-400 text-sm mt-2">Catherine, Charlene, Sheryl, & Rafa</p>
        </div>
    </footer>
    
    <script>
        // Total Kamar: 2 Malam x Rp 850.000 = Rp 1.700.000
        const roomCost = 1700000; 
        // Sarapan: 2 Tamu x 2 Malam x Rp 100.000 = Rp 400.000
        const breakfastPrice = 400000; 
        const laundryPrice = 50000; 
        
        const checkboxBreakfast = document.getElementById('addon_breakfast');
        const checkboxLaundry = document.getElementById('addon_laundry');
        const displayAdditionalCost = document.getElementById('display_additional_cost');
        const displayGrandTotal = document.getElementById('display_grand_total');
        const inputGrandTotal = document.getElementById('grand_total');
        const inputTotalAdditional = document.getElementById('total_additional');

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        function calculateTotal() {
            let totalAdditional = 0;
            if (checkboxBreakfast.checked) {
                totalAdditional += breakfastPrice;
            }
            if (checkboxLaundry.checked) {
                totalAdditional += laundryPrice;
            }

            const grandTotal = roomCost + totalAdditional;

            displayAdditionalCost.textContent = formatRupiah(totalAdditional);
            displayGrandTotal.textContent = formatRupiah(grandTotal);
            inputGrandTotal.value = grandTotal;
            inputTotalAdditional.value = totalAdditional;
            
            // Update value checkbox agar sesuai dengan harga saat terpilih
            checkboxBreakfast.value = checkboxBreakfast.checked ? breakfastPrice : 0;
            checkboxLaundry.value = checkboxLaundry.checked ? laundryPrice : 0;
        }

        checkboxBreakfast.addEventListener('change', calculateTotal);
        checkboxLaundry.addEventListener('change', calculateTotal);

        calculateTotal(); 
    </script>
</body>
</html>
