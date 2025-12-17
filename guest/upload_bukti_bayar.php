<?php
// guest/upload_bukti_bayar.php
session_start();
// Sesuaikan path koneksi
require '../koneksi.php'; 

// Sesuaikan folder tempat bukti pembayaran akan disimpan
// PASTIKAN FOLDER INI ADA di root project (misalnya, di luar folder guest)
$target_dir = "../uploads/bukti_bayar/";

// Pastikan folder target ada dan dapat ditulis
if (!is_dir($target_dir)) {
    // Mencoba membuat direktori dengan izin penuh (0777)
    // Jika gagal, akan ada error.
    if (!mkdir($target_dir, 0777, true)) {
        // Jika mkdir gagal, set error untuk ditampilkan
        die('FATAL ERROR: Gagal membuat direktori upload. Hubungi administrator.');
    }
}

// Ambil kode booking dan kontak (untuk keamanan)
$kode_booking = $_GET['kode'] ?? null;
$kontak = $_GET['kontak'] ?? null; 
$reservasi = null;
$error_message = null;
$success_message = null;

if (!$kode_booking || !$kontak) {
    $error_message = "Kode Booking dan Kontak Pemesan harus diisi.";
} else {
    // 1. Ambil data reservasi
    // Memverifikasi KODE BOOKING DAN KONTAK
    // Menggunakan email_pemesan = ? OR telp_pemesan = ? untuk verifikasi kontak
    $query = "SELECT kode_booking, status_pembayaran, metode_pembayaran, email_pemesan, telp_pemesan 
              FROM reservasi 
              WHERE kode_booking = ? AND (email_pemesan = ? OR telp_pemesan = ?)";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        $error_message = "Database Error: " . $conn->error;
    } else {
        // Tiga parameter string (kode_booking, kontak, kontak)
        $stmt->bind_param("sss", $kode_booking, $kontak, $kontak); 
        $stmt->execute();
        $result = $stmt->get_result();
        $reservasi = $result->fetch_assoc();
        $stmt->close();

        if (!$reservasi) {
            $error_message = "Data pemesanan tidak ditemukan atau kontak tidak cocok.";
        } elseif ($reservasi['metode_pembayaran'] !== 'Transfer Bank') {
            $error_message = "Metode pembayaran pemesanan ini adalah **" . htmlspecialchars($reservasi['metode_pembayaran']) . "**, tidak memerlukan upload bukti transfer.";
        } elseif ($reservasi['status_pembayaran'] !== 'Belum Bayar') {
            $error_message = "Pembayaran untuk pesanan ini sudah berstatus: **" . htmlspecialchars($reservasi['status_pembayaran']) . "**.";
        }
    }
}

// --- LOGIKA UPLOAD FILE (HANYA JIKA ADA POST DAN TIDAK ADA ERROR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error_message && $reservasi) {
    // Cek apakah ada file yang diupload dan tidak ada error pada upload PHP
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] === UPLOAD_ERR_OK) {
        
        $file_name = basename($_FILES["bukti_bayar"]["name"]);
        $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_size = $_FILES["bukti_bayar"]["size"];
        
        // Buat nama file unik: [KODE BOOKING]_[TIMESTAMP].[EKSTENSI]
        $new_file_name = $reservasi['kode_booking'] . '_' . time() . '.' . $file_type;
        $target_file = $target_dir . $new_file_name;
        $upload_ok = 1;

        // 1. Cek ekstensi file (keamanan)
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array(strtolower($file_type), $allowed_types)) {
            $error_message = "Maaf, hanya file JPG, JPEG, PNG, dan PDF yang diperbolehkan.";
            $upload_ok = 0;
        }

        // 2. Cek ukuran file (maks 5MB)
        if ($file_size > 5000000) { 
            $error_message = "Maaf, ukuran file terlalu besar (Maksimal 5MB).";
            $upload_ok = 0;
        }

        // 3. Lakukan upload dan update database
        if ($upload_ok) {
            if (move_uploaded_file($_FILES["bukti_bayar"]["tmp_name"], $target_file)) {
                
                // UPDATE database menggunakan prepared statement
                $query_update = "UPDATE reservasi 
                                 SET status_pembayaran = 'Menunggu Verifikasi', 
                                     bukti_pembayaran = ? 
                                 WHERE kode_booking = ?";
                
                $stmt_update = $conn->prepare($query_update);
                // Bind parameter: ss (string, string)
                $stmt_update->bind_param("ss", $new_file_name, $reservasi['kode_booking']);
                
                if ($stmt_update->execute()) {
                    // Berhasil, redirect ke halaman detail dengan status terbaru (PRG Pattern)
                    $_SESSION['success_message'] = "Bukti pembayaran berhasil diunggah! Status pesanan Anda sekarang 'Menunggu Verifikasi'.";
                    header("Location: cek_pesanan_detail.php?kode=" . urlencode($reservasi['kode_booking']) . "&kontak=" . urlencode($kontak));
                    exit;
                } else {
                    $error_message = "Gagal memperbarui database: " . $stmt_update->error;
                    // Jika update DB gagal, hapus file yang sudah terlanjur terupload
                    @unlink($target_file); // Menggunakan @ untuk menekan warning jika unlink gagal
                }
                $stmt_update->close();
            } else {
                $error_message = "Terjadi kesalahan saat mengunggah file. Kode error: " . $_FILES["bukti_bayar"]["error"];
            }
        }
    } elseif (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] !== UPLOAD_ERR_NO_FILE) {
         // Catch all other PHP upload errors
         $error_message = "Terjadi kesalahan pada file upload: Error code " . $_FILES['bukti_bayar']['error'];
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $error_message = "Anda belum memilih file bukti pembayaran.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran</title>
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
        <div class="max-w-md mx-auto">
            
            <div class="p-8 bg-white rounded-xl shadow-2xl border-t-8 border-blue-600">
                <h1 class="text-2xl font-extrabold text-gray-800 mb-6 text-center">Upload Bukti Transfer</h1>
                
                <?php if ($error_message): ?>
                    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-300 font-medium" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php 
                // Hanya tampilkan form jika reservasi ditemukan, metode transfer, dan belum bayar
                if ($reservasi && $reservasi['metode_pembayaran'] === 'Transfer Bank' && $reservasi['status_pembayaran'] === 'Belum Bayar'): 
                ?>
                
                    <div class="bg-blue-50 p-4 rounded-lg mb-6 border border-blue-300">
                        <p class="text-sm font-semibold text-blue-800">Kode Booking:</p>
                        <p class="text-2xl font-extrabold text-blue-600"><?php echo htmlspecialchars($kode_booking); ?></p>
                        <p class="mt-2 text-sm text-gray-600">Mohon unggah bukti transfer Anda agar pesanan dapat segera diverifikasi oleh tim kami.</p>
                        <p class="mt-4 text-xs font-semibold text-gray-700">REKENING TRANSFER:</p>
                        <p class="text-sm text-gray-800">Bank Contoh | A/N Cloud Nine In | **1234 5678 9012**</p>
                    </div>

                    <form action="upload_bukti_bayar.php?kode=<?php echo urlencode($kode_booking); ?>&kontak=<?php echo urlencode($kontak); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label for="bukti_bayar" class="block text-sm font-medium text-gray-700">Pilih File Bukti Pembayaran (JPG, PNG, PDF, Max 5MB):</label>
                            <input type="file" name="bukti_bayar" id="bukti_bayar" required accept=".jpg,.jpeg,.png,.pdf"
                                class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition duration-300 shadow-lg">
                            Upload & Konfirmasi Pembayaran
                        </button>
                    </form>

                <?php elseif ($reservasi): ?>
                    <div class="text-center p-6 bg-green-50 rounded-lg border border-green-300">
                        <p class="text-xl font-bold text-green-700">Status Pembayaran Saat Ini:</p>
                        <p class="text-2xl font-extrabold text-green-600 mt-2"><?php echo htmlspecialchars($reservasi['status_pembayaran']); ?></p>
                        <p class="mt-4 text-sm text-gray-600">Anda dapat melihat detail pesanan:</p>
                        <a href="cek_pesanan_detail.php?kode=<?php echo urlencode($kode_booking); ?>&kontak=<?php echo urlencode($kontak); ?>" 
                            class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                            Lihat Detail Pesanan
                        </a>
                    </div>
                <?php endif; ?>
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