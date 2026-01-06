<?php
require_once '../config.php';
check_login_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data peserta
$query = "SELECT * FROM peserta WHERE id_peserta = $id";
$result = mysqli_query($conn, $query);
$peserta = mysqli_fetch_assoc($result);

if (!$peserta) {
    alert('Peserta tidak ditemukan!', 'danger');
    redirect('admin/peserta.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomor_peserta = mysqli_real_escape_string($conn, $_POST['nomor_peserta']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $telepon = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Update password hanya jika diisi
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE peserta SET 
                  nomor_peserta = '$nomor_peserta',
                  nama_lengkap = '$nama_lengkap',
                  email = '$email',
                  password = '$password',
                  jenis_kelamin = '$jenis_kelamin',
                  tanggal_lahir = '$tanggal_lahir',
                  telepon = '$telepon',
                  alamat = '$alamat',
                  status = '$status'
                  WHERE id_peserta = $id";
    } else {
        $query = "UPDATE peserta SET 
                  nomor_peserta = '$nomor_peserta',
                  nama_lengkap = '$nama_lengkap',
                  email = '$email',
                  jenis_kelamin = '$jenis_kelamin',
                  tanggal_lahir = '$tanggal_lahir',
                  telepon = '$telepon',
                  alamat = '$alamat',
                  status = '$status'
                  WHERE id_peserta = $id";
    }
    
    if (mysqli_query($conn, $query)) {
        alert('Peserta berhasil diupdate!', 'success');
        redirect('admin/peserta.php');
    } else {
        alert('Gagal mengupdate peserta: ' . mysqli_error($conn), 'danger');
    }
}

include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Edit Peserta</h2>
    <a href="peserta.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<?php show_alert(); ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nomor Peserta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nomor_peserta" value="<?php echo htmlspecialchars($peserta['nomor_peserta']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($peserta['nama_lengkap']); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($peserta['email']); ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin">
                            <option value="L" <?php echo $peserta['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="P" <?php echo $peserta['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo $peserta['tanggal_lahir']; ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="telepon" value="<?php echo htmlspecialchars($peserta['telepon']); ?>">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" rows="3"><?php echo htmlspecialchars($peserta['alamat']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="aktif" <?php echo $peserta['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="nonaktif" <?php echo $peserta['status'] == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
