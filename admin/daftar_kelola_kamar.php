<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard)
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// =================================================================
// LOGIKA DATABASE (CRUD: CREATE, UPDATE, DELETE)
// =================================================================

$action = $_POST['aksi'] ?? $_GET['aksi'] ?? null;

if ($action) {
    // --- 1. LOGIKA TAMBAH DATA ---
    if ($action == 'tambah') {
        $nomor   = $_POST['nomor_kamar'];
        $lantai  = $_POST['lantai'];
        $id_tipe = $_POST['id_tipe_kamar'];
        $status  = $_POST['status'];

        // Cek Duplikat
        $cek = $conn->query("SELECT nomor_kamar FROM kamar WHERE nomor_kamar = '$nomor'");
        if ($cek->num_rows > 0) {
            echo "<script>alert('Gagal! Nomor Kamar $nomor sudah ada.'); window.location='daftar_kelola_kamar.php';</script>";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO kamar (nomor_kamar, lantai, id_tipe_kamar, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $nomor, $lantai, $id_tipe, $status);
        $stmt->execute();
        $stmt->close();
        
        header("Location: daftar_kelola_kamar.php");
        exit;

    // --- 2. LOGIKA UPDATE DATA (EDIT) ---
    } elseif ($action == 'edit') {
        $id_kamar = $_POST['id_kamar'];
        $nomor    = $_POST['nomor_kamar'];
        $lantai   = $_POST['lantai'];
        $id_tipe  = $_POST['id_tipe_kamar'];
        $status   = $_POST['status'];

        // Cek Duplikat (Kecuali punya sendiri)
        $cek = $conn->query("SELECT nomor_kamar FROM kamar WHERE nomor_kamar = '$nomor' AND id_kamar != '$id_kamar'");
        if ($cek->num_rows > 0) {
            echo "<script>alert('Gagal! Nomor sudah dipakai kamar lain.'); window.location='daftar_kelola_kamar.php';</script>";
            exit;
        }

        $stmt = $conn->prepare("UPDATE kamar SET nomor_kamar=?, lantai=?, id_tipe_kamar=?, status=? WHERE id_kamar=?");
        $stmt->bind_param("siisi", $nomor, $lantai, $id_tipe, $status, $id_kamar);
        
        if($stmt->execute()) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location='daftar_kelola_kamar.php';</script>";
        } else {
            echo "<script>alert('Gagal update!');</script>";
        }
        $stmt->close();
        exit;

    // --- 3. LOGIKA HAPUS DATA ---
    } elseif ($action == 'hapus') {
        $id_kamar = $_GET['id'];
        $conn->query("DELETE FROM kamar WHERE id_kamar = $id_kamar");
        header("Location: daftar_kelola_kamar.php");
        exit;
    }
}

// =================================================================
// AMBIL DATA UNTUK TAMPILAN
// =================================================================

// 1. Ambil List Tipe Kamar (Simpan di Array agar bisa dipakai di form Tambah & Edit)
$tipe_result = $conn->query("SELECT id_tipe_kamar, nama_tipe FROM tipe_kamar ORDER BY nama_tipe ASC");
$tipe_list = [];
while($row = $tipe_result->fetch_assoc()) {
    $tipe_list[] = $row;
}

// 2. Ambil Data Kamar untuk Tabel
$query_kamar = "SELECT kamar.*, tipe_kamar.nama_tipe 
                FROM kamar 
                LEFT JOIN tipe_kamar ON kamar.id_tipe_kamar = tipe_kamar.id_tipe_kamar
                ORDER BY kamar.lantai ASC, kamar.nomor_kamar ASC";
$result_kamar = $conn->query($query_kamar);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Kamar</title>
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
                <li><a href="dashboard_admin.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🏠</span>Dashboard</a></li>
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">🛏️</span>Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-3 bg-blue-600 rounded-lg shadow-md"><span class="mr-3 text-xl">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">👥</span>Data Customer</a></li>
                <li><a href="daftar_kelola_transaksi.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">💳</span>Transaksi</a></li>
                <li><a href="laporan.php" class="flex items-center p-3 hover:bg-gray-800 rounded-lg transition-colors"><span class="mr-3 text-xl">📈</span>Laporan</a></li>
                <li class="absolute bottom-6 w-52"><a href="../logout.php" class="flex items-center p-2 bg-red-600 hover:bg-red-700 rounded-lg"><span class="mr-2">🚪</span>Logout</a></li>
            </ul>
        </nav>

        <main class="flex-1 p-6 md:p-8 overflow-y-auto relative">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Unit Kamar</h1>
                    <p class="text-gray-500 mt-1">Atur nomor kamar, lantai, dan status ketersediaan.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-10">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3 text-sm">➕</span>
                        Tambah Unit Kamar
                    </h2>
                </div>
                
                <form action="daftar_kelola_kamar.php" method="POST" class="p-6 md:p-8">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Kamar</label>
                            <input type="text" name="nomor_kamar" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500" placeholder="Cth: 101" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lantai</label>
                            <select name="lantai" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <?php for($i=1; $i<=3; $i++): ?>
                                    <option value="<?= $i ?>">Lantai <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Kamar</label>
                            <select name="id_tipe_kamar" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                                <option value="" disabled selected>-- Pilih Tipe --</option>
                                <?php foreach($tipe_list as $t): ?>
                                    <option value="<?= $t['id_tipe_kamar'] ?>"><?= htmlspecialchars($t['nama_tipe']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Awal</label>
                            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                                <option value="Tersedia">Tersedia</option>
                                <option value="Terisi">Terisi</option>
                                <option value="Kotor">Kotor</option>
                                <option value="Perbaikan">Perbaikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 border-t">
                        <button type="submit" class="bg-blue-600 text-white px-8 py-2.5 rounded-lg hover:bg-blue-700 font-bold shadow-md transition">Simpan Unit</button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800">Daftar Unit Kamar</h2>
                    <span class="text-sm text-gray-500 bg-white border px-3 py-1 rounded-full">Total: <?php echo $result_kamar->num_rows; ?></span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-800 text-white uppercase tracking-wider">
                            <tr>
                                <th class="py-4 px-6">No</th>
                                <th class="py-4 px-6">Nomor</th>
                                <th class="py-4 px-6 text-center">Lantai</th>
                                <th class="py-4 px-6">Tipe</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if ($result_kamar->num_rows > 0): ?>
                                <?php $no=1; while($row = $result_kamar->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50 transition duration-150">
                                    <td class="py-4 px-6 text-gray-500"><?php echo $no++; ?></td>
                                    <td class="py-4 px-6 font-bold text-lg text-gray-800"><?php echo htmlspecialchars($row['nomor_kamar']); ?></td>
                                    <td class="py-4 px-6 text-center"><span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-bold">Lt <?php echo $row['lantai']; ?></span></td>
                                    <td class="py-4 px-6 text-gray-600"><?php echo htmlspecialchars($row['nama_tipe']); ?></td>
                                    
                                    <td class="py-4 px-6 text-center">
                                        <?php 
                                            $st = strtolower(trim($row['status']));
                                            $cls = "bg-blue-100 text-blue-800";
                                            if($st=='tersedia') $cls="bg-green-100 text-green-800 border-green-200 border";
                                            elseif($st=='terisi') $cls="bg-red-100 text-red-800 border-red-200 border";
                                            elseif($st=='kotor') $cls="bg-yellow-200 text-yellow-900 border-yellow-400 border";
                                            elseif($st=='perbaikan') $cls="bg-gray-200 text-gray-800 border-gray-400 border";
                                        ?>
                                        <span class="<?= $cls ?> px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide"><?= $row['status'] ?></span>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            
                                            <button onclick="bukaModalEdit(
                                                '<?= $row['id_kamar'] ?>',
                                                '<?= $row['nomor_kamar'] ?>',
                                                '<?= $row['lantai'] ?>',
                                                '<?= $row['id_tipe_kamar'] ?>',
                                                '<?= $row['status'] ?>'
                                            )" 
                                            class="bg-yellow-400 hover:bg-yellow-500 text-white p-2 rounded-lg shadow-sm transition" title="Edit">
                                                ✏️
                                            </button>
                                            
                                            <a href="daftar_kelola_kamar.php?aksi=hapus&id=<?= $row['id_kamar'] ?>" 
                                               class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-sm transition"
                                               onclick="return confirm('Hapus kamar <?= $row['nomor_kamar'] ?>?');" title="Hapus">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-8 text-gray-500">Belum ada data.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalEdit" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center backdrop-blur-sm transition-opacity duration-300">
        
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 transform scale-100 transition-transform duration-300">
            
            <div class="flex justify-between items-center bg-gray-50 px-6 py-4 border-b rounded-t-xl">
                <h3 class="text-xl font-bold text-gray-800">✏️ Edit Data Kamar</h3>
                <button onclick="tutupModal()" class="text-gray-400 hover:text-gray-600 text-3xl font-bold leading-none">&times;</button>
            </div>

            <form action="daftar_kelola_kamar.php" method="POST" class="p-6">
                
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" id="edit_id_kamar" name="id_kamar">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Kamar</label>
                        <input type="text" id="edit_nomor" name="nomor_kamar" class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
                        <select id="edit_lantai" name="lantai" class="w-full px-4 py-2 border rounded-lg bg-white" required>
                            <?php for($i=1; $i<=3; $i++): ?>
                                <option value="<?= $i ?>">Lantai <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kamar</label>
                        <select id="edit_tipe" name="id_tipe_kamar" class="w-full px-4 py-2 border rounded-lg bg-white" required>
                            <?php foreach($tipe_list as $t): ?>
                                <option value="<?= $t['id_tipe_kamar'] ?>"><?= htmlspecialchars($t['nama_tipe']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="edit_status" name="status" class="w-full px-4 py-2 border rounded-lg bg-white" required>
                            <option value="Tersedia">Tersedia</option>
                            <option value="Terisi">Terisi</option>
                            <option value="Kotor">Kotor</option>
                            <option value="Perbaikan">Perbaikan</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="tutupModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 shadow-md transform hover:-translate-y-0.5 transition">Update Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk membuka modal dan mengisi form dengan data dari tabel
        function bukaModalEdit(id, nomor, lantai, tipe, status) {
            
            // 1. Masukkan data ke dalam input di modal
            document.getElementById('edit_id_kamar').value = id;
            document.getElementById('edit_nomor').value = nomor;
            document.getElementById('edit_lantai').value = lantai;
            document.getElementById('edit_tipe').value = tipe;
            document.getElementById('edit_status').value = status;

            // 2. Tampilkan Modal (Hapus class 'hidden')
            document.getElementById('modalEdit').classList.remove('hidden');
        }

        // Fungsi untuk menutup modal
        function tutupModal() {
            // Sembunyikan Modal (Tambah class 'hidden')
            document.getElementById('modalEdit').classList.add('hidden');
        }

        // Fitur Tambahan: Tutup modal jika klik di area gelap (luar kotak putih)
        window.onclick = function(event) {
            let modal = document.getElementById('modalEdit');
            if (event.target == modal) {
                tutupModal();
            }
        }
    </script>
</body>
</html>