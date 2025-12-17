<!-- hasil_pencarian.php -->
<?php
session_start();
require '../koneksi.php';

function formatRupiah($angka){
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

$tgl_in    = $_GET['check_in'] ?? null;
$tgl_out   = $_GET['check_out'] ?? null;
$jml_kamar = (int)($_GET['jumlah_kamar'] ?? 1); 

$error_message = '';
$data_kamar_tersedia = [];
$lama_menginap = 0;

if (empty($tgl_in) || empty($tgl_out) || strtotime($tgl_in) >= strtotime($tgl_out) || $jml_kamar <= 0) {
    $error_message = "Harap masukkan tanggal Check-in dan Check-out yang valid, serta jumlah kamar minimal 1.";
} else {
    $date_in = new DateTime($tgl_in);
    $date_out = new DateTime($tgl_out);
    $interval = $date_in->diff($date_out);
    $lama_menginap = $interval->days;
    if ($lama_menginap == 0) $lama_menginap = 1;

    $status_lock = ['Check In', 'Konfirmasi', 'Pending'];
    $placeholders = str_repeat('?,', count($status_lock) - 1) . '?';

    $sub_query_terpakai = "
        (
            SELECT 
                id_tipe_kamar, 
                COUNT(id_reservasi) AS total_terpakai 
            FROM 
                reservasi 
            WHERE 
                tanggal_checkin < ? AND tanggal_checkout > ? 
                AND status_reservasi IN ($placeholders)
            GROUP BY 
                id_tipe_kamar
        ) AS terpakai
    ";

    $query = "
        SELECT 
            tk.*, 
            COALESCE(SUM(k.id_kamar IS NOT NULL), 0) AS total_unit_kamar,
            COALESCE(terpakai.total_terpakai, 0) AS total_terpakai_saat_ini
        FROM 
            tipe_kamar tk
        LEFT JOIN 
            kamar k ON tk.id_tipe_kamar = k.id_tipe_kamar
        LEFT JOIN 
            $sub_query_terpakai ON tk.id_tipe_kamar = terpakai.id_tipe_kamar
        GROUP BY 
            tk.id_tipe_kamar
        HAVING 
            (total_unit_kamar - total_terpakai_saat_ini) >= ?
    ";

    $stmt = $conn->prepare($query);
    $params = array_merge([$tgl_out, $tgl_in], $status_lock, [$jml_kamar]);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_kamar_tersedia = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

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
            
            <div class="flex items-center space-x-6">
                <ul class="flex space-x-6 items-center">
                    <li><a href="index.php" class="text-gray-700 hover:text-blue-600 font-medium">Home</a></li>
                    <li><a href="index.php#populer" class="text-gray-700 hover:text-blue-600 font-medium">Kamar Populer</a></li>
                    <li><a href="index.php#cek-pesanan" class="text-gray-700 hover:text-blue-600 font-medium">Cek Pesanan Saya</a></li>
                </ul>

                <?php 
                // Menggunakan variabel sesi yang sama dengan Admin/Resepsionis: 'loggedin'
                if (isset($_SESSION['loggedin'])): 
                ?>
                    <a href="../logout.php"
                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 shadow-md">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="../login.php"
                        class="bg-[#134686] hover:bg-[#27548A] text-white font-bold py-2 px-4 rounded-lg transition duration-300 shadow-md">
                        Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-6 text-center">
            Hasil Pencarian Kamar Tersedia
        </h1>
        <p class="text-center text-lg text-gray-600 mb-10">
            <?php 
            if ($tgl_in && $tgl_out) {
                echo "Kamar tersedia untuk check-in: **" . date('d M Y', strtotime($tgl_in)) . "** hingga check-out: **" . date('d M Y', strtotime($tgl_out)) . "** (Total: **" . $lama_menginap . " Malam**).";
            } else {
                echo "Silakan lakukan pencarian dari halaman utama.";
            }
            ?>
        </p>

        <div class="max-w-6xl mx-auto space-y-6">
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-center" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php elseif (empty($data_kamar_tersedia)): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative text-center" role="alert">
                    <span class="block sm:inline">Maaf, tidak ada kamar yang tersedia untuk tanggal dan jumlah yang Anda cari.</span>
                </div>
            <?php else: ?>
                <?php foreach ($data_kamar_tersedia as $kamar): 
                    $sisa_kamar = $kamar['total_unit_kamar'] - $kamar['total_terpakai_saat_ini'];
                ?>
                <div class="bg-white rounded-xl shadow-lg flex flex-col md:flex-row overflow-hidden border">
                    <img src="../uploads/<?php echo htmlspecialchars($kamar['foto'] ?? 'default.jpg'); ?>" 
                            alt="<?php echo htmlspecialchars($kamar['nama_tipe']); ?>" 
                            class="w-full md:w-1/3 h-64 md:h-auto object-cover">
                    <div class="p-6 flex-1 flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($kamar['nama_tipe']); ?></h2>
                            <p class="text-sm text-gray-600 mt-1 mb-3">Kapasitas: Maks. <?php echo htmlspecialchars($kamar['kapasitas']); ?> Orang | Ranjang: <?php echo htmlspecialchars($kamar['jenis_tempat_tidur'] ?? 'N/A'); ?></p>
                            <p class="text-sm text-gray-700 list-inside space-y-1 mt-4">
                                <?php echo nl2br(htmlspecialchars(substr($kamar['deskripsi'], 0, 150) . (strlen($kamar['deskripsi']) > 150 ? '...' : ''))); ?>
                            </p>
                            <p class="text-xs text-green-600 mt-2 font-semibold">Tersedia **<?php echo $sisa_kamar; ?>** unit dari **<?php echo $kamar['total_unit_kamar']; ?>** unit.</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-sm text-gray-500 block">Harga / Malam</span>
                            <p class="text-3xl font-extrabold text-red-600 mb-2"><?php echo formatRupiah($kamar['harga_per_malam']); ?></p>
                            <p class="text-xs text-gray-500 mb-4">Total <?php echo $lama_menginap; ?> Malam x <?php echo $jml_kamar; ?> Kamar</p>
                            <a href="pemesanan.php?tipe_id=<?php echo $kamar['id_tipe_kamar']; ?>&checkin=<?php echo $tgl_in; ?>&checkout=<?php echo $tgl_out; ?>&jml=<?php echo $jml_kamar; ?>" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 shadow-md">
                                Pesan <?php echo $jml_kamar; ?> Kamar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-10">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; 2025 Cloud Nine In. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
