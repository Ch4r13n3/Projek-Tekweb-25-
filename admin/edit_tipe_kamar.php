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

$id_tipe = $_GET['id'];

// 3. PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_tipe          = $_POST['nama_tipe'];
    $harga_per_malam    = $_POST['harga_per_malam'];
    $kapasitas          = $_POST['kapasitas'];
    $luas_kamar         = $_POST['luas_kamar'];
    $kategori_hunian    = $_POST['kategori_hunian'];
    $tingkat_fasilitas  = $_POST['tingkat_fasilitas'];
    $deskripsi          = $_POST['deskripsi'];
    $add_on             = $_POST['add_on'];

    // --- LOGIKA BARU: MENGGABUNGKAN CHECKBOX MENJADI STRING ---
    // Ambil array dari checkbox, jika kosong set array kosong
    $input_bed = $_POST['jenis_tempat_tidur'] ?? [];
    // Gabungkan array menjadi string dipisah koma (Cth: "King Size, Single Bed")
    $jenis_tempat_tidur = implode(", ", $input_bed); 
    
    // Logika Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // JIKA GANTI FOTO
        $target_dir = "../uploads/";
        $ext = pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION);
        $nama_foto = "tipe_kamar_" . time() . "." . $ext;
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $nama_foto);

        // Hapus foto lama fisik jika perlu (Code optional)
        // ...

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
        // JIKA TIDAK GANTI FOTO
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
        echo "<script>alert('Data berhasil diperbarui!'); window.location='daftar_kelola_tipekamar.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    exit;
}

// 4. AMBIL DATA LAMA
$stmt = $conn->prepare("SELECT * FROM tipe_kamar WHERE id_tipe_kamar = ?");
$stmt->bind_param("i", $id_tipe);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Data tipe kamar tidak ditemukan.";
    exit;
}

// --- PERSIAPAN DATA CHECKBOX ---
// Pecah string dari database menjadi array agar bisa dicek satu-satu
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
                            <input type="number" name="harga_per_malam" class="w-full border rounded p-2" value="<?php echo htmlspecialchars($data['harga_per_malam']); ?>" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Kategori Hunian</label>
                            <select id="kategori_hunian" name="kategori_hunian" class="w-full border rounded p-2 bg-white" onchange="updateSpesifikasi()">
                                <option value="Single" <?php echo ($data['kategori_hunian'] == 'Single') ? 'selected' : ''; ?>>Single Room (1 tamu)</option>
                                <option value="Double" <?php echo ($data['kategori_hunian'] == 'Double') ? 'selected' : ''; ?>>Double Room (2 tamu)</option>
                                <option value="Connecting Room" <?php echo ($data['kategori_hunian'] == 'Connecting Room') ? 'selected' : ''; ?>>Connecting Room</option>
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
                            
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="Single Bed" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array("Single Bed", $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span>Single Bed</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="Double Bed" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array("Double Bed", $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span>Double Bed</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="Queen Size Bed" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array("Queen Size Bed", $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span>Queen Size</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="King Size Bed" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array("King Size Bed", $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span>King Size</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="Twin Bed" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array("Twin Bed", $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span>Twin Bed</span>
                            </label>

                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="Bunk Bed" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array("Bunk Bed", $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span>Bunk Bed</span>
                            </label>

                             <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="jenis_tempat_tidur[]" value="Extra Bed" 
                                class="form-checkbox h-4 w-4 text-blue-600"
                                <?php echo in_array("Extra Bed", $bed_dimiliki) ? 'checked' : ''; ?>>
                                <span>Extra Bed Available</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Tingkat Fasilitas</label>
                        <select id="tingkat_fasilitas" name="tingkat_fasilitas" class="w-full border rounded p-2 bg-white" onchange="updateDeskripsi()">
                            <option value="Standard" <?php echo ($data['tingkat_fasilitas'] == 'Standard') ? 'selected' : ''; ?>>Standard</option>
                            <option value="Superior" <?php echo ($data['tingkat_fasilitas'] == 'Superior') ? 'selected' : ''; ?>>Superior</option>
                            <option value="Deluxe" <?php echo ($data['tingkat_fasilitas'] == 'Deluxe') ? 'selected' : ''; ?>>Deluxe</option>
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
                            <img src="../uploads/<?php echo htmlspecialchars($data['foto']); ?>" class="h-20 w-20 object-cover rounded border">
                            <input type="file" name="foto" class="w-full">
                        </div>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 font-bold">Update Data</button>
                </form>
            </div>
        </main>
    </div>

    <script>
    // FUNGSI 1: Mengatur Kapasitas & Luas (Otomatisasi Connecting Room)
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
            kapasitas = 3; 
            luas = "45 m²";
        } else if (kategori === "Connecting Room") {
            // LOGIKA CONNECTING ROOM: Kapasitas & Luas dikali 2
            kapasitas = 4;
            luas = "64 m² (2 x 32 m²)";
        }

        inputKapasitas.value = kapasitas;
        inputLuas.value = luas;
    }

    // FUNGSI 2: Mengatur Deskripsi Fasilitas Otomatis
    function updateDeskripsi() {
        let fasilitas = document.getElementById("tingkat_fasilitas").value;
        let kategoriHunian = document.getElementById("kategori_hunian").value; 
        let deskripsiBox = document.getElementById("deskripsi");
        let teks = "";

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
            case "Family Room":
                teks = "Kamar Keluarga: Ruangan sangat luas dengan area duduk (Sofa). Dilengkapi fasilitas hiburan Smart TV 50 inch, Microwave, dan Meja makan kecil.";
                break;
            case "Smoking Room":
                teks = "Kamar Khusus Merokok: Memiliki ventilasi udara khusus (Exhaust fan) atau akses langsung ke balkon terbuka. Dilengkapi asbak dan area sirkulasi udara yang baik.";
                break;
            default: 
                teks = ""; 
        }

        // TAMBAHAN KHUSUS: Jika Connecting Room
        if (kategoriHunian === "Connecting Room") {
            teks += "\n\nCATATAN CONNECTING: Unit ini terdiri dari 2 kamar terpisah yang memiliki pintu penghubung di tengah (Connecting Door). Anda akan mendapatkan 2 kamar mandi dan privasi ganda.";
        }
        
        deskripsiBox.value = teks; 
    }
    </script>
</body>
</html>