<?php
// 1. Logika untuk menghitung jumlah pembayaran yang perlu diverifikasi
$sql_verif = "SELECT COUNT(*) as total FROM reservasi WHERE status_pembayaran = 'Menunggu Verifikasi'";
$result_verif = $conn->query($sql_verif);
$total_pending_verif = ($result_verif) ? $result_verif->fetch_assoc()['total'] : 0;

// 2. Logika untuk mendeteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="w-64 bg-gray-900 text-white flex flex-col h-full shadow-2xl fixed left-0 top-0">
    <div class="p-6">
        <h2 class="text-3xl font-extrabold tracking-wider text-sky-400">CNI RESEPSIONIS</h2>
    </div>
    
    <ul class="flex-1 px-4 space-y-1 overflow-y-auto">
        <li>
            <a href="dashboard_resepsionis.php" 
               class="flex items-center p-3 rounded-lg font-semibold transition-colors 
               <?= ($current_page == 'dashboard_resepsionis.php') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400' ?>">
                <i class="fas fa-home mr-3 w-5"></i>Dashboard
            </a>
        </li>

        <h6 class="px-3 pt-6 pb-2 text-xs font-semibold uppercase text-gray-500 border-t border-gray-800 mt-4">TRANSAKSI</h6>
        
        <li>
            <a href="walk_in.php" 
               class="flex items-center p-3 rounded-lg transition-colors 
               <?= ($current_page == 'walk_in.php') ? 'hover:bg-gray-800 text-gray-400' : 'hover:bg-gray-800 text-gray-400' ?>">
                <i class="fas fa-hand-holding-usd mr-3 w-5"></i>Reservasi Walk-in
            </a>
        </li>

        <li>
            <a href="reservasi_list.php" 
               class="flex items-center p-3 rounded-lg transition-colors 
               <?= ($current_page == 'reservasi_list.php' && !isset($_GET['status'])) ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400' ?>">
                <i class="fas fa-calendar-check mr-3 w-5"></i>Kelola Reservasi
            </a>
        </li>

        <li>
            <!-- <a href="reservasi_list.php?status=menunggu_verifikasi"  -->
            <a href="verifikasi_bayar.php"
               class="flex items-center p-3 rounded-lg transition-colors 
               <?= (isset($_GET['status']) && $_GET['status'] == 'menunggu_verifikasi') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400' ?>">
                <i class="fas fa-money-check-alt mr-3 w-5"></i>Verifikasi Pembayaran 
                <?php if ($total_pending_verif > 0): ?>
                    <span class="ml-auto bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full ring-2 ring-gray-900"><?= $total_pending_verif; ?></span>
                <?php endif; ?>
            </a>
        </li>

        <h6 class="px-3 pt-6 pb-2 text-xs font-semibold uppercase text-gray-500 border-t border-gray-800 mt-4">OPERASIONAL</h6>
        
        <li>
            <a href="alokasi_kamar.php" 
               class="flex items-center p-3 rounded-lg transition-colors 
               <?= (isset($_GET['status']) && $_GET['status'] == 'Belum Dialokasi') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400' ?>">
                <i class="fas fa-bed mr-3 w-5"></i>Alokasi Kamar
            </a>
        </li>
        
        <li>
            <a href="reservasi_list.php?status=checkin" 
               class="flex items-center p-3 rounded-lg transition-colors 
               <?= (isset($_GET['status']) && $_GET['status'] == 'checkin') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400' ?>">
                <i class="fas fa-door-open mr-3 w-5"></i>Daftar Tamu Menginap
            </a>
        </li>

        <li>
            <a href="extend_stay.php" 
               class="flex items-center p-3 rounded-lg transition-colors 
               <?= ($current_page == 'extend_stay.php') ? 'bg-sky-600 text-white shadow-lg' : 'hover:bg-gray-800 text-gray-400' ?>">
                <i class="fas fa-clock-rotate-left mr-3 w-5"></i>Extended Stay
            </a>
        </li>
    </ul>
    
    <div class="p-4 border-t border-gray-800">
        <a href="../logout.php" class="flex items-center justify-center p-3 bg-red-700 hover:bg-red-800 rounded-lg transition-colors font-semibold shadow-xl">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
</nav>

<div class="w-64 flex-shrink-0"></div>