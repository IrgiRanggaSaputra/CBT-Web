-- Data Sample untuk Testing CBT LPK
-- Import setelah database.sql

USE cbt_lpk;

-- Sample Peserta
INSERT INTO peserta (nomor_peserta, nama_lengkap, email, password, jenis_kelamin, tanggal_lahir, telepon, alamat, status) VALUES
('P001', 'Ahmad Rizki', 'ahmad@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', '1995-03-15', '081234567890', 'Jl. Merdeka No. 1, Jakarta', 'aktif'),
('P002', 'Siti Nurhaliza', 'siti@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'P', '1997-06-20', '081234567891', 'Jl. Sudirman No. 2, Bandung', 'aktif'),
('P003', 'Budi Santoso', 'budi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', '1996-09-10', '081234567892', 'Jl. Gatot Subroto No. 3, Surabaya', 'aktif'),
('P004', 'Dewi Lestari', 'dewi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'P', '1998-12-05', '081234567893', 'Jl. Ahmad Yani No. 4, Medan', 'aktif'),
('P005', 'Eko Prasetyo', 'eko@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'L', '1994-04-25', '081234567894', 'Jl. Diponegoro No. 5, Semarang', 'aktif');
-- Password semua peserta: password

-- Sample Soal Matematika
INSERT INTO bank_soal (id_kategori, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, pilihan_e, jawaban_benar, bobot) VALUES
(2, 'Berapakah hasil dari 15 + 27?', '40', '41', '42', '43', '44', 'C', 1),
(2, 'Berapakah hasil dari 8 x 9?', '64', '72', '81', '88', '96', 'B', 1),
(2, 'Berapakah hasil dari 144 : 12?', '10', '11', '12', '13', '14', 'C', 1),
(2, 'Berapakah 25% dari 200?', '25', '40', '50', '75', '100', 'C', 1),
(2, 'Jika 3x + 5 = 20, berapakah nilai x?', '3', '4', '5', '6', '7', 'C', 1),
(2, 'Luas persegi dengan sisi 8 cm adalah...', '16 cm²', '32 cm²', '48 cm²', '64 cm²', '72 cm²', 'D', 1),
(2, 'Keliling lingkaran dengan diameter 14 cm (π=22/7) adalah...', '22 cm', '44 cm', '66 cm', '88 cm', '110 cm', 'B', 1),
(2, 'Hasil dari √144 adalah...', '10', '11', '12', '13', '14', 'C', 1),
(2, 'Rata-rata dari 70, 80, 90, 100 adalah...', '80', '82.5', '85', '87.5', '90', 'C', 1),
(2, 'Berapakah 2³ + 3² ?', '15', '16', '17', '18', '19', 'C', 1);

-- Sample Soal Bahasa Indonesia
INSERT INTO bank_soal (id_kategori, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, pilihan_e, jawaban_benar, bobot) VALUES
(1, 'Kata baku dari "apotek" adalah...', 'Apotik', 'Apotek', 'Apotex', 'Apothek', 'Aphotik', 'B', 1),
(1, 'Antonim dari kata "rajin" adalah...', 'Tekun', 'Giat', 'Malas', 'Sibuk', 'Aktif', 'C', 1),
(1, 'Sinonim dari kata "indah" adalah...', 'Cantik', 'Jelek', 'Buruk', 'Kotor', 'Suram', 'A', 1),
(1, 'Kalimat yang benar adalah...', 'Dia pergi ke sekolah', 'Dia ke sekolah pergi', 'Ke sekolah dia pergi', 'Pergi sekolah dia ke', 'Sekolah pergi dia', 'A', 1),
(1, 'Imbuhan yang tepat untuk kata "ajar" menjadi kata kerja adalah...', 'Pe-', 'Ber-', 'Ter-', 'Se-', 'Ke-', 'B', 1);

-- Sample Soal Bahasa Inggris
INSERT INTO bank_soal (id_kategori, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, pilihan_e, jawaban_benar, bobot) VALUES
(3, 'What is the capital of Indonesia?', 'Bandung', 'Surabaya', 'Jakarta', 'Medan', 'Yogyakarta', 'C', 1),
(3, '"She ... to school every day" (Present Simple)', 'go', 'goes', 'going', 'gone', 'went', 'B', 1),
(3, 'What is the past tense of "eat"?', 'eated', 'eat', 'ate', 'eaten', 'eating', 'C', 1),
(3, '"I ... a book now" (Present Continuous)', 'read', 'reads', 'reading', 'am reading', 'have read', 'D', 1),
(3, 'What is the opposite of "big"?', 'Large', 'Huge', 'Small', 'Giant', 'Tall', 'C', 1);

-- Sample Soal Pengetahuan Umum
INSERT INTO bank_soal (id_kategori, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, pilihan_e, jawaban_benar, bobot) VALUES
(4, 'Siapa presiden pertama Republik Indonesia?', 'Soeharto', 'Soekarno', 'Habibie', 'Megawati', 'SBY', 'B', 1),
(4, 'Kapan Indonesia merdeka?', '17 Agustus 1944', '17 Agustus 1945', '17 Agustus 1946', '17 Agustus 1947', '17 Agustus 1948', 'B', 1),
(4, 'Ibu kota negara Jepang adalah...', 'Beijing', 'Seoul', 'Bangkok', 'Tokyo', 'Manila', 'D', 1),
(4, 'Planet terbesar di tata surya adalah...', 'Mars', 'Saturnus', 'Jupiter', 'Uranus', 'Neptunus', 'C', 1),
(4, 'Lambang negara Indonesia adalah...', 'Harimau', 'Gajah', 'Garuda', 'Banteng', 'Naga', 'C', 1);

-- Sample Jadwal Tes
INSERT INTO jadwal_tes (nama_tes, id_kategori, tanggal_mulai, tanggal_selesai, durasi, jumlah_soal, passing_grade, instruksi, status) VALUES
('Tes Matematika Dasar', 2, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 60, 10, 70.00, 
'1. Baca soal dengan teliti\n2. Pilih satu jawaban yang paling tepat\n3. Waktu pengerjaan 60 menit\n4. Jawaban akan tersimpan otomatis', 'aktif'),

('Tes Bahasa Indonesia', 1, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY), 45, 10, 70.00,
'1. Kerjakan dengan jujur\n2. Pilih jawaban yang paling benar\n3. Tidak boleh membuka kamus', 'aktif'),

('Tes Komprehensif', 4, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 90, 20, 75.00,
'Tes ini mencakup berbagai bidang:\n1. Matematika\n2. Bahasa Indonesia\n3. Pengetahuan Umum\n4. Selesaikan dalam waktu 90 menit', 'draft');

-- Assign Peserta ke Jadwal Tes (opsional)
-- INSERT INTO peserta_tes (id_jadwal, id_peserta, token, status_tes) VALUES
-- (1, 1, 'token_001', 'belum_mulai'),
-- (1, 2, 'token_002', 'belum_mulai'),
-- (1, 3, 'token_003', 'belum_mulai');

-- End of sample data
