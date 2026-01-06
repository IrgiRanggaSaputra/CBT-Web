<?php
require_once '../config.php';
check_login_peserta();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_peserta_tes = (int)$_POST['id_peserta_tes'];
    
    // Get peserta_tes info
    $peserta_tes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM peserta_tes WHERE id_peserta_tes = $id_peserta_tes"));
    $id_jadwal = $peserta_tes['id_jadwal'];
    
    // Calculate score
    $query_jawaban = "SELECT COUNT(*) as total, SUM(is_correct) as benar 
                      FROM jawaban_peserta 
                      WHERE id_peserta_tes = $id_peserta_tes";
    $result = mysqli_fetch_assoc(mysqli_query($conn, $query_jawaban));
    
    $total_soal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jumlah_soal FROM jadwal_tes WHERE id_jadwal = $id_jadwal"))['jumlah_soal'];
    $nilai = ($result['benar'] / $total_soal) * 100;
    
    // Get passing grade
    $passing_grade = mysqli_fetch_assoc(mysqli_query($conn, "SELECT passing_grade FROM jadwal_tes WHERE id_jadwal = $id_jadwal"))['passing_grade'];
    $status_kelulusan = $nilai >= $passing_grade ? 'lulus' : 'tidak_lulus';
    
    // Update peserta_tes
    $query_update = "UPDATE peserta_tes 
                     SET status_tes = 'selesai', 
                         waktu_selesai = NOW(), 
                         nilai = $nilai, 
                         status_kelulusan = '$status_kelulusan' 
                     WHERE id_peserta_tes = $id_peserta_tes";
    mysqli_query($conn, $query_update);
    
    echo json_encode(['status' => 'success', 'nilai' => $nilai]);
}
?>
