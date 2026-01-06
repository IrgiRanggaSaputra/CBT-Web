<?php
require_once '../config.php';
check_login_peserta();

$id_jadwal = (int)$_GET['id'];
$peserta_id = $_SESSION['peserta_id'];

// Get test info
$query = "SELECT * FROM jadwal_tes WHERE id_jadwal = $id_jadwal";
$tes = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$tes) {
    alert('Jadwal tes tidak ditemukan!', 'danger');
    redirect('peserta/dashboard.php');
}

// Check or create peserta_tes
$check = mysqli_query($conn, "SELECT * FROM peserta_tes WHERE id_jadwal = $id_jadwal AND id_peserta = $peserta_id");

if (mysqli_num_rows($check) > 0) {
    $peserta_tes = mysqli_fetch_assoc($check);
    $id_peserta_tes = $peserta_tes['id_peserta_tes'];
    
    if ($peserta_tes['status_tes'] == 'selesai') {
        alert('Anda sudah menyelesaikan tes ini!', 'info');
        redirect('peserta/dashboard.php');
    }
} else {
    // Create new peserta_tes record
    $token = bin2hex(random_bytes(16));
    $waktu_mulai = date('Y-m-d H:i:s');
    
    $insert = "INSERT INTO peserta_tes (id_jadwal, id_peserta, token, status_tes, waktu_mulai) 
               VALUES ($id_jadwal, $peserta_id, '$token', 'sedang_tes', '$waktu_mulai')";
    mysqli_query($conn, $insert);
    $id_peserta_tes = mysqli_insert_id($conn);
}

// Get questions for this test
$query_soal = "SELECT bs.* FROM soal_tes st
               JOIN bank_soal bs ON st.id_soal = bs.id_soal
               WHERE st.id_jadwal = $id_jadwal
               ORDER BY st.nomor_urut";
$result_soal = mysqli_query($conn, $query_soal);

// If no questions assigned, randomly select from category
if (mysqli_num_rows($result_soal) == 0) {
    $query_random = "SELECT * FROM bank_soal WHERE id_kategori = {$tes['id_kategori']} ORDER BY RAND() LIMIT {$tes['jumlah_soal']}";
    $result_soal = mysqli_query($conn, $query_random);
    
    // Save to soal_tes
    $no = 1;
    mysqli_data_seek($result_soal, 0);
    while ($soal = mysqli_fetch_assoc($result_soal)) {
        mysqli_query($conn, "INSERT INTO soal_tes (id_jadwal, id_soal, nomor_urut) VALUES ($id_jadwal, {$soal['id_soal']}, $no)");
        $no++;
    }
    
    // Re-query
    mysqli_data_seek($result_soal, 0);
}

// Get existing answers
$jawaban_peserta = [];
$query_jawaban = "SELECT * FROM jawaban_peserta WHERE id_peserta_tes = $id_peserta_tes";
$result_jawaban = mysqli_query($conn, $query_jawaban);
while ($jwb = mysqli_fetch_assoc($result_jawaban)) {
    $jawaban_peserta[$jwb['id_soal']] = $jwb['jawaban'];
}

// Calculate remaining time
$waktu_mulai = strtotime($peserta_tes['waktu_mulai'] ?? date('Y-m-d H:i:s'));
$durasi_detik = $tes['durasi'] * 60;
$waktu_sekarang = time();
$sisa_waktu = $durasi_detik - ($waktu_sekarang - $waktu_mulai);

if ($sisa_waktu <= 0) {
    // Time's up - auto submit
    mysqli_query($conn, "UPDATE peserta_tes SET status_tes = 'selesai', waktu_selesai = NOW() WHERE id_peserta_tes = $id_peserta_tes");
    alert('Waktu habis! Tes otomatis disubmit.', 'warning');
    redirect('peserta/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tes Online - <?php echo $tes['nama_tes']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/peserta.css">
</head>
<body>
    <!-- Timer -->
    <div class="timer-box">
        <div class="text-center">
            <small>Sisa Waktu</small>
            <div class="timer" id="timer"><?php echo floor($sisa_waktu / 60); ?>:<?php echo str_pad($sisa_waktu % 60, 2, '0', STR_PAD_LEFT); ?></div>
        </div>
    </div>

    <div class="container mt-5 mb-5">
        <div class="row">
            <!-- Question Navigation -->
            <div class="col-md-3">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h6><i class="bi bi-grid-3x3-gap-fill"></i> Navigasi Soal</h6>
                    </div>
                    <div class="card-body">
                        <div class="question-nav" id="questionNav">
                            <?php 
                            $no = 1;
                            mysqli_data_seek($result_soal, 0);
                            while ($soal = mysqli_fetch_assoc($result_soal)) {
                                $answered_class = isset($jawaban_peserta[$soal['id_soal']]) ? 'answered' : '';
                                $active_class = $no == 1 ? 'active' : '';
                                echo "<button class='question-nav-btn $answered_class $active_class' onclick='showQuestion($no)'>" . $no . "</button>";
                                $no++;
                            }
                            ?>
                        </div>
                        
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-square-fill text-success"></i> Dijawab</span>
                                <span id="answeredCount">0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="bi bi-square" style="color: #ddd;"></i> Belum Dijawab</span>
                                <span id="unansweredCount"><?php echo mysqli_num_rows($result_soal); ?></span>
                            </div>
                        </div>
                        
                        <button class="btn btn-danger w-100 mt-3" onclick="submitTest()">
                            <i class="bi bi-send-fill"></i> Submit Tes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Question Content -->
            <div class="col-md-9">
                <?php 
                $no = 1;
                mysqli_data_seek($result_soal, 0);
                while ($soal = mysqli_fetch_assoc($result_soal)): 
                    $jawaban = $jawaban_peserta[$soal['id_soal']] ?? '';
                ?>
                <div class="question-box question-item" id="question-<?php echo $no; ?>" style="display: <?php echo $no == 1 ? 'block' : 'none'; ?>;">
                    <h5>Soal No. <?php echo $no; ?></h5>
                    <hr>
                    <p class="lead"><?php echo nl2br($soal['pertanyaan']); ?></p>
                    
                    <?php if ($soal['gambar']): ?>
                        <img src="../assets/uploads/<?php echo $soal['gambar']; ?>" class="img-fluid mb-3" alt="Gambar Soal">
                    <?php endif; ?>
                    
                    <div class="options mt-4">
                        <?php foreach(['A', 'B', 'C', 'D', 'E'] as $opt): 
                            $pilihan = 'pilihan_' . strtolower($opt);
                            if (!empty($soal[$pilihan])):
                        ?>
                        <div class="option-box <?php echo $jawaban == $opt ? 'selected' : ''; ?>" onclick="selectOption(<?php echo $soal['id_soal']; ?>, '<?php echo $opt; ?>', <?php echo $no; ?>)">
                            <input type="radio" name="jawaban_<?php echo $soal['id_soal']; ?>" value="<?php echo $opt; ?>" <?php echo $jawaban == $opt ? 'checked' : ''; ?>>
                            <strong><?php echo $opt; ?>.</strong> <?php echo $soal[$pilihan]; ?>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-secondary" onclick="prevQuestion()" id="prevBtn-<?php echo $no; ?>" <?php echo $no == 1 ? 'disabled' : ''; ?>>
                            <i class="bi bi-arrow-left"></i> Sebelumnya
                        </button>
                        <button class="btn btn-primary" onclick="nextQuestion()" id="nextBtn-<?php echo $no; ?>">
                            <?php echo $no == mysqli_num_rows($result_soal) ? 'Selesai' : 'Selanjutnya'; ?> <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <?php 
                $no++;
                endwhile; 
                ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentQuestion = 1;
        const totalQuestions = <?php echo mysqli_num_rows($result_soal); ?>;
        const idPesertaTes = <?php echo $id_peserta_tes; ?>;
        let sisaWaktu = <?php echo $sisa_waktu; ?>;
        
        // Timer countdown
        setInterval(function() {
            sisaWaktu--;
            
            let minutes = Math.floor(sisaWaktu / 60);
            let seconds = sisaWaktu % 60;
            
            $('#timer').text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);
            
            if (sisaWaktu <= 300) { // 5 minutes
                $('#timer').removeClass('warning').addClass('danger');
            } else if (sisaWaktu <= 600) { // 10 minutes
                $('#timer').addClass('warning');
            }
            
            if (sisaWaktu <= 0) {
                submitTest();
            }
        }, 1000);
        
        // Show specific question
        function showQuestion(num) {
            $('.question-item').hide();
            $('#question-' + num).show();
            currentQuestion = num;
            
            $('.question-nav-btn').removeClass('active');
            $('.question-nav-btn').eq(num - 1).addClass('active');
        }
        
        // Next question
        function nextQuestion() {
            if (currentQuestion < totalQuestions) {
                showQuestion(currentQuestion + 1);
            } else {
                submitTest();
            }
        }
        
        // Previous question
        function prevQuestion() {
            if (currentQuestion > 1) {
                showQuestion(currentQuestion - 1);
            }
        }
        
        // Select option
        function selectOption(idSoal, jawaban, questionNum) {
            // Update UI
            $('#question-' + questionNum + ' .option-box').removeClass('selected');
            $(event.target).closest('.option-box').addClass('selected');
            $('input[name="jawaban_' + idSoal + '"]').prop('checked', false);
            $(event.target).find('input').prop('checked', true);
            
            // Mark as answered
            $('.question-nav-btn').eq(questionNum - 1).addClass('answered');
            
            // Update count
            updateAnswerCount();
            
            // Auto save via AJAX
            $.post('tes_save.php', {
                id_peserta_tes: idPesertaTes,
                id_soal: idSoal,
                jawaban: jawaban
            }, function(response) {
                console.log('Jawaban tersimpan');
            });
        }
        
        // Update answer count
        function updateAnswerCount() {
            let answered = $('.question-nav-btn.answered').length;
            let unanswered = totalQuestions - answered;
            $('#answeredCount').text(answered);
            $('#unansweredCount').text(unanswered);
        }
        
        // Submit test
        function submitTest() {
            $.post('tes_submit.php', {
                id_peserta_tes: idPesertaTes
            }, function(response) {
                window.location.href = 'dashboard.php';
            });
        }
        
        // Prevent back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
        
        // Prevent right click
        document.addEventListener('contextmenu', event => event.preventDefault());
        
        // Initial count
        updateAnswerCount();
    </script>
</body>
</html>
