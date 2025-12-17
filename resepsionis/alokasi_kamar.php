<?php
// resepsionis/alokasi_kamar.php
session_start();
require '../koneksi.php'; 

// 1. Autentikasi Resepsionis
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

$msg = $_SESSION['msg'] ?? '';
$alert_class = $_SESSION['alert_class'] ?? '';
unset($_SESSION['msg'], $_SESSION['alert_class']);

if (isset($_POST['proses_alokasi'])) {
    $kode = $_POST['kode_booking'];
    $id_kamar = $_POST['id_kamar']; // Nomor kamar yang dipilih resepsionis

    $conn->begin_transaction();
    try {
        // 1. Hubungkan reservasi dengan nomor kamar
        $conn->query("UPDATE reservasi SET id_kamar_ditempati = '$id_kamar' WHERE kode_booking = '$kode'");
        
        // 2. Ubah status kamar jadi Terisi
        $conn->query("UPDATE kamar SET status = 'Terisi' WHERE id_kamar = '$id_kamar'");

        // 3. AMBIL DATA LENGKAP UNTUK TRANSAKSI (PENTING!)
        $res = $conn->query("SELECT * FROM reservasi WHERE kode_booking = '$kode'")->fetch_assoc();

        // 4. SIMPAN KE TABEL TRANSAKSI (Agar masuk ke laporan Admin)
        $sql_t = "INSERT INTO transaksi (kode_booking, id_user, id_kamar, tgl_check_in, tgl_check_out, total_harga, status_transaksi) 
                  VALUES (?, ?, ?, ?, ?, ?, 'Lunas')";
        $stmt_t = $conn->prepare($sql_t);
        
        // Pastikan urutan bind_param sesuai: s (string), i (int), i (int), s (date), s (date), d (double/decimal)
        $stmt_t->bind_param("siiisd", 
            $res['kode_booking'], 
            $res['id_user'], 
            $id_kamar, 
            $res['tanggal_checkin'], 
            $res['tanggal_checkout'], 
            $res['total_bayar']
        );
        $stmt_t->execute();

        $conn->commit();
        $_SESSION['msg'] = "Kamar berhasil dialokasikan dan pendapatan tercatat!";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// // --- LOGIKA PROSES ALOKASI ---
// if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proses_alokasi'])) {
//     $kode = $_POST['kode_booking'];
//     $id_kamar = $_POST['id_kamar'];

//     $conn->begin_transaction();
//     try {
//         // Update tabel reservasi: isi id_kamar_ditempati
//         $sql = "UPDATE reservasi SET id_kamar_ditempati = ? WHERE kode_booking = ?";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("is", $id_kamar, $kode);
        
//         if (!$stmt->execute()) throw new Exception("Gagal update reservasi.");

//         // Update status kamar menjadi 'Terisi' (Opsional, tergantung alur hotelmu)
//         // Jika alokasi dilakukan saat check-in, maka status kamar jadi 'Terisi'
//         $conn->query("UPDATE kamar SET status = 'Terisi' WHERE id_kamar = $id_kamar");

//         $conn->commit();
//         $_SESSION['msg'] = "Berhasil! Kamar telah dialokasikan untuk pesanan $kode.";
//         $_SESSION['alert_class'] = "success";
//         header("Location: reservasi_list.php");
//         exit;
//     } catch (Exception $e) {
//         $conn->rollback();
//         $_SESSION['msg'] = "Gagal Alokasi: " . $e->getMessage();
//         $_SESSION['alert_class'] = "danger";
//     }
// }

// 2. Ambil data reservasi yang butuh alokasi (Status: Confirmed/Lunas tapi id_kamar masih kosong)
$sql_butuh_alokasi = "SELECT r.*, tk.nama_tipe 
                      FROM reservasi r
                      JOIN tipe_kamar tk ON r.id_tipe_kamar = tk.id_tipe_kamar
                      WHERE r.id_kamar_ditempati IS NULL 
                      AND r.status_pembayaran = 'Lunas'
                      ORDER BY r.tanggal_checkin ASC";
$result = $conn->query($sql_butuh_alokasi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Alokasi Kamar - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <?php include 'sidebar.php'; ?>
<!-- 
    <nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-xl">
        <div class="p-6"><h2 class="text-2xl font-bold text-sky-400">Resepsionis</h2></div>
        <ul class="flex-1 px-4 space-y-2">
            <li><a href="dashboard_resepsionis.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg"><i class="fas fa-home mr-3 text-gray-400"></i>Dashboard</a></li>
            <h6 class="px-3 pt-4 text-xs font-semibold text-gray-400 uppercase">Transaksi</h6>
            <li><a href="walk_in.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg"><i class="fas fa-walking mr-3 text-gray-400"></i>Walk-in</a></li>
            <li><a href="verifikasi_bayar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg"><i class="fas fa-check-circle mr-3 text-gray-400"></i>Verifikasi</a></li>
            <li><a href="reservasi_list.php" class="flex items-center p-3 bg-sky-600 rounded-lg font-bold"><i class="fas fa-list mr-3"></i>Data Reservasi</a></li>
        </ul>
    </nav> -->

    <main class="flex-1 p-8 overflow-y-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-black text-gray-900">🛏️ Alokasi Kamar Customer</h1>
            <p class="text-gray-500">Tentukan nomor kamar untuk reservasi yang masuk via Website.</p>
        </header>

        <?php if($msg): ?>
            <div class="p-4 mb-6 rounded-2xl border <?= ($alert_class=='success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800') ?> flex items-center shadow-sm">
                <i class="fas <?= ($alert_class=='success'?'fa-check-circle':'fa-exclamation-circle') ?> mr-3 text-xl"></i>
                <span class="font-bold"><?= $msg ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-6">
            <?php if($result->num_rows > 0): while($r = $result->fetch_assoc()): ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col md:flex-row justify-between items-center hover:shadow-md transition">
                    
                    <div class="mb-4 md:mb-0">
                        <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">KODE: <?= $r['kode_booking'] ?></span>
                        <h3 class="text-xl font-bold text-gray-800 mt-2"><?= htmlspecialchars($r['nama_pemesan']) ?></h3>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-bed mr-1"></i> <?= $r['nama_tipe'] ?> 
                            <span class="mx-2">|</span> 
                            <i class="fas fa-calendar-alt mr-1"></i> <?= date('d M', strtotime($r['tanggal_checkin'])) ?> - <?= date('d M Y', strtotime($r['tanggal_checkout'])) ?>
                        </p>
                    </div>

                    <form method="POST" class="flex items-center gap-4 w-full md:w-auto">
                        <input type="hidden" name="kode_booking" value="<?= $r['kode_booking'] ?>">
                        
                        <div class="flex-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Pilih Kamar Tersedia</label>
                            <select name="id_kamar" required class="w-full md:w-48 p-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none text-sm font-semibold">
                                <option value="">-- Pilih --</option>
                                <?php 
                                // Ambil daftar kamar yang tipenya sama dan statusnya 'Tersedia'
                                $tipe_id = $r['id_tipe_kamar'];
                                $kamar_ready = $conn->query("SELECT id_kamar, nomor_kamar FROM kamar WHERE id_tipe_kamar = $tipe_id AND status = 'Tersedia'");
                                while($kmr = $kamar_ready->fetch_assoc()): 
                                ?>
                                    <option value="<?= $kmr['id_kamar'] ?>">No. <?= $kmr['nomor_kamar'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <button type="submit" name="proses_alokasi" class="bg-gray-900 text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-black transition shadow-lg mt-5">
                            Alokasikan
                        </button>
                    </form>
                </div>
            <?php endwhile; else: ?>
                <div class="bg-white p-20 rounded-3xl border-2 border-dashed border-gray-200 text-center text-gray-400">
                    <i class="fas fa-check-double text-5xl mb-4 text-gray-200"></i>
                    <p class="font-medium">Semua reservasi website sudah dialokasikan kamarnya.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>