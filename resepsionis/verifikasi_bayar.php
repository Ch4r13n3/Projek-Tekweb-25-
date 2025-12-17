<?php
// resepsionis/verifikasi_bayar.php
session_start();
require '../koneksi.php'; 

// Autentikasi Resepsionis
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

$msg = $_SESSION['msg'] ?? '';
$alert_class = $_SESSION['alert_class'] ?? '';
unset($_SESSION['msg'], $_SESSION['alert_class']);

// --- LOGIKA PROSES VERIFIKASI (SETUJU/TOLAK) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $kode = $_POST['kode_booking'];
    $action = $_POST['action'];

        
// --- LOGIKA PROSES VERIFIKASI (SETUJU/ACC) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $kode = $_POST['kode_booking'];
    $action = $_POST['action'];

    if ($action == 'setujui') {
        // PENTING: Mengubah Pembayaran jadi 'Lunas' DAN Reservasi jadi 'Confirmed'
        $sql = "UPDATE reservasi SET 
                status_pembayaran = 'Lunas', 
                status_reservasi = 'Confirmed' 
                WHERE kode_booking = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $kode);
        
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Pembayaran $kode Berhasil Diverifikasi! Tamu kini dapat Check-in.";
            $_SESSION['alert_class'] = "success";
        }
    }elseif ($action == 'tolak') {
        // Kembalikan ke Belum Bayar dan hapus bukti_pembayaran (opsional)
        $sql = "UPDATE reservasi SET status_pembayaran = 'Belum Bayar', bukti_pembayaran = NULL WHERE kode_booking = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $kode);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Bukti pembayaran $kode ditolak.";
            $_SESSION['alert_class'] = "danger";
        }
    }
    header("Location: verifikasi_bayar.php");
    exit;
}
}
// Ambil data yang berstatus 'Menunggu Verifikasi'
$sql_verif = "SELECT r.*, u.nama_lengkap, tk.nama_tipe 
              FROM reservasi r
              LEFT JOIN users u ON r.id_user = u.id_user
              JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar
              WHERE r.status_pembayaran = 'Menunggu Verifikasi'
              ORDER BY r.tanggal_pemesanan ASC";
$result = $conn->query($sql_verif);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Pembayaran - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex h-screen">

    <?php include 'sidebar.php'; ?>
<!-- 
    <nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-xl">
        <div class="p-6"><h2 class="text-2xl font-bold text-sky-400">Resepsionis</h2></div>
        <ul class="flex-1 px-4 space-y-2">
            <li><a href="dashboard_resepsionis.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg"><i class="fas fa-home mr-3"></i>Dashboard</a></li>
            <h6 class="px-3 pt-4 text-xs font-semibold text-gray-400 uppercase">Transaksi</h6>
            <li><a href="walk_in.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg"><i class="fas fa-walking mr-3"></i>Walk-in</a></li>
            <li><a href="verifikasi_bayar.php" class="flex items-center p-3 bg-sky-600 rounded-lg font-bold"><i class="fas fa-check-circle mr-3"></i>Verifikasi</a></li>
            <li><a href="reservasi_list.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg"><i class="fas fa-list mr-3"></i>Data Reservasi</a></li>
        </ul>
    </nav> -->

    <main class="flex-1 p-8 overflow-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">✔️ Verifikasi Pembayaran</h1>

        <?php if($msg): ?>
            <div class="p-4 mb-6 rounded-lg bg-<?= ($alert_class=='success'?'green':'red') ?>-100 text-<?= ($alert_class=='success'?'green':'red') ?>-700 border-l-4 border-<?= ($alert_class=='success'?'green':'red') ?>-500">
                <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-6">
            <?php if($result->num_rows > 0): while($r = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-xl shadow-md border overflow-hidden flex flex-col md:flex-row">
                    <div class="md:w-1/4 bg-gray-200 flex items-center justify-center p-2">
                        <?php if($r['bukti_pembayaran']): ?>
                            <a href="../uploads/bukti_bayar/<?= $r['bukti_pembayaran'] ?>" target="_blank">
                                <img src="../uploads/bukti_bayar/<?= $r['bukti_pembayaran'] ?>" alt="Bukti" class="max-h-48 object-contain hover:opacity-75 transition">
                            </a>
                        <?php else: ?>
                            <span class="text-gray-400 italic">Tidak ada foto</span>
                        <?php endif; ?>
                    </div>

                    <div class="p-6 flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-sky-700"><?= $r['kode_booking'] ?></h3>
                                <p class="text-gray-600 font-semibold"><?= $r['nama_lengkap'] ?: $r['nama_pemesan'] ?></p>
                                <p class="text-sm text-gray-500"><?= $r['nama_tipe'] ?> | <?= date('d M Y', strtotime($r['tanggal_checkin'])) ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">Rp <?= number_format($r['total_bayar'], 0, ',', '.') ?></p>
                                <p class="text-xs text-gray-400">Dipesan pada: <?= $r['tanggal_pemesanan'] ?></p>
                            </div>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <form method="POST" class="inline" onsubmit="return confirm('Setujui pembayaran ini?')">
                                <input type="hidden" name="kode_booking" value="<?= $r['kode_booking'] ?>">
                                <input type="hidden" name="action" value="setujui">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-bold text-sm">
                                    <i class="fas fa-check mr-1"></i> Setujui Pembayaran
                                </button>
                            </form>
                            <form method="POST" class="inline" onsubmit="return confirm('Tolak bukti pembayaran ini?')">
                                <input type="hidden" name="kode_booking" value="<?= $r['kode_booking'] ?>">
                                <input type="hidden" name="action" value="tolak">
                                <button type="submit" class="bg-white border border-red-500 text-red-500 hover:bg-red-50 px-4 py-2 rounded-lg font-bold text-sm">
                                    Tolak
                                </button>
                            </form>
                            <a href="reservasi_detail.php?kode=<?= $r['kode_booking'] ?>" class="ml-auto text-sky-600 hover:underline text-sm font-medium">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; else: ?>
                <div class="bg-white p-10 rounded-xl border-2 border-dashed text-center text-gray-400">
                    <i class="fas fa-clipboard-check text-5xl mb-3"></i>
                    <p>Tidak ada pembayaran yang perlu diverifikasi saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>