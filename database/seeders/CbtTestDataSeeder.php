<?php

namespace Database\Seeders;

use App\Models\BankSoal;
use App\Models\JadwalTes;
use App\Models\KategoriSoal;
use App\Models\Peserta;
use App\Models\PesertaTes;
use App\Models\SoalTes;
use App\Models\JawabanPeserta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CbtTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to truncate tables safely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        JawabanPeserta::truncate();
        SoalTes::truncate();
        PesertaTes::truncate();
        JadwalTes::truncate();
        BankSoal::truncate();
        KategoriSoal::truncate();
        Peserta::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Kategori Soal
        $kategoriIT = KategoriSoal::create([
            'nama_kategori' => 'Teknologi Informasi',
            'deskripsi' => 'Ujian mengenai dasar-dasar pemrograman, jaringan, dan perangkat lunak.',
        ]);

        $kategoriEnglish = KategoriSoal::create([
            'nama_kategori' => 'Bahasa Inggris',
            'deskripsi' => 'Ujian kompetensi bahasa Inggris mencakup grammar, vocabulary, dan reading.',
        ]);

        $kategoriMath = KategoriSoal::create([
            'nama_kategori' => 'Matematika Dasar',
            'deskripsi' => 'Ujian logika dan perhitungan matematika dasar.',
        ]);

        // 2. Bank Soal - Teknologi Informasi (6 Soal)
        $soalsIT = [
            [
                'pertanyaan' => 'Manakah di bawah ini yang merupakan bahasa pemrograman tingkat tinggi yang sangat populer untuk analisis data dan kecerdasan buatan?',
                'pilihan_a' => 'Assembly',
                'pilihan_b' => 'C',
                'pilihan_c' => 'Python',
                'pilihan_d' => 'HTML',
                'pilihan_e' => 'CSS',
                'jawaban_benar' => 'C',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Protokol apa yang digunakan untuk mengirim dan menerima halaman web secara aman melalui internet?',
                'pilihan_a' => 'FTP',
                'pilihan_b' => 'SMTP',
                'pilihan_c' => 'HTTPS',
                'pilihan_d' => 'SSH',
                'pilihan_e' => 'DHCP',
                'jawaban_benar' => 'C',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Struktur data yang menggunakan prinsip Last-In, First-Out (LIFO) disebut...',
                'pilihan_a' => 'Queue (Antrean)',
                'pilihan_b' => 'Stack (Tumpukan)',
                'pilihan_c' => 'Tree (Pohon)',
                'pilihan_d' => 'Array (Larik)',
                'pilihan_e' => 'Linked List',
                'jawaban_benar' => 'B',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Di bawah ini yang merupakan jenis database NoSQL adalah...',
                'pilihan_a' => 'MySQL',
                'pilihan_b' => 'PostgreSQL',
                'pilihan_c' => 'SQLite',
                'pilihan_d' => 'MongoDB',
                'pilihan_e' => 'Oracle Database',
                'jawaban_benar' => 'D',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Apa kepanjangan dari CPU pada komputer?',
                'pilihan_a' => 'Central Processing Unit',
                'pilihan_b' => 'Computer Power Unit',
                'pilihan_c' => 'Control Program Utility',
                'pilihan_d' => 'Core Process Usability',
                'pilihan_e' => 'Central Program Unit',
                'jawaban_benar' => 'A',
                'bobot' => 10,
            ],
            [
                'pertanyaan' => 'Berikut ini yang bukan merupakan sistem operasi adalah...',
                'pilihan_a' => 'Linux',
                'pilihan_b' => 'macOS',
                'pilihan_c' => 'Windows',
                'pilihan_d' => 'Android',
                'pilihan_e' => 'Vite',
                'jawaban_benar' => 'E',
                'bobot' => 10,
            ],
        ];

        foreach ($soalsIT as $soal) {
            $soal['id_kategori'] = $kategoriIT->id_kategori;
            BankSoal::create($soal);
        }

        // Bank Soal - Bahasa Inggris (5 Soal)
        $soalsEnglish = [
            [
                'pertanyaan' => 'Choose the correct sentence: She ____ to school every day.',
                'pilihan_a' => 'go',
                'pilihan_b' => 'goes',
                'pilihan_c' => 'going',
                'pilihan_d' => 'gone',
                'pilihan_e' => 'went',
                'jawaban_benar' => 'B',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'What is the synonym of the word "Beautiful"?',
                'pilihan_a' => 'Ugly',
                'pilihan_b' => 'Pretty',
                'pilihan_c' => 'Angry',
                'pilihan_d' => 'Sad',
                'pilihan_e' => 'Smart',
                'jawaban_benar' => 'B',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'I ____ my homework when the lights went out last night.',
                'pilihan_a' => 'was doing',
                'pilihan_b' => 'did',
                'pilihan_c' => 'do',
                'pilihan_d' => 'am doing',
                'pilihan_e' => 'done',
                'jawaban_benar' => 'A',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Which of the following is an adverb of frequency?',
                'pilihan_a' => 'Quickly',
                'pilihan_b' => 'Under',
                'pilihan_c' => 'Always',
                'pilihan_d' => 'Happiness',
                'pilihan_e' => 'Beautifully',
                'jawaban_benar' => 'C',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'If it rains tomorrow, we ____ cancel the picnic.',
                'pilihan_a' => 'would',
                'pilihan_b' => 'will',
                'pilihan_c' => 'had',
                'pilihan_d' => 'did',
                'pilihan_e' => 'are',
                'jawaban_benar' => 'B',
                'bobot' => 20,
            ],
        ];

        foreach ($soalsEnglish as $soal) {
            $soal['id_kategori'] = $kategoriEnglish->id_kategori;
            BankSoal::create($soal);
        }

        // Bank Soal - Matematika Dasar (5 Soal)
        $soalsMath = [
            [
                'pertanyaan' => 'Berapakah hasil dari 25% dari 120?',
                'pilihan_a' => '20',
                'pilihan_b' => '25',
                'pilihan_c' => '30',
                'pilihan_d' => '40',
                'pilihan_e' => '50',
                'jawaban_benar' => 'C',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Jika x + 5 = 12, maka nilai dari 2x - 3 adalah...',
                'pilihan_a' => '7',
                'pilihan_b' => '9',
                'pilihan_c' => '11',
                'pilihan_d' => '13',
                'pilihan_e' => '15',
                'jawaban_benar' => 'C',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Berapakah luas segitiga dengan alas 8 cm and tinggi 5 cm?',
                'pilihan_a' => '13 cm²',
                'pilihan_b' => '20 cm²',
                'pilihan_c' => '40 cm²',
                'pilihan_d' => '26 cm²',
                'pilihan_e' => '30 cm²',
                'jawaban_benar' => 'B',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Jika sebuah dadu dilempar sekali, berapakah peluang munculnya angka prima?',
                'pilihan_a' => '1/6',
                'pilihan_b' => '1/3',
                'pilihan_c' => '1/2',
                'pilihan_d' => '2/3',
                'pilihan_e' => '5/6',
                'jawaban_benar' => 'C',
                'bobot' => 20,
            ],
            [
                'pertanyaan' => 'Nilai dari 12 - 3 * 2 + 8 adalah...',
                'pilihan_a' => '26',
                'pilihan_b' => '14',
                'pilihan_c' => '20',
                'pilihan_d' => '22',
                'pilihan_e' => '10',
                'jawaban_benar' => 'B',
                'bobot' => 20,
            ],
        ];

        foreach ($soalsMath as $soal) {
            $soal['id_kategori'] = $kategoriMath->id_kategori;
            BankSoal::create($soal);
        }

        // 3. Peserta
        $peserta1 = Peserta::create([
            'nomor_peserta' => 'P001',
            'nama_lengkap' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('peserta123'),
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2005-08-12',
            'telepon' => '081234567890',
            'alamat' => 'Jl. Merdeka No. 10, Jakarta',
            'status' => 'aktif',
        ]);

        $peserta2 = Peserta::create([
            'nomor_peserta' => 'P002',
            'nama_lengkap' => 'Siti Aminah',
            'email' => 'siti@gmail.com',
            'password' => Hash::make('peserta123'),
            'jenis_kelamin' => 'P',
            'tanggal_lahir' => '2006-03-24',
            'telepon' => '089876543210',
            'alamat' => 'Jl. Mawar No. 5, Surabaya',
            'status' => 'aktif',
        ]);

        // 4. Jadwal Ujian
        // Jadwal 1: Aktif saat ini (Teknologi Informasi)
        $jadwalIT = JadwalTes::create([
            'nama_tes' => 'Ujian Dasar Pemrograman',
            'id_kategori' => $kategoriIT->id_kategori,
            'tanggal_mulai' => now()->subHours(2)->format('Y-m-d H:i:s'),
            'tanggal_selesai' => now()->addHours(10)->format('Y-m-d H:i:s'),
            'durasi' => 15, // 15 menit
            'jumlah_soal' => 5,
            'passing_grade' => 70.00,
            'instruksi' => 'Jawablah dengan teliti dan pilih opsi terbaik. Dilarang membuka tab baru selama ujian berlangsung.',
            'status' => 'aktif',
        ]);

        // Jadwal 2: Mendatang (Bahasa Inggris)
        $jadwalEnglish = JadwalTes::create([
            'nama_tes' => 'English Proficiency Test',
            'id_kategori' => $kategoriEnglish->id_kategori,
            'tanggal_mulai' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'tanggal_selesai' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'durasi' => 60, // 60 menit
            'jumlah_soal' => 5,
            'passing_grade' => 75.00,
            'instruksi' => 'Read each text and sentence carefully. Choose the single best answer for each question.',
            'status' => 'aktif',
        ]);

        // 5. Hubungkan Peserta ke Ujian (PesertaTes)
        PesertaTes::create([
            'id_jadwal' => $jadwalIT->id_jadwal,
            'id_peserta' => $peserta1->id_peserta,
            'status_tes' => 'belum_mulai',
            'status_kelulusan' => 'belum_dinilai',
        ]);

        PesertaTes::create([
            'id_jadwal' => $jadwalIT->id_jadwal,
            'id_peserta' => $peserta2->id_peserta,
            'status_tes' => 'belum_mulai',
            'status_kelulusan' => 'belum_dinilai',
        ]);

        PesertaTes::create([
            'id_jadwal' => $jadwalEnglish->id_jadwal,
            'id_peserta' => $peserta1->id_peserta,
            'status_tes' => 'belum_mulai',
            'status_kelulusan' => 'belum_dinilai',
        ]);
    }
}
