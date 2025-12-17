<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard)
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// =========================================================
// == LOGIKA HITUNG STATISTIK (DARI DATABASE) ==
// =========================================================

// Karena semua query di bawah ini adalah SELECT sederhana tanpa input user, 
// kita bisa menggunakan $conn->query() untuk efisiensi, tetapi
// untuk total pendapatan, kita akan gunakan Prepared Statement.

// A. Hitung Kamar Terisi, Kosong, Maintenance
$kamar_terisi = $conn->query("SELECT COUNT(*) as total FROM kamar WHERE status = 'Terisi'")->fetch_assoc()['total'] ?? 0;
$kamar_kosong = $conn->query("SELECT COUNT(*) as total FROM kamar WHERE status = 'Tersedia'")->fetch_assoc()['total'] ?? 0;
$kamar_maintenance = $conn->query("SELECT COUNT(*) as total FROM kamar WHERE status IN ('Kotor', 'Perbaikan')")->fetch_assoc()['total'] ?? 0;


// D. Total Pendapatan Bulan Ini (MENGGUNAKAN PREPARED STATEMENT)
$total_pendapatan_bulan_ini = 0; 
$current_month = date('m');
$current_year = date('Y');

$cek_tabel = $conn->query("SHOW TABLES LIKE 'transaksi'");
if ($cek_tabel && $cek_tabel->num_rows > 0) {
    
    $query_uang = "SELECT SUM(total_harga) as total 
                   FROM transaksi
                   WHERE status_transaksi IN ('Selesai', 'Check In')
                   AND MONTH(tgl_transaksi) = ?
                   AND YEAR(tgl_transaksi) = ?";
    
    $stmt_uang = $conn->prepare($query_uang);
    // 'ii' untuk 2 integer (bulan dan tahun)
    $stmt_uang->bind_param("ii", $current_month, $current_year); 
    $stmt_uang->execute();
    $res_uang = $stmt_uang->get_result();

    if ($res_uang) {
        $row_uang = $res_uang->fetch_assoc();
        $total_pendapatan_bulan_ini = $row_uang['total'] ?? 0;
    }
    $stmt_uang->close();
}

$sql = "SELECT SUM(total_bayar) AS total_pendapatan FROM reservasi WHERE status_pembayaran = 'Lunas'";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

// =========================================================
// == LOGIKA DENAH KAMAR (PER LANTAI) ==
// =========================================================

// Ambil semua kamar, urutkan berdasarkan Lantai lalu Nomor
$sql_denah = "SELECT kamar.*, tipe_kamar.nama_tipe 
              FROM kamar 
              JOIN tipe_kamar ON kamar.id_tipe_kamar = tipe_kamar.id_tipe_kamar 
              ORDER BY kamar.lantai ASC, kamar.nomor_kamar ASC";
$result_denah = $conn->query($sql_denah);

// Kita kelompokkan data ke dalam Array Multidimensi
$denah_lantai = [];
while($row = $result_denah->fetch_assoc()) {
    $lantai = $row['lantai'];
    $denah_lantai[$lantai][] = $row;
}

// 5. Tutup koneksi database
if (isset($conn)) {
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cloud Nine In</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="flex h-screen overflow-hidden">
        
        <nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-xl">
            <div class="p-6">
                <h2 class="text-3xl font-bold tracking-wider text-blue-400">Admin CNI</h2>
            </div>
            <ul class="flex-1 px-4 space-y-2 overflow-y-auto">
                <li><a href="dashboard_admin.php" class="flex items-center p-3 bg-blue-600 rounded-lg shadow-md"><span class="mr-3 text-xl">🏠</span>Dashboard</a></li>
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🛏️</span>Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">👥</span>Data Customer</a></li>
                <li><a href="daftar_kelola_transaksi.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">💳</span>Transaksi</a></li>
                <li><a href="laporan.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">📈</span>Laporan</a></li>
            </ul>
             <div class="p-4 border-t border-gray-800">
                <a href="../logout.php" class="flex items-center justify-center p-3 bg-red-600 hover:bg-red-700 rounded-lg transition-colors font-semibold shadow-lg">
                    <span class="mr-2">🚪</span> Logout
                </a>
            </div>
        </nav>

        <main class="flex-1 p-8 overflow-auto">
            <h1 class="text-3xl font-bold mb-2 text-gray-800">Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
            <p class="text-gray-500 mb-8">Berikut adalah ringkasan operasional hotel hari ini.</p>
            
            <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Pendapatan (Bulan Ini)</h3>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">Rp <?php echo number_format($data['total_pendapatan'], 0, ',', '.'); ?></p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Kamar Terisi</h3>
                    </div>
                    <p class="text-3xl font-bold text-red-600"><?php echo $kamar_terisi; ?> <span class="text-sm font-normal text-gray-400">Unit</span></p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Kamar Kosong</h3>
                    </div>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $kamar_kosong; ?> <span class="text-sm font-normal text-gray-400">Unit</span></p>
                </div>

                 <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Kotor / Perbaikan</h3>
                    </div>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $kamar_maintenance; ?> <span class="text-sm font-normal text-gray-400">Unit</span></p>
                </div>
            </section>

            <section class="bg-white p-8 rounded-xl shadow-md border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Denah Status Kamar</h2>
                    
                    <div class="flex space-x-4 text-xs font-medium">
                        <div class="flex items-center"><span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>Tersedia</div>
                        <div class="flex items-center"><span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>Terisi</div>
                        <div class="flex items-center"><span class="w-3 h-3 bg-yellow-400 rounded-full mr-2"></span>Kotor</div>
                        <div class="flex items-center"><span class="w-3 h-3 bg-gray-600 rounded-full mr-2"></span>Perbaikan</div>
                    </div>
                </div>

                <?php if (empty($denah_lantai)): ?>
                    <div class="text-center py-10 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <p class="text-gray-500">Belum ada data kamar. Silakan input di menu "Kelola Kamar".</p>
                        <a href="daftar_kelola_kamar.php" class="text-blue-600 hover:underline mt-2 inline-block">Input Kamar Sekarang &rarr;</a>
                    </div>
                <?php else: ?>
                    
                    <?php foreach ($denah_lantai as $lantai => $kamar_list): ?>
                        <div class="mb-8 last:mb-0">
                            <h3 class="text-lg font-bold text-gray-700 mb-3 flex items-center">
                                <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-sm mr-2">Lantai <?php echo $lantai; ?></span>
                            </h3>
                            
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                                <?php foreach ($kamar_list as $kamar): ?>
                                    <?php 
                                        // Logika Warna Dinamis berdasarkan Status DB
                                        $bg_color = "bg-gray-200"; // Default
                                        $status_text = $kamar['status'];

                                        if($status_text == 'Tersedia') $bg_color = "bg-green-500 hover:bg-green-600";
                                        elseif($status_text == 'Terisi') $bg_color = "bg-red-500 hover:bg-red-600";
                                        elseif($status_text == 'Kotor') $bg_color = "bg-yellow-400 hover:bg-yellow-500 text-yellow-900"; 
                                        elseif($status_text == 'Perbaikan') $bg_color = "bg-gray-600 hover:bg-gray-700";
                                    ?>

                                    <a href="edit_kamar.php?id=<?= $kamar['id_kamar'] ?>" class="<?php echo $bg_color; ?> text-white p-3 rounded-lg shadow-sm text-center transition transform hover:scale-105 cursor-pointer relative group" 
                                         title="<?php echo $kamar['nomor_kamar'] . ' - ' . $status_text . ' (' . $kamar['nama_tipe'] . ')'; ?>">
                                        
                                        <div class="text-lg font-bold"><?php echo $kamar['nomor_kamar']; ?></div>
                                        <div class="text-[10px] uppercase opacity-90 truncate px-1">
                                            <?php 
                                                // Jika nama tipe kepanjangan, ambil kata depan aja biar kotak gak rusak
                                                $tipe = explode(" ", $kamar['nama_tipe']);
                                                echo $tipe[0]; 
                                            ?>
                                        </div>

                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-32 bg-black text-white text-xs rounded py-1 px-2 hidden group-hover:block z-10">
                                            <?php echo $kamar['nama_tipe']; ?><br>
                                            Status: <?php echo $status_text; ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

        </main>
    </div>
</body>
</html>