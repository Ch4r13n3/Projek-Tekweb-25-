<?php
// resepsionis/extend_stay.php
session_start();
require_once '../koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'resepsionis') {
    header("Location: ../login.php");
    exit;
}

$msg = $_SESSION['msg'] ?? '';
$alert_class = $_SESSION['alert_class'] ?? '';
unset($_SESSION['msg'], $_SESSION['alert_class']);

$current_reservation = null;

// --- 1. LOGIKA MENCARI TAMU ---
if (isset($_POST['cari_tamu'])) {
    $search_query = "%" . $_POST['search_query'] . "%";
    $raw_query = $_POST['search_query'];

    $sql_cari = "SELECT 
                    r.id_reservasi, r.kode_booking, r.tanggal_checkin, r.tanggal_checkout, 
                    r.total_biaya_kamar, r.id_kamar_ditempati,
                    r.nama_pemesan, r.telp_pemesan, 
                    k.nomor_kamar, tk.nama_tipe, tk.harga_per_malam
                 FROM reservasi r
                 JOIN kamar k ON r.id_kamar_ditempati = k.id_kamar
                 JOIN tipe_kamar tk ON k.id_tipe_kamar = tk.id_tipe_kamar
                 WHERE r.status_reservasi = 'Check-in'
                   AND (r.kode_booking = ? OR r.nama_pemesan LIKE ?)";
    
    $stmt = $conn->prepare($sql_cari);
    $stmt->bind_param("ss", $raw_query, $search_query);
    $stmt->execute();
    $result_cari = $stmt->get_result();

    if ($result_cari->num_rows > 0) {
        $current_reservation = $result_cari->fetch_assoc();
    } else {
        $msg = "Tamu tidak ditemukan atau status tidak sedang Check-in.";
        $alert_class = 'danger';
    }
}

// --- 2. LOGIKA PROSES PERPANJANGAN ---
if (isset($_POST['proses_extend'])) {
    $id_reservasi = $_POST['id_reservasi'];
    $id_kamar = $_POST['id_kamar'];
    $new_checkout_date = $_POST['new_checkout_date'];
    $harga_per_malam = $_POST['harga_per_malam'];
    $old_total_cost = $_POST['old_total_cost'];
    $old_checkout_date = $_POST['old_checkout_date'];
    
    $d1 = new DateTime($old_checkout_date);
    $d2 = new DateTime($new_checkout_date);
    $durasi_tambahan = $d1->diff($d2)->days;
    
    if ($durasi_tambahan <= 0) {
        $_SESSION['msg'] = "Tanggal baru harus lebih lama dari jadwal sebelumnya.";
        $_SESSION['alert_class'] = "danger";
    } else {
        $biaya_tambahan = $durasi_tambahan * $harga_per_malam;
        $new_total_cost = $old_total_cost + $biaya_tambahan;

        // Cek Konflik: Apakah di tanggal perpanjangan tersebut kamar sudah dibooking orang lain?
        $sql_conflict = "SELECT id_reservasi FROM reservasi 
                         WHERE id_kamar_ditempati = ? 
                         AND status_reservasi IN ('Confirmed', 'Check-in')
                         AND tanggal_checkin < ? 
                         AND tanggal_checkout > ?
                         AND id_reservasi != ? LIMIT 1";
        
        $stmt_c = $conn->prepare($sql_conflict);
        $stmt_c->bind_param("issi", $id_kamar, $new_checkout_date, $old_checkout_date, $id_reservasi);
        $stmt_c->execute();
        
        if ($stmt_c->get_result()->num_rows == 0) {
            // Update tanggal dan total biaya
            $sql_update = "UPDATE reservasi SET tanggal_checkout = ?, total_biaya_kamar = ?, total_bayar = ? WHERE id_reservasi = ?";
            $stmt_u = $conn->prepare($sql_update);
            $stmt_u->bind_param("sddi", $new_checkout_date, $new_total_cost, $new_total_cost, $id_reservasi);

            if ($stmt_u->execute()) {
                $_SESSION['msg'] = "Berhasil diperpanjang! Tambahan biaya: Rp " . number_format($biaya_tambahan, 0, ',', '.');
                $_SESSION['alert_class'] = "success";
                header("Location: extend_stay.php");
                exit;
            }
        } else {
            $_SESSION['msg'] = "Gagal! Kamar ini sudah dipesan tamu lain pada tanggal perpanjangan tersebut.";
            $_SESSION['alert_class'] = "danger";
        }
    }
    header("Location: extend_stay.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Extend Stay - Cloud Nine Inn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-900">

    <?php include 'sidebar.php'; ?>

    <main class="ml-64 p-8">
        <header class="mb-8">
            <h1 class="text-3xl font-black text-gray-900">🗓️ Extend Stay</h1>
            <p class="text-gray-500">Perpanjang durasi menginap tamu yang sedang Check-in.</p>
        </header>

        <?php if($msg): ?>
            <div class="p-4 mb-6 rounded-2xl border <?= ($alert_class == 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800') ?> flex items-center shadow-sm">
                <i class="fas <?= ($alert_class == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') ?> mr-3 text-xl"></i>
                <span class="font-bold"><?= $msg ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 sticky top-8">
                    <h2 class="text-lg font-bold mb-4 flex items-center text-sky-700">
                        <i class="fas fa-search-user mr-2"></i> Cari Tamu
                    </h2>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Kode Booking / Nama</label>
                            <input type="text" name="search_query" required 
                                   class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-sky-500" 
                                   placeholder="Contoh: WALK-A1B2C3">
                        </div>
                        <button type="submit" name="cari_tamu" class="w-full bg-gray-900 text-white p-3 rounded-xl font-bold hover:bg-black transition shadow-lg">
                            Cari Data Tamu
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <?php if ($current_reservation): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <span class="text-[10px] font-black bg-sky-100 text-sky-700 px-2 py-1 rounded uppercase tracking-wider">Sedang Menginap</span>
                            <h2 class="text-2xl font-black text-gray-900 mt-1"><?= $current_reservation['nama_pemesan'] ?></h2>
                            <p class="text-sm text-gray-500 font-medium">Booking ID: <span class="text-gray-900"><?= $current_reservation['kode_booking'] ?></span></p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-gray-400 uppercase italic">Nomor Kamar</p>
                            <p class="text-3xl font-black text-sky-600"><?= $current_reservation['nomor_kamar'] ?></p>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-400 uppercase">Tipe Kamar</label>
                                <p class="font-bold text-gray-800 text-lg"><?= $current_reservation['nama_tipe'] ?></p>
                                <p class="text-sm text-emerald-600 font-bold">Rp <?= number_format($current_reservation['harga_per_malam'], 0, ',', '.') ?> <span class="text-gray-400 font-normal">/ malam</span></p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-400 uppercase">Jadwal Check-out Saat Ini</label>
                                <p class="font-black text-red-500 text-lg">
                                    <i class="far fa-calendar-times mr-1"></i>
                                    <?= date('d F Y', strtotime($current_reservation['tanggal_checkout'])) ?>
                                </p>
                            </div>
                        </div>

                        <form method="POST" class="pt-6 border-t border-dashed border-gray-200">
                            <input type="hidden" name="id_reservasi" value="<?= $current_reservation['id_reservasi'] ?>">
                            <input type="hidden" name="id_kamar" value="<?= $current_reservation['id_kamar_ditempati'] ?>">
                            <input type="hidden" id="harga_per_malam" name="harga_per_malam" value="<?= $current_reservation['harga_per_malam'] ?>">
                            <input type="hidden" id="old_checkout_date" name="old_checkout_date" value="<?= $current_reservation['tanggal_checkout'] ?>">
                            <input type="hidden" name="old_total_cost" value="<?= $current_reservation['total_biaya_kamar'] ?>">

                            <div class="mb-6">
                                <label class="block text-sm font-black text-gray-700 mb-2 uppercase tracking-tight">Pilih Tanggal Check-out Baru</label>
                                <input type="date" name="new_checkout_date" id="new_checkout_date"
                                       min="<?= date('Y-m-d', strtotime($current_reservation['tanggal_checkout'] . ' +1 day')) ?>"
                                       class="w-full p-4 border-2 border-emerald-100 bg-emerald-50 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none font-bold text-gray-800 text-lg transition-all" required>
                            </div>

                            <div id="calc_box" class="hidden bg-gray-900 rounded-2xl p-6 text-white flex justify-between items-center shadow-xl mb-6 animate-in fade-in duration-500">
                                <div>
                                    <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Tambahan Biaya ( <span id="label_durasi">0</span> Malam )</p>
                                    <h3 class="text-3xl font-black text-emerald-400" id="label_tambahan">Rp 0</h3>
                                </div>
                                <button type="submit" name="proses_extend" onclick="return confirm('Konfirmasi perpanjangan menginap?')" 
                                        class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-4 rounded-xl font-black transition-all transform hover:scale-105 shadow-lg">
                                    PROSES EXTEND <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white p-20 rounded-2xl border-2 border-dashed border-gray-200 text-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-calendar-plus text-3xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-400 font-medium max-w-xs mx-auto">Cari tamu berdasarkan Kode Booking atau Nama untuk melakukan perpanjangan.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script>
        // Live Biaya Calculator
        const inputNewDate = document.getElementById('new_checkout_date');
        const oldDateVal = document.getElementById('old_checkout_date').value;
        const hargaVal = document.getElementById('harga_per_malam').value;
        const calcBox = document.getElementById('calc_box');
        const labelDurasi = document.getElementById('label_durasi');
        const labelTambahan = document.getElementById('label_tambahan');

        if(inputNewDate) {
            inputNewDate.addEventListener('change', function() {
                const date1 = new Date(oldDateVal);
                const date2 = new Date(this.value);
                
                const diffTime = date2 - date1;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays > 0) {
                    const totalTambahan = diffDays * hargaVal;
                    labelDurasi.innerText = diffDays;
                    labelTambahan.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalTambahan);
                    calcBox.classList.remove('hidden');
                } else {
                    calcBox.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>