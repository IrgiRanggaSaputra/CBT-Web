<?php
require_once '../config.php';
check_login_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    alert('ID soal tidak valid!', 'danger');
    redirect('admin/bank_soal.php');
}

// Ambil data soal
$query = "SELECT * FROM bank_soal WHERE id_soal = $id";
$result = mysqli_query($conn, $query);
$soal = mysqli_fetch_assoc($result);

if (!$soal) {
    alert('Soal tidak ditemukan!', 'danger');
    redirect('admin/bank_soal.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kategori = (int)$_POST['id_kategori'];
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $pilihan_a = mysqli_real_escape_string($conn, $_POST['pilihan_a']);
    $pilihan_b = mysqli_real_escape_string($conn, $_POST['pilihan_b']);
    $pilihan_c = mysqli_real_escape_string($conn, $_POST['pilihan_c']);
    $pilihan_d = mysqli_real_escape_string($conn, $_POST['pilihan_d']);
    $pilihan_e = mysqli_real_escape_string($conn, $_POST['pilihan_e']);
    $jawaban_benar = mysqli_real_escape_string($conn, $_POST['jawaban_benar']);
    $bobot = (int)$_POST['bobot'];
    
    // Handle upload gambar
    $gambar = $soal['gambar']; // Keep old image
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowed)) {
            // Delete old image if exists
            if ($soal['gambar'] && file_exists('../assets/uploads/' . $soal['gambar'])) {
                unlink('../assets/uploads/' . $soal['gambar']);
            }
            
            $gambar = time() . '_' . $file['name'];
            move_uploaded_file($file['tmp_name'], '../assets/uploads/' . $gambar);
        }
    }
    
    // Check if user wants to delete image
    if (isset($_POST['hapus_gambar']) && $_POST['hapus_gambar'] == '1') {
        if ($soal['gambar'] && file_exists('../assets/uploads/' . $soal['gambar'])) {
            unlink('../assets/uploads/' . $soal['gambar']);
        }
        $gambar = '';
    }
    
    $query = "UPDATE bank_soal SET 
              id_kategori = $id_kategori,
              pertanyaan = '$pertanyaan',
              pilihan_a = '$pilihan_a',
              pilihan_b = '$pilihan_b',
              pilihan_c = '$pilihan_c',
              pilihan_d = '$pilihan_d',
              pilihan_e = '$pilihan_e',
              jawaban_benar = '$jawaban_benar',
              bobot = $bobot,
              gambar = '$gambar'
              WHERE id_soal = $id";
    
    if (mysqli_query($conn, $query)) {
        alert('Soal berhasil diupdate!', 'success');
        redirect('admin/bank_soal.php');
    } else {
        alert('Gagal mengupdate soal: ' . mysqli_error($conn), 'danger');
    }
}

$kategori_list = mysqli_query($conn, "SELECT * FROM kategori_soal ORDER BY nama_kategori");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Edit Soal</h2>
    <a href="bank_soal.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Kategori Soal <span class="text-danger">*</span></label>
                <select class="form-select" name="id_kategori" required>
                    <option value="">Pilih Kategori</option>
                    <?php while($kat = mysqli_fetch_assoc($kategori_list)): ?>
                        <option value="<?php echo $kat['id_kategori']; ?>" <?php echo $soal['id_kategori'] == $kat['id_kategori'] ? 'selected' : ''; ?>>
                            <?php echo $kat['nama_kategori']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                <textarea class="form-control" name="pertanyaan" rows="4" required placeholder="Masukkan pertanyaan soal..."><?php echo htmlspecialchars($soal['pertanyaan']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Gambar Soal (opsional)</label>
                <?php if ($soal['gambar']): ?>
                    <div class="mb-2">
                        <img src="../assets/uploads/<?php echo $soal['gambar']; ?>" alt="Gambar Soal" style="max-width: 200px;" class="img-thumbnail">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="hapus_gambar" value="1" id="hapusGambar">
                            <label class="form-check-label" for="hapusGambar">
                                Hapus gambar ini
                            </label>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" name="gambar" accept="image/*">
                <small class="text-muted">Format: JPG, PNG, GIF (Max 2MB). Upload gambar baru untuk mengganti gambar lama.</small>
            </div>
            
            <hr>
            <h5 class="mb-3">Pilihan Jawaban</h5>
            
            <div class="mb-3">
                <label class="form-label">A. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_a" required placeholder="Pilihan A" value="<?php echo htmlspecialchars($soal['pilihan_a']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">B. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_b" required placeholder="Pilihan B" value="<?php echo htmlspecialchars($soal['pilihan_b']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">C. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_c" required placeholder="Pilihan C" value="<?php echo htmlspecialchars($soal['pilihan_c']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">D. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_d" required placeholder="Pilihan D" value="<?php echo htmlspecialchars($soal['pilihan_d']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">E. (opsional)</label>
                <input type="text" class="form-control" name="pilihan_e" placeholder="Pilihan E" value="<?php echo htmlspecialchars($soal['pilihan_e']); ?>">
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Jawaban Benar <span class="text-danger">*</span></label>
                        <select class="form-select" name="jawaban_benar" required>
                            <option value="">Pilih Jawaban</option>
                            <option value="A" <?php echo $soal['jawaban_benar'] == 'A' ? 'selected' : ''; ?>>A</option>
                            <option value="B" <?php echo $soal['jawaban_benar'] == 'B' ? 'selected' : ''; ?>>B</option>
                            <option value="C" <?php echo $soal['jawaban_benar'] == 'C' ? 'selected' : ''; ?>>C</option>
                            <option value="D" <?php echo $soal['jawaban_benar'] == 'D' ? 'selected' : ''; ?>>D</option>
                            <option value="E" <?php echo $soal['jawaban_benar'] == 'E' ? 'selected' : ''; ?>>E</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bobot Nilai <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="bobot" min="1" value="<?php echo $soal['bobot']; ?>" required>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update Soal
            </button>
            <a href="bank_soal.php" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Batal
            </a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
