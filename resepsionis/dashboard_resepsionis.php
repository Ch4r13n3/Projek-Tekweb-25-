<?php
// resepsionis/index.php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

$today = date('Y-m-d');

// --- 1. QUERY STATISTIK ---
// A. Tamu yang sedang menginap
$sql_in = "SELECT COUNT(*) as total FROM reservasi WHERE status_reservasi = 'Check-in'";
$count_in = $conn->query($sql_in)->fetch_assoc()['total'];

// B. Tamu yang Check-out HARI INI
$sql_out = "SELECT COUNT(*) as total FROM reservasi WHERE tanggal_checkout = '$today' AND status_reservasi = 'Completed'";
$count_out = $conn->query($sql_out)->fetch_assoc()['total'];

// C. Kamar Tersedia
$sql_rooms = "SELECT COUNT(*) as total FROM kamar WHERE status = 'Tersedia'";
$count_available = $conn->query($sql_rooms)->fetch_assoc()['total'];

// D. PENDAPATAN HARI INI (Ambil dari tabel transaksi agar sinkron dengan Admin)
$sql_income = "SELECT SUM(total_harga) as total FROM transaksi 
               WHERE DATE(tgl_transaksi) = '$today' AND status_transaksi = 'Lunas'";
$income_today = $conn->query($sql_income)->fetch_assoc()['total'] ?? 0;

// --- 2. QUERY AKTIVITAS & DENAH ---
$sql_recent = "SELECT r.*, k.nomor_kamar FROM reservasi r
               LEFT JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar
               WHERE r.status_reservasi IN ('Check-in', 'Completed')
               ORDER BY r.id_reservasi DESC LIMIT 8";
$recent_activities = $conn->query($sql_recent);

$sql_denah = "SELECT kamar.*, tipe_kamar.nama_tipe FROM kamar 
              JOIN tipe_kamar ON kamar.id_tipe_kamar = tipe_kamar.id_tipe_kamar 
              ORDER BY kamar.lantai ASC, kamar.nomor_kamar ASC";
$result_denah = $conn->query($sql_denah);

$denah_lantai = [];
while($row = $result_denah->fetch_assoc()) {
    $denah_lantai[$row['lantai']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">

    <?php include 'sidebar.php'; // Sidebar sekarang aman karena koneksi belum ditutup ?>

    <main class="ml-64 p-8">
        <header class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Dashboard Resepsionis</h1>
                <p class="text-gray-500 font-medium">Ringkasan operasional tanggal <?= date('d F Y') ?></p>
            </div>
            <div class="text-right">
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Pendapatan Terkoneksi (Hari Ini)</span>
                <span class="text-2xl font-black text-emerald-600">Rp <?= number_format($income_today, 0, ',', '.') ?></span>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl mb-4"><i class="fas fa-user-check"></i></div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tamu In-House</p>
                <h3 class="text-3xl font-black"><?= $count_in ?></h3>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-xl mb-4"><i class="fas fa-sign-out-alt"></i></div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Selesai Hari Ini</p>
                <h3 class="text-3xl font-black"><?= $count_out ?></h3>
            </div>
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl mb-4"><i class="fas fa-door-open"></i></div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kamar Siap</p>
                <h3 class="text-3xl font-black"><?= $count_available ?></h3>
            </div>
            <div class="bg-gray-900 p-6 rounded-3xl shadow-sm text-white">
                <div class="w-12 h-12 bg-gray-800 text-gray-400 rounded-2xl flex items-center justify-center text-xl mb-4"><i class="fas fa-clock"></i></div>
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Waktu Sistem</p>
                <h3 class="text-2xl font-black" id="liveClock">00:00:00</h3>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                <h2 class="text-lg font-black text-gray-900">Aktivitas Terkini</h2>
                <a href="reservasi_list.php" class="text-sky-600 font-bold text-xs hover:underline">Detail &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="text-gray-400 text-[10px] uppercase font-bold tracking-widest">
                        <tr><th class="p-5">Tamu / Kode</th><th class="p-5">Kamar</th><th class="p-5">Status</th><th class="p-5">Tanggal</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php while($row = $recent_activities->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="p-5">
                                <p class="font-bold text-gray-800"><?= $row['nama_pemesan'] ?></p>
                                <p class="text-[10px] font-mono text-gray-400"><?= $row['kode_booking'] ?></p>
                            </td>
                            <td class="p-5"><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-[10px] font-bold">KMR <?= $row['nomor_kamar'] ?></span></td>
                            <td class="p-5">
                                <span class="text-[9px] font-black uppercase px-2 py-1 rounded-full <?= $row['status_reservasi'] == 'Check-in' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' ?>">
                                    <?= $row['status_reservasi'] == 'Check-in' ? 'Menginap' : 'Selesai' ?>
                                </span>
                            </td>
                            <td class="p-5 text-[10px] font-medium text-gray-500"><?= date('d/m', strtotime($row['tanggal_checkin'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <section class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-black text-gray-900 mb-6">Denah Status Kamar</h2>
            <?php foreach ($denah_lantai as $lantai => $kamar_list): ?>
                <div class="mb-8 last:mb-0">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                        <span class="bg-gray-100 px-3 py-1 rounded-lg mr-2">Lantai <?= $lantai ?></span>
                        <div class="h-[1px] bg-gray-100 flex-1"></div>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-3">
                        <?php foreach ($kamar_list as $kamar): ?>
                            <?php 
                                $bg = "bg-gray-200";
                                if($kamar['status'] == 'Tersedia') $bg = "bg-green-500 hover:bg-green-600";
                                elseif($kamar['status'] == 'Terisi') $bg = "bg-red-500 hover:bg-red-600";
                                elseif($kamar['status'] == 'Kotor') $bg = "bg-yellow-400 hover:bg-yellow-500";
                            ?>
                            <div class="<?= $bg ?> text-white p-4 rounded-2xl shadow-sm text-center transition transform hover:scale-105">
                                <div class="text-xl font-black"><?= $kamar['nomor_kamar'] ?></div>
                                <div class="text-[8px] uppercase font-bold opacity-80 mt-1"><?= explode(" ", $kamar['nama_tipe'])[0] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </main>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent = now.toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>
<?php $conn->close(); // Tutup koneksi DI SINI, setelah HTML selesai ?>