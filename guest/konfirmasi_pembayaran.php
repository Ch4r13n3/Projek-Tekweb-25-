<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran</title>
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
        <h1 class="text-4xl font-extrabold text-gray-800 mb-8 text-center">3. Pilih Metode Pembayaran</h1>
        
        <div class="max-w-4xl mx-auto">
            <div class="bg-red-50 p-6 rounded-xl shadow-md mb-8 border-l-4 border-red-500">
                <p class="text-xl font-bold text-red-700">TOTAL YANG HARUS DIBAYAR</p>
                <p class="text-5xl font-extrabold text-red-600 mt-2">Rp 2.150.000</p>
                <p class="text-sm text-gray-600 mt-2">Pembayaran harus dilakukan dalam waktu 1x24 jam untuk mengamankan pesanan Anda (kecuali Bayar di Hotel).</p>
            </div>
            
            <form action="konfirmasi_pemesanan.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="total_bayar" value="2150000">

                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Metode Pembayaran</h2>
                    
                    <div class="space-y-6">

                        <div class="border p-4 rounded-lg cursor-pointer transition duration-300 hover:bg-blue-50">
                            <label class="flex items-center space-x-3">
                                <input type="radio" name="metode_pembayaran" value="Transfer Bank" id="metode_transfer" checked class="form-radio h-5 w-5 text-blue-600">
                                <span class="text-lg font-semibold text-gray-900">Transfer Bank</span>
                            </label>
                            
                            <div id="detail_transfer" class="mt-4 p-4 bg-gray-50 border-t rounded-b-lg space-y-3">
                                <p class="font-bold text-blue-700">Instruksi:</p>
                                <ol class="list-decimal list-inside text-sm space-y-1">
                                    <li>Transfer total biaya ke nomor rekening di bawah ini.</li>
                                    <li>Pastikan jumlah yang ditransfer sesuai (Rp 2.150.000).</li>
                                    <li>Unggah bukti transfer Anda di form di bawah ini.</li>
                                </ol>
                                <div class="bg-yellow-100 p-3 rounded-md font-mono text-sm">
                                    <p><strong>Bank:</strong> BCA</p>
                                    <p><strong>No. Rekening:</strong> 123-456-7890</p>
                                    <p><strong>Atas Nama:</strong> PT. Cloud Nine In</p>
                                </div>
                                <div class="mt-4">
                                    <label for="bukti_transfer" class="block text-sm font-medium text-gray-700">Unggah Bukti Pembayaran (JPG/PNG)</label>
                                    <input type="file" id="bukti_transfer" name="bukti_transfer" accept=".jpg, .jpeg, .png" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                            </div>
                        </div>

                        <div class="border p-4 rounded-lg cursor-pointer transition duration-300 hover:bg-blue-50">
                            <label class="flex items-center space-x-3">
                                <input type="radio" name="metode_pembayaran" value="Bayar di Hotel" id="metode_cash" class="form-radio h-5 w-5 text-blue-600">
                                <span class="text-lg font-semibold text-gray-900">Bayar di Hotel (Cash/Debit)</span>
                            </label>
                            
                            <div id="detail_cash" class="mt-4 p-4 bg-gray-50 border-t rounded-b-lg space-y-3 hidden">
                                <p class="font-bold text-blue-700">Instruksi:</p>
                                <p class="text-sm">Anda dapat melakukan pembayaran tunai atau debit saat proses Check-in di Resepsionis. Pesanan Anda akan berstatus **Pending** dan akan dikonfirmasi setelah pembayaran lunas.</p>
                                <p class="text-red-500 font-medium text-sm">Penting: Ketersediaan kamar hanya dijamin hingga 2 jam sebelum waktu check-in.</p>
                            </div>
                        </div>

                        <div class="border p-4 rounded-lg cursor-not-allowed bg-gray-200">
                            <label class="flex items-center space-x-3">
                                <input type="radio" name="metode_pembayaran" value="Kartu" id="metode_card" disabled class="form-radio h-5 w-5 text-gray-400">
                                <span class="text-lg font-semibold text-gray-500">Debit / Credit Card (Segera Hadir)</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-lg transition duration-300 shadow-md">
                        Selesaikan Pemesanan
                    </button>
                </div>
            </form>
        </div>

    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const transferRadio = document.getElementById('metode_transfer');
        const cashRadio = document.getElementById('metode_cash');
        const detailTransfer = document.getElementById('detail_transfer');
        const detailCash = document.getElementById('detail_cash');
        const buktiTransferInput = document.getElementById('bukti_transfer');

        function toggleDetails() {
            if (transferRadio.checked) {
                detailTransfer.classList.remove('hidden');
                buktiTransferInput.required = true;
                detailCash.classList.add('hidden');
            } else if (cashRadio.checked) {
                detailTransfer.classList.add('hidden');
                buktiTransferInput.required = false;
                detailCash.classList.remove('hidden');
            }
        }

        transferRadio.addEventListener('change', toggleDetails);
        cashRadio.addEventListener('change', toggleDetails);

        toggleDetails();
    </script>
</body>
</html>
