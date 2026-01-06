<?php
require_once '../config.php';
check_login_admin();

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
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $file = $_FILES['gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($ext, $allowed)) {
            $gambar = time() . '_' . $file['name'];
            move_uploaded_file($file['tmp_name'], '../assets/uploads/' . $gambar);
        }
    }
    
    $query = "INSERT INTO bank_soal (id_kategori, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, pilihan_e, jawaban_benar, bobot, gambar) 
              VALUES ($id_kategori, '$pertanyaan', '$pilihan_a', '$pilihan_b', '$pilihan_c', '$pilihan_d', '$pilihan_e', '$jawaban_benar', $bobot, '$gambar')";
    
    if (mysqli_query($conn, $query)) {
        alert('Soal berhasil ditambahkan!', 'success');
        redirect('admin/bank_soal.php');
    } else {
        alert('Gagal menambahkan soal: ' . mysqli_error($conn), 'danger');
    }
}

$kategori_list = mysqli_query($conn, "SELECT * FROM kategori_soal ORDER BY nama_kategori");

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Tambah Soal</h2>
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
                        <option value="<?php echo $kat['id_kategori']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                <textarea class="form-control" name="pertanyaan" rows="4" required placeholder="Masukkan pertanyaan soal..."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Gambar Soal (opsional)</label>
                <input type="file" class="form-control" name="gambar" accept="image/*">
                <small class="text-muted">Format: JPG, PNG, GIF (Max 2MB)</small>
            </div>
            
            <hr>
            <h5 class="mb-3">Pilihan Jawaban</h5>
            
            <div class="mb-3">
                <label class="form-label">A. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_a" required placeholder="Pilihan A">
            </div>
            
            <div class="mb-3">
                <label class="form-label">B. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_b" required placeholder="Pilihan B">
            </div>
            
            <div class="mb-3">
                <label class="form-label">C. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_c" required placeholder="Pilihan C">
            </div>
            
            <div class="mb-3">
                <label class="form-label">D. <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="pilihan_d" required placeholder="Pilihan D">
            </div>
            
            <div class="mb-3">
                <label class="form-label">E. (opsional)</label>
                <input type="text" class="form-control" name="pilihan_e" placeholder="Pilihan E">
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Jawaban Benar <span class="text-danger">*</span></label>
                        <select class="form-select" name="jawaban_benar" required>
                            <option value="">Pilih Jawaban</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Bobot Nilai <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="bobot" min="1" value="1" required>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan Soal
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
