<?php
require_once '../config.php';
check_login_peserta();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_peserta_tes = (int)$_POST['id_peserta_tes'];
    $id_soal = (int)$_POST['id_soal'];
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    
    // Get correct answer
    $soal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jawaban_benar FROM bank_soal WHERE id_soal = $id_soal"));
    $is_correct = ($jawaban == $soal['jawaban_benar']) ? 1 : 0;
    
    // Check if answer already exists
    $check = mysqli_query($conn, "SELECT * FROM jawaban_peserta WHERE id_peserta_tes = $id_peserta_tes AND id_soal = $id_soal");
    
    if (mysqli_num_rows($check) > 0) {
        // Update
        $query = "UPDATE jawaban_peserta SET jawaban = '$jawaban', is_correct = $is_correct, waktu_jawab = NOW() 
                  WHERE id_peserta_tes = $id_peserta_tes AND id_soal = $id_soal";
    } else {
        // Insert
        $query = "INSERT INTO jawaban_peserta (id_peserta_tes, id_soal, jawaban, is_correct, waktu_jawab) 
                  VALUES ($id_peserta_tes, $id_soal, '$jawaban', $is_correct, NOW())";
    }
    
    mysqli_query($conn, $query);
    
    echo json_encode(['status' => 'success']);
}
?>
