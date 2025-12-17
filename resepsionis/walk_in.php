<?php
// resepsionis/walk_in.php
session_start();
require_once '../koneksi.php'; 

// 1. Autentikasi Resepsionis
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

// Helper Functions
function formatRupiah($angka) { return 'Rp ' . number_format($angka, 0, ',', '.'); }
function generateKodeBooking() { return 'WALK-' . strtoupper(substr(md5(time()), 0, 6)); }

// Ambil Pesan Flash
$msg = $_SESSION['msg'] ?? '';
$alert_class = $_SESSION['alert_class'] ?? '';
$search_data = $_SESSION['search_data'] ?? []; 
unset($_SESSION['msg'], $_SESSION['alert_class'], $_SESSION['search_data']);

// Ambil Master Tipe Kamar
$tipe_kamar = $conn->query("SELECT * FROM tipe_kamar ORDER BY harga_per_malam ASC")->fetch_all(MYSQLI_ASSOC);

// Data Default
$checkinDate = $search_data['checkinDate'] ?? date('Y-m-d');
$checkoutDate = $search_data['checkoutDate'] ?? date('Y-m-d', strtotime('+1 day'));
$available_rooms = $search_data['available_rooms'] ?? [];
$id_tipe_kamar_final = $search_data['id_tipe_kamar_final'] ?? null;
$total_biaya = $search_data['total_biaya'] ?? 0;
$nama_tipe_dipesan = $search_data['nama_tipe_dipesan'] ?? '';

// --- LOGIKA 1: CEK KETERSEDIAAN ---
if (isset($_POST['cek_ketersediaan'])) {
    $checkinDate = $_POST['checkinDate'];
    $checkoutDate = $_POST['checkoutDate'];
    $id_tipe_kamar = (int)$_POST['id_tipe_kamar_search'];

    // Validasi Tanggal
    if (strtotime($checkinDate) >= strtotime($checkoutDate)) {
        $_SESSION['msg'] = "Tanggal check-out harus setelah check-in.";
        $_SESSION['alert_class'] = "danger";
        header("Location: walk_in.php"); exit;
    }

    // Hitung Durasi & Biaya
    $durasi = (new DateTime($checkoutDate))->diff(new DateTime($checkinDate))->days;
    $tipe = $conn->query("SELECT * FROM tipe_kamar WHERE id_tipe_kamar = $id_tipe_kamar")->fetch_assoc();
    
    // Query Kamar Tersedia
    $query = "SELECT id_kamar, nomor_kamar FROM kamar 
              WHERE id_tipe_kamar = ? AND status = 'Tersedia'
              AND id_kamar NOT IN (
                  SELECT id_kamar_ditempati FROM reservasi 
                  WHERE id_kamar_ditempati IS NOT NULL 
                  AND status_reservasi NOT IN ('Batal', 'Completed')
                  AND (tanggal_checkin < ? AND tanggal_checkout > ?)
              )";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $id_tipe_kamar, $checkoutDate, $checkinDate);
    $stmt->execute();
    $rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $_SESSION['search_data'] = [
        'checkinDate' => $checkinDate,
        'checkoutDate' => $checkoutDate,
        'id_tipe_kamar_final' => $id_tipe_kamar,
        'available_rooms' => $rooms,
        'total_biaya' => $tipe['harga_per_malam'] * $durasi,
        'nama_tipe_dipesan' => $tipe['nama_tipe']
    ];

    if (empty($rooms)) {
        $_SESSION['msg'] = "Kamar tipe tersebut penuh pada tanggal tersebut.";
        $_SESSION['alert_class'] = "danger";
    }
    header("Location: walk_in.php"); exit;
}

// --- LOGIKA 2: SIMPAN & CHECK-IN (FIXED WITH DURASI_INAP) ---
if (isset($_POST['submit_walkin'])) {
    $conn->begin_transaction();
    try {
        $kode = generateKodeBooking();
        $id_kamar = $_POST['id_kamar_ditempati'];
        $id_user_petugas = $_SESSION['id_user']; 

        // A. Hitung ulang durasi inap untuk keamanan data
        $t1 = new DateTime($_POST['checkinDate_final']);
        $t2 = new DateTime($_POST['checkoutDate_final']);
        $durasi_inap = $t1->diff($t2)->days;
        if($durasi_inap < 1) $durasi_inap = 1;

        // B. SQL Query: Tambahkan kolom durasi_inap
        $query_insert = "INSERT INTO reservasi (
            kode_booking, 
            id_user, 
            id_tipe_kamar, 
            id_kamar_ditempati, 
            durasi_inap,
            tanggal_checkin, 
            tanggal_checkout, 
            jumlah_tamu, 
            total_bayar, 
            status_reservasi, 
            status_pembayaran, 
            nama_pemesan, 
            telp_pemesan, 
            email_pemesan,
            tanggal_pemesanan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Check-in', 'Lunas', ?, ?, ?, NOW())";

        $total_bayar = $_POST['total_bayar_final']; // Ambil dari hitungan harga tipe kamar x durasi
        $status_pembayaran = 'Lunas'; // Karena tamu walk-in langsung bayar di tempat

        $query = "INSERT INTO reservasi (kode_booking, total_bayar, status_pembayaran, tanggal_pemesanan, ...) 
                VALUES (?, ?, ?, NOW(), ...)";
        
        $stmt = $conn->prepare($query_insert);

        if (!$stmt) {
            // Ini untuk menangkap pesan error SQL jika prepare gagal
            throw new Exception("Prepare Error: " . $conn->error);
        }
        
        // C. Bind Param: s (kode), i (user), i (tipe), i (kamar), i (durasi), s (in), s (out), i (tamu), d (bayar), s (nama), s (telp), s (email)
        // Format Tipe: siiiisidsss
        $tipe_data = "siiiissidsss"; 
        
        $stmt->bind_param(
            $tipe_data, 
            $kode, 
            $id_user_petugas, 
            $_POST['id_tipe_kamar_final'], 
            $id_kamar, 
            $durasi_inap, // <--- Data Durasi Inap
            $_POST['checkinDate_final'], 
            $_POST['checkoutDate_final'], 
            $_POST['jumlah_tamu'], 
            $_POST['total_bayar_final'], 
            $_POST['namaTamu'], 
            $_POST['noTelp'],
            $_POST['emailTamu']
        );
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        // D. Update Status Kamar
        $conn->query("UPDATE kamar SET status = 'Terisi' WHERE id_kamar = $id_kamar");

        $conn->commit();
        $_SESSION['msg'] = "Check-in Berhasil! Kode Booking: $kode";
        $_SESSION['alert_class'] = "success";
        header("Location: walk_in.php"); 
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['msg'] = "Gagal Simpan: " . $e->getMessage();
        $_SESSION['alert_class'] = "danger";
        header("Location: walk_in.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Walk-in - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <header class="mb-8">
            <h1 class="text-3xl font-black text-gray-900">🚶‍♂️ Walk-in Reservation</h1>
            <p class="text-gray-500">Proses tamu datang langsung (langsung bayar & check-in).</p>
        </header>

        <?php if($msg): ?>
            <div class="p-4 mb-6 rounded-2xl border <?= ($alert_class=='success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800') ?> flex items-center shadow-sm">
                <i class="fas <?= ($alert_class=='success'?'fa-check-circle':'fa-exclamation-circle') ?> mr-3 text-xl"></i>
                <span class="font-bold"><?= $msg ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 sticky top-8">
                    <h2 class="text-lg font-bold mb-4 flex items-center text-sky-700">
                        <span class="w-8 h-8 bg-sky-100 text-sky-700 rounded-full flex items-center justify-center mr-2 text-sm">1</span>
                        Cek Ketersediaan
                    </h2>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Check-in</label>
                            <input type="date" name="checkinDate" value="<?= $checkinDate ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Check-out</label>
                            <input type="date" name="checkoutDate" value="<?= $checkoutDate ?>" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Tipe Kamar</label>
                            <select name="id_tipe_kamar_search" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500">
                                <?php foreach($tipe_kamar as $t): ?>
                                    <option value="<?= $t['id_tipe_kamar'] ?>" <?= ($id_tipe_kamar_final == $t['id_tipe_kamar'] ? 'selected' : '') ?>>
                                        <?= $t['nama_tipe'] ?> (<?= formatRupiah($t['harga_per_malam']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="cek_ketersediaan" class="w-full bg-gray-900 text-white p-3 rounded-xl font-bold hover:bg-black transition shadow-lg">
                            Cari Kamar
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <?php if(!empty($available_rooms)): ?>
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold mb-6 flex items-center text-emerald-700">
                        <span class="w-8 h-8 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mr-2 text-sm">2</span>
                        Detail Tamu & Pembayaran
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="checkinDate_final" value="<?= $checkinDate ?>">
                        <input type="hidden" name="checkoutDate_final" value="<?= $checkoutDate ?>">
                        <input type="hidden" name="id_tipe_kamar_final" value="<?= $id_tipe_kamar_final ?>">
                        <input type="hidden" name="total_bayar_final" value="<?= $total_biaya ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Nama Tamu</label>
                                <input type="text" name="namaTamu" required placeholder="Contoh: John Doe" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase block mb-1">No. WhatsApp</label>
                                <input type="text" name="noTelp" required placeholder="0812..." class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Email Tamu</label>
                                <input type="email" name="emailTamu" required placeholder="email@tamu.com" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Jumlah Tamu</label>
                                <input type="number" name="jumlah_tamu" value="1" min="1" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Pilih Nomor Kamar</label>
                                <select name="id_kamar_ditempati" required class="w-full p-3 border border-emerald-200 bg-emerald-50 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-700">
                                    <option value="">-- Pilih Kamar --</option>
                                    <?php foreach($available_rooms as $r): ?>
                                        <option value="<?= $r['id_kamar'] ?>">Kamar No. <?= $r['nomor_kamar'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="bg-gray-900 rounded-2xl p-6 text-white flex justify-between items-center shadow-xl">
                            <div>
                                <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Total Pembayaran</p>
                                <h3 class="text-3xl font-black text-emerald-400"><?= formatRupiah($total_biaya) ?></h3>
                                <p class="text-xs text-gray-400 mt-1 italic">* Pembayaran harus diterima lunas saat Walk-in</p>
                            </div>
                            <button type="submit" name="submit_walkin" onclick="return confirm('Proses Check-in sekarang?')" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-xl font-black transition-all transform hover:scale-105 shadow-lg">
                                BAYAR & CHECK-IN <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                <div class="bg-white p-20 rounded-2xl border-2 border-dashed border-gray-200 text-center">
                    <i class="fas fa-bed text-5xl text-gray-200 mb-4"></i>
                    <p class="text-gray-400 font-medium">Silakan cek ketersediaan kamar terlebih dahulu pada langkah pertama.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</body>
</html>