<?php
session_start();
require '../koneksi.php';

// 1. Penjaga (Guard)
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// 2. Cek ID
if (!isset($_GET['id'])) {
    header("Location: daftar_kelola_tipekamar.php");
    exit;
}

$id_tipe = (int) $_GET['id']; // Pastikan ID di-cast ke integer

// 3. PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- KEAMANAN: Sanitasi Input POST ---
    $nama_tipe          = htmlspecialchars(trim($_POST['nama_tipe']));
    $harga_per_malam    = (int) $_POST['harga_per_malam']; // Cast ke integer
    $kapasitas          = (int) $_POST['kapasitas'];
    $luas_kamar         = htmlspecialchars(trim($_POST['luas_kamar']));
    $kategori_hunian    = htmlspecialchars($_POST['kategori_hunian']);
    $tingkat_fasilitas  = htmlspecialchars($_POST['tingkat_fasilitas']);
    $deskripsi          = htmlspecialchars(trim($_POST['deskripsi']));
    $add_on             = htmlspecialchars(trim($_POST['add_on']));
    $old_foto           = $_POST['old_foto'];

    // --- LOGIKA: MENGGABUNGKAN CHECKBOX MENJADI STRING ---
    $input_bed = $_POST['jenis_tempat_tidur'] ?? [];
    $jenis_tempat_tidur = implode(", ", $input_bed); 
    
    // 🔥 PENAMBAHAN: Pengecekan Duplikat (Kecuali Data Milik Sendiri)
    $cek_stmt = $conn->prepare("SELECT id_tipe_kamar FROM tipe_kamar WHERE nama_tipe = ? AND id_tipe_kamar != ?");
    $cek_stmt->bind_param("si", $nama_tipe, $id_tipe);
    $cek_stmt->execute();
    $cek_result = $cek_stmt->get_result();

    if ($cek_result->num_rows > 0) {
        $cek_stmt->close();
        $_SESSION['flash_message'] = [
            'type' => 'error', 
            'text' => "Gagal! Nama Tipe Kamar **$nama_tipe** sudah ada. Silakan gunakan nama lain."
        ];
        header("Location: edit_tipe_kamar.php?id=$id_tipe"); // Kembali ke halaman edit
        exit;
    }
    $cek_stmt->close();


    $nama_foto = $old_foto; // Default: Gunakan nama foto lama

    // Logika Foto Baru
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_name = $_FILES["foto"]["name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // --- KEAMANAN TAMBAHAN: Validasi Ukuran File (Max 5MB) ---
        $max_size = 5 * 1024 * 1024; // 5 MB
        if ($_FILES['foto']['size'] > $max_size) {
            $_SESSION['flash_message'] = [
                'type' => 'error', 
                'text' => "Gagal: Ukuran file foto maksimal 5MB."
            ];
            header("Location: edit_tipe_kamar.php?id=$id_tipe");
            exit;
        }
        
        // --- KEAMANAN: Validasi Ekstensi File ---
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed_ext)) {
            $_SESSION['flash_message'] = [
                'type' => 'error', 
                'text' => "Gagal: Hanya file JPG, JPEG, PNG, atau WEBP yang diizinkan."
            ];
            header("Location: edit_tipe_kamar.php?id=$id_tipe");
            exit;
        }

        // --- KEAMANAN: Penamaan File Unik ---
        $nama_foto = "tipe_kamar_" . time() . "_" . uniqid() . "." . $ext;
        
        // Coba upload file baru
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $nama_foto)) {
            
            // --- LOGIKA PENGHAPUSAN FOTO LAMA FISIK ---
            if ($old_foto && $old_foto != 'default.jpg' && file_exists($target_dir . $old_foto)) {
                unlink($target_dir . $old_foto);
            }

        } else {
            $_SESSION['flash_message'] = [
                'type' => 'error', 
                'text' => "Gagal mengunggah file foto baru."
            ];
            header("Location: edit_tipe_kamar.php?id=$id_tipe");
            exit;
        }

        // Jika ganti foto, gunakan query UPDATE yang mencakup kolom 'foto'
        $query = "UPDATE tipe_kamar SET 
                      nama_tipe=?, harga_per_malam=?, kapasitas=?, luas_kamar=?, foto=?, 
                      deskripsi=?, add_on=?, kategori_hunian=?, tingkat_fasilitas=?, jenis_tempat_tidur=? 
                    WHERE id_tipe_kamar=?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siisssssssi", 
            $nama_tipe, $harga_per_malam, $kapasitas, $luas_kamar, $nama_foto, 
            $deskripsi, $add_on, $kategori_hunian, $tingkat_fasilitas, $jenis_tempat_tidur, $id_tipe
        );

    } else {
        // JIKA TIDAK GANTI FOTO, gunakan query UPDATE tanpa kolom 'foto'
        $query = "UPDATE tipe_kamar SET 
                      nama_tipe=?, harga_per_malam=?, kapasitas=?, luas_kamar=?, 
                      deskripsi=?, add_on=?, kategori_hunian=?, tingkat_fasilitas=?, jenis_tempat_tidur=? 
                    WHERE id_tipe_kamar=?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siissssssi", 
            $nama_tipe, $harga_per_malam, $kapasitas, $luas_kamar, 
            $deskripsi, $add_on, $kategori_hunian, $tingkat_fasilitas, $jenis_tempat_tidur, $id_tipe
        );
    }

    if ($stmt->execute()) {
        $_SESSION['flash_message'] = [
            'type' => 'success', 
            'text' => "Tipe Kamar **$nama_tipe** berhasil diperbarui."
        ];
        $stmt->close(); // Tutup statement sebelum redirect
        header("Location: daftar_kelola_tipekamar.php");
        exit;
    } else {
        error_log("Error update tipe kamar: " . $stmt->error);
        $_SESSION['flash_message'] = [
            'type' => 'error', 
            'text' => "Gagal memperbarui data. Error: " . $stmt->error
        ];
        $stmt->close(); // Tutup statement sebelum redirect
        header("Location: edit_tipe_kamar.php?id=$id_tipe"); // Kembali ke halaman edit
        exit;
    }
}

// 4. AMBIL DATA LAMA
// (Logika ini tetap di luar blok POST agar data lama ditampilkan saat pertama kali diakses atau saat error redirect dari POST)
$stmt = $conn->prepare("SELECT * FROM tipe_kamar WHERE id_tipe_kamar = ?");
$stmt->bind_param("i", $id_tipe);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Data tipe kamar tidak ditemukan.";
    exit;
}
$stmt->close(); // Tutup statement SELECT

// --- PERSIAPAN DATA CHECKBOX ---
$bed_dimiliki = explode(", ", $data['jenis_tempat_tidur']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tipe Kamar - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        
        <nav class="w-64 bg-gray-800 text-white p-6">
            <h2 class="text-2xl font-bold mb-8">Admin CNI</h2>
            <ul class="space-y-4">
                <li><a href="dashboard_admin.php" class="flex items-center p-2 hover:bg-gray-700 rounded-lg"><span class="mr-2">🏠</span>Dashboard</a></li>
                <li><a href="daftar_kelola_tipekamar.php" class="flex items-center p-2 bg-gray-700 rounded-lg"><span class="mr-2">🛏️</span>Kelola Tipe Kamar</a></li>
                <li><a href="daftar_kelola_kamar.php" class="flex items-center p-2 hover:bg-gray-700 rounded-lg"><span class="mr-2">✨</span>Kelola Kamar</a></li>
                <li><a href="daftar_kelola_dataCustomer.php" class="flex items-center p-2 hover:bg-gray-700 rounded-lg"><span class="mr-2">👥</span>Data Customer</a></li>
                <li><a href="daftar_kelola_transaksi.php" class="flex items-center p-2 hover:bg-gray-700 rounded-lg"><span class="mr-2">💳</span>Data Transaksi</a></li>
                <li><a href="laporan.php" class="flex items-center p-2 hover:bg-gray-700 rounded-lg"><span class="mr-2">📈</span>Laporan Transaksi</a></li>
                <li class="absolute bottom-6 w-52"><a href="../logout.php" class="flex items-center p-2 bg-red-600 hover:bg-red-700 rounded-lg"><span class="mr-2">🚪</span>Logout</a></li>
            </ul>
        </nav>

        <main class="flex-1 p-8 overflow-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Edit Tipe Kamar</h1>
                <a href="daftar_kelola_tipekamar.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Kembali</a>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="old_foto" value="<?php echo htmlspecialchars($data['foto']); ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Nama Tipe Kamar</label>
                            <input type="text" name="nama_tipe" class="w-full border rounded p-2" value="<?php echo htmlspecialchars($data['nama_tipe']); ?>" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Harga per Malam</label>
                            <input type="number" name="harga_per_malam" min="0" class="w-full border rounded p-2" value="<?php echo htmlspecialchars($data['harga_per_malam']); ?>" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Kategori Hunian</label>
                            <select id="kategori_hunian" name="kategori_hunian" class="w-full border rounded p-2 bg-white" onchange="updateSpesifikasi()">
                                <option value="Single" <?php echo ($data['kategori_hunian'] == 'Single') ? 'selected' : ''; ?>>Single Room (1 tamu)</option>
                                <option value="Double" <?php echo ($data['kategori_hunian'] == 'Double') ? 'selected' : ''; ?>>Double Room (2 tamu)</option>
                                <option value="Family" <?php echo ($data['kategori_hunian'] == 'Family') ? 'selected' : ''; ?>>Family Room</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Kapasitas (Orang)</label>
                            <input type="number" id="kapasitas" name="kapasitas" class="w-full border rounded p-2 bg-gray-100" value="<?php echo htmlspecialchars($data['kapasitas']); ?>" readonly required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Luas Kamar</label>
                            <input type="text" id="luas_kamar" name="luas_kamar" class="w-full border rounded p-2 bg-gray-100" value="<?php echo htmlspecialchars($data['luas_kamar']); ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Jenis Tempat Tidur (Boleh pilih lebih dari 1)</label>
                        <div class="bg-white border rounded p-3 grid grid-cols-2 gap-2">
                            
                            <?php 
                            $bed_options = ["Single Bed", "Double Bed", "Queen Size Bed", "King Size Bed", "Twin Bed", "Bunk Bed", "Extra Bed"];
                            foreach ($bed_options as $bed): 
                            ?>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="<?php echo $bed; ?>" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array($bed, $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span><?php echo $bed; ?></span>
                            </label>
                            <?php endforeach; ?>

                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Tingkat Fasilitas</label>
                        <select id="tingkat_fasilitas" name="tingkat_fasilitas" class="w-full border rounded p-2 bg-white" onchange="updateDeskripsi(true)">
                            <option value="Standard" <?php echo ($data['tingkat_fasilitas'] == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                            <option value="Superior" <?php echo ($data['tingkat_fasilitas'] == 'Superior') ? 'selected' : ''; ?>>Superior</option>
                            <option value="Deluxe" <?php echo ($data['tingkat_fasilitas'] == 'Deluxe') ? 'selected' : ''; ?>>Deluxe</option>
                            <option value="Executive Suite" <?php echo ($data['tingkat_fasilitas'] == 'Executive Suite') ? 'selected' : ''; ?>>Executive Suite</option>
                            <option value="Family Room" <?php echo ($data['tingkat_fasilitas'] == 'Family Room') ? 'selected' : ''; ?>>Family Room</option>
                            <option value="Smoking Room" <?php echo ($data['tingkat_fasilitas'] == 'Smoking Room') ? 'selected' : ''; ?>>Smoking Room</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Deskripsi Fasilitas</label>
                        <textarea id="deskripsi" name="deskripsi" class="w-full border rounded p-2 h-24"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Add on (Pisahkan koma)</label>
                        <input type="text" name="add_on" class="w-full border rounded p-2" value="<?php echo htmlspecialchars($data['add_on']); ?>">
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Foto (Biarkan kosong jika tidak ingin mengubah)</label>
                        <div class="flex items-center gap-4">
                            <img src="../uploads/<?php echo htmlspecialchars($data['foto']); ?>" class="h-20 w-20 object-cover rounded border" alt="Foto Lama">
                            <input type="file" name="foto" class="w-full">
                        </div>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold">Update Data</button>
                </form>
            </div>
        </main>
    </div>

    <script>
    // FUNGSI 1: Mengatur Kapasitas & Luas (Otomatisasi)
    function updateSpesifikasi() {
        let kategori = document.getElementById("kategori_hunian").value;
        let inputKapasitas = document.getElementById("kapasitas");
        let inputLuas = document.getElementById("luas_kamar");

        let kapasitas = 0;
        let luas = "";

        if (kategori === "Single") {
            kapasitas = 1;
            luas = "20 m²"; 
        } else if (kategori === "Double") {
            kapasitas = 2;
            luas = "32 m²";
        } else if (kategori === "Family") {
            kapasitas = 4; 
            luas = "45 m²";
        }

        inputKapasitas.value = kapasitas;
        inputLuas.value = luas;
    }

    // FUNGSI 2: Mengatur Deskripsi Fasilitas Otomatis
    function updateDeskripsi(forceUpdate = true) {
        let fasilitas = document.getElementById("tingkat_fasilitas").value;
        let deskripsiBox = document.getElementById("deskripsi");
        let teks = "";

        // JIKA TIDAK DIPAKSA UPDATE DAN TEXTAREA SUDAH ADA ISI (dari DB), JANGAN DITIMPA
        if (!forceUpdate && deskripsiBox.value.trim() !== "") {
            return; 
        }
        
        switch(fasilitas){
            case "Standard":
                teks = "Fasilitas Dasar: AC, TV Kabel 32 inch, Wi-Fi gratis, Kamar mandi shower (Hot/Cold), Air mineral botol, dan Perlengkapan mandi dasar.";
                break;
            case "Superior":
                teks = "Upgrade dari Standard: Lokasi kamar dengan view lebih baik, tambahan fasilitas pembuat Kopi/Teh (Coffee Maker), dan Meja kerja compact.";
                break;
            case "Deluxe":
                teks = "Ukuran lebih luas dengan Balkon pribadi. Fasilitas mencakup: Bathtub, Kulkas mini (Minibar), Hairdryer, TV 40 inch, dan Brankas pribadi.";
                break;
            case "Executive Suite":
                 teks = "Tingkat premium: Ruang tamu terpisah, tempat tidur King Size, meja makan, dan akses lounge eksklusif. Menyediakan fasilitas Deluxe PLUS bathrobe dan sepatu kamar premium.";
                 break;
            case "Family Room":
                teks = "Kamar Keluarga: Ruangan sangat luas dengan area duduk (Sofa). Dilengkapi fasilitas hiburan Smart TV 50 inch, Microwave, dan Meja makan kecil.";
                break;
            case "Smoking Room":
                teks = "Kamar Khusus Merokok: Memiliki ventilasi udara khusus (Exhaust fan) atau akses langsung ke balkon terbuka. Dilengkapi asbak dan area sirkulasi udara yang baik.";
                break;
            default: 
                teks = ""; 
        }
        
        deskripsiBox.value = teks; 
    }

    // Panggil kedua fungsi saat dokumen dimuat.
    document.addEventListener('DOMContentLoaded', () => {
        updateSpesifikasi(); 
        updateDeskripsi(false);
    });
    </script>
</body>
</html>