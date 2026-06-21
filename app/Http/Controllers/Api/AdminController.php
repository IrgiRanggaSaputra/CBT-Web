<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BankSoal;
use App\Models\JadwalTes;
use App\Models\JawabanPeserta;
use App\Models\KategoriSoal;
use App\Models\Peserta;
use App\Models\PesertaTes;
use App\Models\SoalTes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user() instanceof Admin, 403, 'Admin access required.');
    }

    public function stats(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->json([
            'participants' => Peserta::count(),
            'active_participants' => Peserta::where('status', 'aktif')->count(),
            'categories' => KategoriSoal::count(),
            'questions' => BankSoal::count(),
            'schedules' => JadwalTes::count(),
            'active_schedules' => JadwalTes::where('status', 'aktif')->count(),
            'running_tests' => PesertaTes::where('status_tes', 'sedang_tes')->count(),
            'finished_tests' => PesertaTes::where('status_tes', 'selesai')->count(),
            'passed_tests' => PesertaTes::where('status_kelulusan', 'lulus')->count(),
            'failed_tests' => PesertaTes::where('status_kelulusan', 'tidak_lulus')->count(),
        ]);
    }

    public function participants(Request $request)
    {
        $this->authorizeAdmin($request);

        return Peserta::query()
            ->latest('id_peserta')
            ->limit(100)
            ->get();
    }

    public function storeParticipant(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'nomor_peserta' => ['required', 'string', 'max:20', 'unique:pesertas,nomor_peserta'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'tanggal_lahir' => ['nullable', 'date'],
            'telepon' => ['nullable', 'string', 'max:15'],
            'alamat' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        return response()->json(Peserta::create($data), 201);
    }

    public function updateParticipant(Request $request, Peserta $peserta)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'nomor_peserta' => ['required', 'string', 'max:20', Rule::unique('pesertas', 'nomor_peserta')->ignore($peserta->id_peserta, 'id_peserta')],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'tanggal_lahir' => ['nullable', 'date'],
            'telepon' => ['nullable', 'string', 'max:15'],
            'alamat' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $peserta->update($data);

        return $peserta->refresh();
    }

    public function destroyParticipant(Request $request, Peserta $peserta)
    {
        $this->authorizeAdmin($request);

        $peserta->delete();

        return response()->json(['message' => 'Peserta deleted.']);
    }

    public function categories(Request $request)
    {
        $this->authorizeAdmin($request);

        return KategoriSoal::withCount('bankSoals')
            ->latest('id_kategori')
            ->get();
    }

    public function storeCategory(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        return response()->json(KategoriSoal::create($data), 201);
    }

    public function updateCategory(Request $request, KategoriSoal $kategori)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $kategori->update($data);

        return $kategori->refresh();
    }

    public function destroyCategory(Request $request, KategoriSoal $kategori)
    {
        $this->authorizeAdmin($request);

        $kategori->delete();

        return response()->json(['message' => 'Kategori deleted.']);
    }

    public function questions(Request $request)
    {
        $this->authorizeAdmin($request);

        return BankSoal::with('kategori:id_kategori,nama_kategori')
            ->latest('id_soal')
            ->limit(150)
            ->get();
    }

    public function storeQuestion(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $this->validateQuestion($request);

        return response()->json(BankSoal::create($data)->load('kategori:id_kategori,nama_kategori'), 201);
    }

    public function updateQuestion(Request $request, BankSoal $soal)
    {
        $this->authorizeAdmin($request);

        $soal->update($this->validateQuestion($request));

        return $soal->refresh()->load('kategori:id_kategori,nama_kategori');
    }

    public function destroyQuestion(Request $request, BankSoal $soal)
    {
        $this->authorizeAdmin($request);

        $soal->delete();

        return response()->json(['message' => 'Soal deleted.']);
    }

    public function schedules(Request $request)
    {
        $this->authorizeAdmin($request);

        return JadwalTes::with('kategori:id_kategori,nama_kategori')
            ->withCount('pesertaTes')
            ->latest('id_jadwal')
            ->get();
    }

    public function storeSchedule(Request $request)
    {
        $this->authorizeAdmin($request);

        $data = $this->validateSchedule($request);

        return response()->json(JadwalTes::create($data)->load('kategori:id_kategori,nama_kategori'), 201);
    }

    public function updateSchedule(Request $request, JadwalTes $jadwal)
    {
        $this->authorizeAdmin($request);

        $jadwal->update($this->validateSchedule($request));

        return $jadwal->refresh()->load('kategori:id_kategori,nama_kategori');
    }

    public function destroySchedule(Request $request, JadwalTes $jadwal)
    {
        $this->authorizeAdmin($request);

        $jadwal->delete();

        return response()->json(['message' => 'Jadwal deleted.']);
    }

    public function reports(Request $request)
    {
        $this->authorizeAdmin($request);

        $tests = PesertaTes::with([
            'peserta:id_peserta,nomor_peserta,nama_lengkap',
            'jadwal:id_jadwal,nama_tes,passing_grade,jumlah_soal',
        ])
            ->latest('id_peserta_tes')
            ->limit(100)
            ->get();

        // Enrich with answered/total question counts
        $testIds = $tests->pluck('id_peserta_tes')->toArray();
        $answeredCounts = JawabanPeserta::whereIn('id_peserta_tes', $testIds)
            ->whereNotNull('jawaban')
            ->selectRaw('id_peserta_tes, COUNT(*) as answered_count')
            ->groupBy('id_peserta_tes')
            ->pluck('answered_count', 'id_peserta_tes');

        return $tests->map(function ($test) use ($answeredCounts) {
            $data = $test->toArray();
            $data['answered_count'] = $answeredCounts[$test->id_peserta_tes] ?? 0;
            $data['total_questions'] = $test->jadwal?->jumlah_soal ?? 0;
            return $data;
        });
    }

    /**
     * Real-time monitoring endpoint for active test sessions.
     * Returns only running tests with progress data.
     */
    public function monitoring(Request $request)
    {
        $this->authorizeAdmin($request);

        $tests = PesertaTes::with([
            'peserta:id_peserta,nomor_peserta,nama_lengkap',
            'jadwal:id_jadwal,nama_tes,durasi,jumlah_soal,passing_grade',
        ])
            ->where('status_tes', 'sedang_tes')
            ->latest('waktu_mulai')
            ->get();

        $testIds = $tests->pluck('id_peserta_tes')->toArray();
        $answeredCounts = JawabanPeserta::whereIn('id_peserta_tes', $testIds)
            ->whereNotNull('jawaban')
            ->selectRaw('id_peserta_tes, COUNT(*) as answered_count')
            ->groupBy('id_peserta_tes')
            ->pluck('answered_count', 'id_peserta_tes');

        return response()->json($tests->map(function ($test) use ($answeredCounts) {
            $remainingSeconds = (int) max(0, now()->diffInSeconds($test->waktu_selesai, false));
            return [
                'id_peserta_tes' => $test->id_peserta_tes,
                'peserta' => $test->peserta,
                'jadwal' => $test->jadwal,
                'waktu_mulai' => $test->waktu_mulai,
                'waktu_selesai' => $test->waktu_selesai,
                'remaining_seconds' => $remainingSeconds,
                'answered_count' => $answeredCounts[$test->id_peserta_tes] ?? 0,
                'total_questions' => $test->jadwal?->jumlah_soal ?? 0,
            ];
        }));
    }

    private function validateQuestion(Request $request): array
    {
        return $request->validate([
            'id_kategori' => ['nullable', 'exists:kategori_soals,id_kategori'],
            'pertanyaan' => ['required', 'string'],
            'pilihan_a' => ['required', 'string'],
            'pilihan_b' => ['required', 'string'],
            'pilihan_c' => ['required', 'string'],
            'pilihan_d' => ['required', 'string'],
            'pilihan_e' => ['nullable', 'string'],
            'jawaban_benar' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E'])],
            'bobot' => ['required', 'integer', 'min:1', 'max:100'],
            'gambar' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'nama_tes' => ['required', 'string', 'max:255'],
            'id_kategori' => ['nullable', 'exists:kategori_soals,id_kategori'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'durasi' => ['required', 'integer', 'min:1'],
            'jumlah_soal' => ['required', 'integer', 'min:1'],
            'passing_grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'instruksi' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'aktif', 'selesai'])],
        ]);
    }

    /**
     * Import students from a CSV file.
     * Expected columns: nomor_peserta,nama_lengkap,email,password,jenis_kelamin,status
     */
    public function importParticipants(Request $request)
    {
        $this->authorizeAdmin($request);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $imported = 0;
        $skipped = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');

            if ($header && count($header) == 1) {
                rewind($handle);
                $header = fgetcsv($handle, 1000, ';');
                $separator = ';';
            } else {
                $separator = ',';
            }

            // Clean BOM and lowercase
            $header = array_map(function ($h) {
                return strtolower(trim(preg_replace('/[\x00-\x1F\x7F-\x9F\xEF\xBB\xBF]/', '', $h)));
            }, $header);

            while (($row = fgetcsv($handle, 1000, $separator)) !== false) {
                if (count($row) < 2) continue;

                $data = array_combine(array_slice($header, 0, count($row)), $row);

                $nomorPeserta = trim($data['nomor_peserta'] ?? '');
                $namaLengkap = trim($data['nama_lengkap'] ?? '');
                $password = trim($data['password'] ?? '');

                if (empty($nomorPeserta) || empty($namaLengkap) || empty($password)) {
                    $skipped++;
                    continue;
                }

                // Skip if nomor_peserta already exists
                if (Peserta::where('nomor_peserta', $nomorPeserta)->exists()) {
                    $skipped++;
                    continue;
                }

                $jenisKelamin = strtoupper(trim($data['jenis_kelamin'] ?? ''));
                if (!in_array($jenisKelamin, ['L', 'P'])) {
                    $jenisKelamin = null;
                }

                $status = strtolower(trim($data['status'] ?? 'aktif'));
                if (!in_array($status, ['aktif', 'nonaktif'])) {
                    $status = 'aktif';
                }

                Peserta::create([
                    'nomor_peserta' => $nomorPeserta,
                    'nama_lengkap' => $namaLengkap,
                    'email' => trim($data['email'] ?? '') ?: null,
                    'password' => $password, // Will be hashed by mutator
                    'jenis_kelamin' => $jenisKelamin,
                    'tanggal_lahir' => !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : null,
                    'telepon' => trim($data['telepon'] ?? '') ?: null,
                    'alamat' => trim($data['alamat'] ?? '') ?: null,
                    'status' => $status,
                ]);

                $imported++;
            }
            fclose($handle);
        }

        return response()->json([
            'message' => "Berhasil mengimpor $imported peserta. $skipped data dilewati.",
            'imported' => $imported,
            'skipped' => $skipped,
        ]);
    }

    public function importQuestions(Request $request)
    {
        $this->authorizeAdmin($request);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $questionsImported = 0;

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            
            if ($header && count($header) == 1) {
                rewind($handle);
                $header = fgetcsv($handle, 1000, ';');
                $separator = ';';
            } else {
                $separator = ',';
            }

            // Clean BOM and lowercase
            $header = array_map(function ($h) {
                return strtolower(trim(preg_replace('/[\x00-\x1F\x7F-\x9F\xEF\xBB\xBF]/', '', $h)));
            }, $header);

            while (($row = fgetcsv($handle, 1000, $separator)) !== false) {
                if (count($row) < 7) continue;

                $data = array_combine(array_slice($header, 0, count($row)), $row);
                
                $id_kategori = isset($data['id_kategori']) && is_numeric($data['id_kategori']) ? (int)$data['id_kategori'] : null;
                $pertanyaan = $data['pertanyaan'] ?? '';
                $pilihan_a = $data['pilihan_a'] ?? '';
                $pilihan_b = $data['pilihan_b'] ?? '';
                $pilihan_c = $data['pilihan_c'] ?? '';
                $pilihan_d = $data['pilihan_d'] ?? '';
                $pilihan_e = $data['pilihan_e'] ?? null;
                $jawaban_benar = strtoupper(trim($data['jawaban_benar'] ?? ''));
                $bobot = isset($data['bobot']) ? (int)$data['bobot'] : 10;

                if (empty($pertanyaan) || empty($pilihan_a) || empty($pilihan_b) || empty($jawaban_benar)) {
                    continue;
                }

                if (!in_array($jawaban_benar, ['A', 'B', 'C', 'D', 'E'])) {
                    $jawaban_benar = 'A';
                }

                BankSoal::create([
                    'id_kategori' => $id_kategori,
                    'pertanyaan' => $pertanyaan,
                    'pilihan_a' => $pilihan_a,
                    'pilihan_b' => $pilihan_b,
                    'pilihan_c' => $pilihan_c,
                    'pilihan_d' => $pilihan_d,
                    'pilihan_e' => $pilihan_e,
                    'jawaban_benar' => $jawaban_benar,
                    'bobot' => $bobot,
                ]);

                $questionsImported++;
            }
            fclose($handle);
        }

        return response()->json([
            'message' => "Berhasil mengimpor $questionsImported soal.",
        ]);
    }

    public function scheduleParticipants(Request $request, JadwalTes $jadwal)
    {
        $this->authorizeAdmin($request);

        $assignedParticipants = PesertaTes::where('id_jadwal', $jadwal->id_jadwal)
            ->with('peserta:id_peserta,nomor_peserta,nama_lengkap,email,status')
            ->get()
            ->map(function ($pt) {
                return [
                    'id_peserta_tes' => $pt->id_peserta_tes,
                    'id_peserta' => $pt->id_peserta,
                    'nomor_peserta' => $pt->peserta?->nomor_peserta,
                    'nama_lengkap' => $pt->peserta?->nama_lengkap,
                    'email' => $pt->peserta?->email,
                    'status_tes' => $pt->status_tes,
                ];
            });

        $assignedIds = $assignedParticipants->pluck('id_peserta')->toArray();
        $availableParticipants = Peserta::where('status', 'aktif')
            ->whereNotIn('id_peserta', $assignedIds)
            ->select('id_peserta', 'nomor_peserta', 'nama_lengkap', 'email')
            ->get();

        return response()->json([
            'assigned' => $assignedParticipants,
            'available' => $availableParticipants,
        ]);
    }

    public function storeScheduleParticipant(Request $request, JadwalTes $jadwal)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'id_peserta' => 'required|exists:pesertas,id_peserta',
        ]);

        $exists = PesertaTes::where('id_jadwal', $jadwal->id_jadwal)
            ->where('id_peserta', $validated['id_peserta'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Peserta sudah terdaftar pada jadwal ujian ini.'], 422);
        }

        $pesertaTes = PesertaTes::create([
            'id_jadwal' => $jadwal->id_jadwal,
            'id_peserta' => $validated['id_peserta'],
            'status_tes' => 'belum_mulai',
            'status_kelulusan' => 'belum_dinilai',
        ]);

        return response()->json([
            'message' => 'Peserta berhasil didaftarkan.',
            'peserta_tes' => $pesertaTes,
        ], 201);
    }

    public function storeAllScheduleParticipants(Request $request, JadwalTes $jadwal)
    {
        $this->authorizeAdmin($request);

        $assignedIds = PesertaTes::where('id_jadwal', $jadwal->id_jadwal)
            ->pluck('id_peserta')
            ->toArray();

        $availableParticipants = Peserta::where('status', 'aktif')
            ->whereNotIn('id_peserta', $assignedIds)
            ->get();

        if ($availableParticipants->isEmpty()) {
            return response()->json(['message' => 'Tidak ada peserta baru yang dapat didaftarkan.'], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($availableParticipants, $jadwal) {
            foreach ($availableParticipants as $peserta) {
                PesertaTes::create([
                    'id_jadwal' => $jadwal->id_jadwal,
                    'id_peserta' => $peserta->id_peserta,
                    'status_tes' => 'belum_mulai',
                    'status_kelulusan' => 'belum_dinilai',
                ]);
            }
        });

        return response()->json([
            'message' => 'Semua peserta berhasil didaftarkan.',
            'count' => $availableParticipants->count(),
        ], 201);
    }

    public function destroyScheduleParticipant(Request $request, JadwalTes $jadwal, Peserta $peserta)
    {
        $this->authorizeAdmin($request);

        $pesertaTes = PesertaTes::where('id_jadwal', $jadwal->id_jadwal)
            ->where('id_peserta', $peserta->id_peserta)
            ->first();

        if (!$pesertaTes) {
            return response()->json(['message' => 'Peserta tidak ditemukan pada jadwal ini.'], 404);
        }

        if ($pesertaTes->status_tes !== 'belum_mulai') {
            return response()->json(['message' => 'Tidak dapat menghapus peserta yang sudah atau sedang mengerjakan ujian.'], 422);
        }

        $pesertaTes->delete();

        return response()->json(['message' => 'Peserta berhasil dihapus dari jadwal ujian ini.']);
    }

    public function exportParticipantCsv(Request $request, PesertaTes $test)
    {
        $this->authorizeAdmin($request);

        $test->load(['peserta', 'jadwal', 'jawabanPesertas.soal']);

        $filename = "rapor_cbt_" . strtolower(str_replace(' ', '_', $test->peserta?->nama_lengkap)) . "_" . $test->id_peserta_tes . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Field', 'Detail / Jawaban'];

        $callback = function () use ($test, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            fputcsv($file, ['Nomor Peserta', $test->peserta?->nomor_peserta]);
            fputcsv($file, ['Nama Lengkap', $test->peserta?->nama_lengkap]);
            fputcsv($file, ['Email', $test->peserta?->email]);
            fputcsv($file, ['Ujian', $test->jadwal?->nama_tes]);
            fputcsv($file, ['Durasi', $test->jadwal?->durasi . ' Menit']);
            fputcsv($file, ['Passing Grade', $test->jadwal?->passing_grade]);
            fputcsv($file, ['Nilai Ujian', $test->nilai ?? '-']);
            fputcsv($file, ['Status Kelulusan', strtoupper($test->status_kelulusan)]);
            fputcsv($file, ['Status Ujian', strtoupper($test->status_tes)]);
            fputcsv($file, []);
            fputcsv($file, ['DETAIL JAWABAN SOAL']);
            fputcsv($file, ['Nomor Soal', 'Pertanyaan', 'Pilihan Jawaban', 'Jawaban Peserta', 'Kunci Jawaban', 'Hasil']);

            $no = 1;
            foreach ($test->jawabanPesertas as $jawaban) {
                fputcsv($file, [
                    $no++,
                    strip_tags($jawaban->soal?->pertanyaan),
                    "A: {$jawaban->soal?->pilihan_a} | B: {$jawaban->soal?->pilihan_b} | C: {$jawaban->soal?->pilihan_c} | D: {$jawaban->soal?->pilihan_d}",
                    $jawaban->jawaban ?? '-',
                    $jawaban->soal?->jawaban_benar,
                    $jawaban->is_correct ? 'BENAR' : 'SALAH'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
