<?php
session_start();
// Path ke koneksi.php disesuaikan dengan struktur folder (asumsi /guest/konfirmasi.php)
require '../koneksi.php'; 

// --- BAGIAN KRITIS: PENGECEKAN SESSION & REDIRECT ---
$id_user = $_SESSION['id_user'] ?? NULL;
$error_message = null; // Inisialisasi variabel error

// Fungsi format Rupiah (dipindahkan ke atas agar bisa dipakai di bagian manapun)
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// --- BAGIAN KRITIS: LOGIKA INSERT RESERVASI (JIKA FORM DISUBMIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data dari form pemesanan.php
    $tipe_id = $_POST['tipe_id'] ?? null;
    $checkin = $_POST['checkin'] ?? null;
    $checkout = $_POST['checkout'] ?? null;
    $lama = (int)($_POST['lama'] ?? 1);
    $nama_pemesan = trim($_POST['nama_pemesan'] ?? '');
    $ktp_pemesan = trim($_POST['ktp_pemesan'] ?? '');
    $email_pemesan = trim($_POST['email_pemesan'] ?? '');
    $telp_pemesan  = trim($_POST['telp_pemesan'] ?? '');
    $jumlah_kamar  = (int)($_POST['jumlah_kamar'] ?? 1);

    // Mengambil metode pembayaran dari POST (Asumsi form telah diperbarui)
    // Jika tidak ada di POST, defaultkan ke 'Transfer Bank' agar alur pembayaran lunas bisa dipicu.
    $metode_pembayaran_post = $_POST['metode_pembayaran'] ?? 'Transfer Bank';
    
    // Validasi data dasar sebelum melanjutkan
    if (empty($tipe_id) || empty($checkin) || empty($checkout) || $jumlah_kamar <= 0) {
        $error_message = "Data pemesanan tidak lengkap atau tidak valid.";
    }
    
    // Asumsi: Metode pembayaran dipilih di form (jika belum ada, set default)
    $metode_pembayaran = $metode_pembayaran_post;
    $status_reservasi = 'Pending';
    $status_pembayaran = 'Belum Bayar';

    // 2. Ambil harga kamar dari DB untuk perhitungan
    if (!$error_message) {
        $stmt_harga = $conn->prepare("SELECT harga_per_malam FROM tipe_kamar WHERE id_tipe_kamar = ?");
        $stmt_harga->bind_param("i", $tipe_id);
        $stmt_harga->execute();
        $result_harga = $stmt_harga->get_result();
        $data_harga = $result_harga->fetch_assoc();
        $stmt_harga->close();

        if ($data_harga) {
        // --- START LOGIKA PERHITUNGAN (SETELAH PENGHAPUSAN IF GANDA) ---
            $harga_per_malam = $data_harga['harga_per_malam'];

            // Hitung jumlah malam (days difference)
            $tanggal_masuk = new DateTime($checkin);
            $tanggal_keluar = new DateTime($checkout);
            $interval = $tanggal_masuk->diff($tanggal_keluar);
            $jumlah_malam = $interval->days;
            
            // Jika durasi 0 hari (checkin = checkout), set minimal 1
            if ($jumlah_malam < 1) {
                $jumlah_malam = 1;
            }

            // 🔥 PERBAIKAN KRITIS: Hitung Total Bayar
        
            $total_bayar = ($harga_per_malam * $jumlah_malam * $jumlah_kamar);
            do{
                // Format: CNI YYMMDD 5-digit Random (misal: CNI251216-12345)
                $random_number = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $kode_booking_baru = 'CNI' . date('ymd') . '-' . $random_number;

                // Cek keunikan di database
                $check_stmt = $conn->prepare("SELECT 1 FROM reservasi WHERE kode_booking = ?");
                $check_stmt->bind_param("s", $kode_booking_baru);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $kode_exists = $check_result->num_rows > 0;
                $check_stmt->close();
            } while ($kode_exists); // Ulangi jika kode sudah ada

            // 4. INSERT Data ke Database
            $query_insert = "
                INSERT INTO reservasi (id_user, kode_booking, nama_pemesan, ktp_pemesan, email_pemesan, 
                                        telp_pemesan, id_tipe_kamar, jumlah_tamu, tanggal_checkin, 
                                        tanggal_checkout, total_bayar, status_reservasi, metode_pembayaran, 
                                        status_pembayaran) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_insert = $conn->prepare($query_insert);
            // Tipe bind: i=integer, s=string, d=double/float (untuk total_bayar)
            $stmt_insert->bind_param("isssssiissdsss", 
                $id_user, $kode_booking_baru, $nama_pemesan, $ktp_pemesan,
                $email_pemesan, $telp_pemesan, $tipe_id,
                $jumlah_kamar, // Menggunakan variabel yang sama untuk jumlah kamar
                $checkin, $checkout, $total_bayar, $status_reservasi,
                $metode_pembayaran, // Menggunakan metode pembayaran dari POST/DEFAULT
                $status_pembayaran
            );

            if ($stmt_insert->execute()) {
                // Simpan kode booking ke sesi dan alihkan (PRG Pattern)
                $_SESSION['kode_booking'] = $kode_booking_baru;
                header("Location: konfirmasi.php?kode=" . $kode_booking_baru);
                exit;
            } else {
                // Tampilkan error DB (Hanya untuk debugging)
                $db_error_detail = $stmt_insert->error;
                $error_message = "Terjadi kesalahan fatal saat menyimpan pesanan: Gagal INSERT DB. Detail: " . $db_error_detail;
            }
            $stmt_insert->close();
        } else {
            $error_message = "Tipe kamar yang dipilih tidak valid atau tidak ditemukan.";
        }
    }
}


// Ambil kode booking dari URL atau session (jika sudah berhasil INSERT)
$kode_booking = $_GET['kode'] ?? $_SESSION['kode_booking'] ?? null;

if (!$kode_booking) {
    // Jika tidak ada kode dan tidak ada POST, kembalikan ke beranda
    header("Location: index.php");
    exit;
}

// Ambil data reservasi dari database
$query = "SELECT r.*, tk.nama_tipe, tk.harga_per_malam 
          FROM reservasi r 
          JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar 
          WHERE r.kode_booking = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $kode_booking);
$stmt->execute();
$result = $stmt->get_result();
$reservasi = $result->fetch_assoc();
$stmt->close();

if (!$reservasi) {
    // Jika reservasi tidak ditemukan (setelah redirect), tampilkan error
    $error_message = "Data pemesanan tidak ditemukan.";
}

// Hapus session kode_booking agar tidak mengganggu pemesanan berikutnya
if (isset($_SESSION['kode_booking'])) {
    unset($_SESSION['kode_booking']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pemesanan</title>
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
        <?php if ($error_message): // Menggunakan $error_message sebagai boolean check ?>
            <div class="max-w-xl mx-auto p-8 bg-white rounded-xl shadow-2xl text-center border-t-8 border-red-500">
                <h1 class="text-3xl font-extrabold text-gray-800 mb-4">PEMESANAN GAGAL!</h1>
                <p class="text-red-700 mb-6 font-medium"><?php echo htmlspecialchars($error_message); ?></p>
                <a href="index.php" class="mt-8 inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg">Kembali ke Beranda</a>
            </div>
        <?php elseif ($reservasi): ?>
            <?php 
            // Amankan data untuk URL
            $kode = urlencode($reservasi['kode_booking']);
            $kontak_url = urlencode($reservasi['email_pemesan']); 
            ?>
            <div class="max-w-xl mx-auto p-8 bg-white rounded-xl shadow-2xl text-center border-t-8 border-green-500">
                <svg class="w-20 h-20 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h1 class="text-3xl font-extrabold text-gray-800 mb-2">PESANAN BERHASIL DIBUAT!</h1>
                
                <div class="bg-yellow-50 p-6 rounded-lg mb-6 border border-yellow-300">
                    <p class="text-sm font-semibold text-yellow-800">SIMPAN KODE INI BAIK-BAIK:</p>
                    <p class="text-5xl font-extrabold text-blue-600 mt-2 tracking-wider"><?php echo htmlspecialchars($reservasi['kode_booking']); ?></p>
                    <p class="text-sm text-gray-500 mt-2">Kode Booking ini diperlukan untuk Check-in dan Cek Status Pesanan.</p>
                </div>

                <div class="space-y-3 text-left p-4 bg-gray-50 rounded-lg">
                    <p class="font-bold text-gray-700">Rincian Pemesanan:</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li><strong>Tipe Kamar:</strong> <?php echo htmlspecialchars($reservasi['nama_tipe']); ?></li>
                        <li><strong>Tanggal Check-in:</strong> <?php echo htmlspecialchars($reservasi['tanggal_checkin']); ?></li>
                        <li><strong>Tanggal Check-out:</strong> <?php echo htmlspecialchars($reservasi['tanggal_checkout']); ?></li>
                        <li><strong>Jumlah Kamar:</strong> <?php echo $reservasi['jumlah_tamu']; ?></li>
                        <li><strong>Total Pembayaran:</strong> <?php echo formatRupiah($reservasi['total_bayar']); ?></li>
                        <li><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($reservasi['metode_pembayaran']); ?></li>
                    </ul>
                </div>

                <div class="space-y-3 text-left p-4 bg-gray-50 rounded-lg">
                    <p class="font-bold text-gray-700">Status Pembayaran:</p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($reservasi['status_pembayaran']); ?></p>
                </div>

                <div class="space-y-3 text-left p-4 bg-gray-50 rounded-lg">
                    <p class="font-bold text-gray-700">Informasi Pemesan:</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li><strong>Nama:</strong> <?php echo htmlspecialchars($reservasi['nama_pemesan']); ?></li>
                        <li><strong>Email:</strong> <?php echo htmlspecialchars($reservasi['email_pemesan']); ?></li>
                        <li><strong>Telepon:</strong> <?php echo htmlspecialchars($reservasi['telp_pemesan']); ?></li>
                        <li><strong>KTP:</strong> <?php echo htmlspecialchars($reservasi['ktp_pemesan']); ?></li>
                    </ul>
                </div>

                <div class="space-y-3 text-left p-4 bg-gray-50 rounded-lg">
                    <p class="font-bold text-gray-700">Instruksi Selanjutnya:</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <?php if ($reservasi['metode_pembayaran'] === 'Transfer Bank' && $reservasi['status_pembayaran'] === 'Belum Bayar'): ?>
                            <li>
                                **Pembayaran Belum Lunas (Transfer Bank):** Segera <a href="upload_bukti_bayar.php?kode=<?php echo $kode; ?>&kontak=<?php echo $kontak_url; ?>" class="text-red-600 font-bold hover:underline">UPLOAD BUKTI TRANSFER DI SINI</a>.
                            </li>
                        <?php elseif ($reservasi['metode_pembayaran'] === 'Bayar Di Tempat'): ?>
                            <li>
                                **Bayar Di Tempat:** Tunjukkan kode booking ini saat Anda Check-in di meja resepsionis hotel.
                            </li>
                        <?php endif; ?>
                        
                        <li>Cek detail pesanan Anda dengan menggunakan kode booking di halaman <a href="cek_pesanan_detail.php?kode=<?php echo $kode; ?>&kontak=<?php echo $kontak_url; ?>" class="text-blue-600">Cek Pesanan</a>.</li>
                    </ul>
                </div>

                <div class="space-y-3 text-left p-4 bg-gray-50 rounded-lg">
                    <p class="font-bold text-gray-700">Kebijakan dan Syarat:</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Pembatalan pemesanan minimal 24 jam sebelum check-in.</li>
                        <li>Kehilangan kode booking dapat mengakibatkan keterlambatan proses check-in.</li>
                    </ul>
                </div>

                <div class="space-y-3 text-left p-4 bg-gray-50 rounded-lg">
                    <p class="font-bold text-gray-700">Kontak Hotel:</p>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li><strong>Alamat:</strong> Jl. Contoh No. 123, Kota Contoh</li>
                        <li><strong>Telepon:</strong> 08123456789</li>
                        <li><strong>Email:</strong> info@cloudninein.com</li>
                    </ul>
                </div>

                <a href="cek_pesanan_detail.php?kode=<?php echo $kode; ?>&kontak=<?php echo $kontak_url; ?>" 
                    class="mt-8 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 shadow-lg">
                     Cek Detail Pesanan Sekarang
                </a>
                <p class="mt-4 text-sm text-gray-500">Email konfirmasi telah dikirim ke alamat email Anda (simulasi).</p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-6 py-4 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>