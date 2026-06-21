<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use App\Models\PesertaTes;
use App\Models\BankSoal;
use App\Models\SoalTes;
use App\Models\JawabanPeserta;
use App\Services\FisherYatesShuffle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesertaController extends Controller
{
    private function authorizePeserta(Request $request): Peserta
    {
        $user = $request->user();

        abort_unless($user instanceof Peserta, 403, 'Peserta access required.');

        return $user;
    }

    public function dashboard(Request $request)
    {
        $peserta = $this->authorizePeserta($request);

        $tests = $this->queryFor($peserta);

        return response()->json([
            'profile' => [
                'nomor_peserta' => $peserta->nomor_peserta,
                'nama_lengkap' => $peserta->nama_lengkap,
                'email' => $peserta->email,
                'status' => $peserta->status,
            ],
            'stats' => [
                'total_tests' => (clone $tests)->count(),
                'available_tests' => (clone $tests)
                    ->whereIn('status_tes', ['belum_mulai', 'sedang_tes'])
                    ->count(),
                'running_tests' => (clone $tests)->where('status_tes', 'sedang_tes')->count(),
                'completed_tests' => (clone $tests)->where('status_tes', 'selesai')->count(),
            ],
            'upcoming_tests' => $this->serializeTests(
                (clone $tests)
                    ->whereIn('status_tes', ['belum_mulai', 'sedang_tes'])
                    ->latest('id_peserta_tes')
                    ->limit(5)
                    ->get()
            ),
        ]);
    }

    public function myTests(Request $request)
    {
        $peserta = $this->authorizePeserta($request);

        $tests = $this->queryFor($peserta)
            ->whereIn('status_tes', ['belum_mulai', 'sedang_tes'])
            ->latest('id_peserta_tes')
            ->get();

        return response()->json($this->serializeTests($tests));
    }

    public function history(Request $request)
    {
        $peserta = $this->authorizePeserta($request);

        $tests = $this->queryFor($peserta)
            ->where('status_tes', 'selesai')
            ->latest('id_peserta_tes')
            ->get();

        return response()->json($this->serializeTests($tests));
    }

    /**
     * Start or resume a test session.
     *
     * On first start (belum_mulai → sedang_tes):
     * 1. Populates soal_tes if the schedule has no questions assigned yet
     * 2. Generates a unique shuffle_seed for this student using random_int()
     * 3. Uses Fisher-Yates algorithm to create per-student shuffled option maps
     * 4. Stores shuffled_options in jawaban_pesertas for each question
     * 5. Sets timer (waktu_mulai/waktu_selesai)
     */
    public function startTest(Request $request, PesertaTes $test)
    {
        $peserta = $this->authorizePeserta($request);
        abort_unless($test->id_peserta === $peserta->id_peserta, 403, 'Anda tidak berwenang mengikuti tes ini.');

        if ($test->status_tes === 'selesai') {
            return response()->json(['message' => 'Tes ini sudah selesai dikerjakan.'], 422);
        }

        $jadwal = $test->jadwal;
        if (!$jadwal || $jadwal->status !== 'aktif') {
            return response()->json(['message' => 'Jadwal tes tidak aktif.'], 422);
        }

        $now = now();
        if ($now->lt($jadwal->tanggal_mulai) || $now->gt($jadwal->tanggal_selesai)) {
            return response()->json(['message' => 'Waktu tes berada di luar periode yang dijadwalkan.'], 422);
        }

        if ($test->status_tes === 'belum_mulai') {
            DB::transaction(function () use ($test, $jadwal, $now) {
                // Step 1: Populate soal_tes if not populated for this schedule
                $exists = SoalTes::where('id_jadwal', $jadwal->id_jadwal)->exists();
                if (!$exists) {
                    $query = BankSoal::query();
                    if ($jadwal->id_kategori) {
                        $query->where('id_kategori', $jadwal->id_kategori);
                    }
                    $questions = $query->inRandomOrder()->limit($jadwal->jumlah_soal)->get();
                    
                    $order = 1;
                    foreach ($questions as $q) {
                        SoalTes::create([
                            'id_jadwal' => $jadwal->id_jadwal,
                            'id_soal' => $q->id_soal,
                            'nomor_urut' => $order++,
                        ]);
                    }
                }

                // Step 2: Generate a unique Fisher-Yates shuffle seed for this student
                $shuffleSeed = random_int(1, 2147483647);

                // Step 3: Get all question IDs in their base order
                $soals = SoalTes::where('id_jadwal', $jadwal->id_jadwal)
                    ->orderBy('nomor_urut')
                    ->get();

                // Step 4: For each question, generate a shuffled option map using Fisher-Yates
                foreach ($soals as $index => $soal) {
                    $question = $soal->soal;
                    if (!$question) {
                        continue;
                    }

                    // Determine available option keys (A-E, excluding null options)
                    $availableKeys = ['A', 'B', 'C', 'D'];
                    if (!empty($question->pilihan_e)) {
                        $availableKeys[] = 'E';
                    }

                    // Generate per-question option seed derived from student's base seed
                    $optionSeed = FisherYatesShuffle::questionOptionSeed($shuffleSeed, $index);

                    // Apply Fisher-Yates shuffle to option keys
                    $shuffledOptionMap = FisherYatesShuffle::shuffleOptions($availableKeys, $optionSeed);

                    // Create jawaban_pesertas record with shuffled option map
                    JawabanPeserta::firstOrCreate([
                        'id_peserta_tes' => $test->id_peserta_tes,
                        'id_soal' => $soal->id_soal,
                    ], [
                        'shuffled_options' => $shuffledOptionMap,
                        'jawaban' => null,
                        'jawaban_asli' => null,
                        'is_correct' => false,
                    ]);
                }

                // Step 5: Set timer and update status
                $waktuMulai = $now;
                $waktuSelesai = $now->copy()->addMinutes($jadwal->durasi);
                if ($waktuSelesai->gt($jadwal->tanggal_selesai)) {
                    $waktuSelesai = $jadwal->tanggal_selesai;
                }

                $test->update([
                    'shuffle_seed' => $shuffleSeed,
                    'status_tes' => 'sedang_tes',
                    'waktu_mulai' => $waktuMulai,
                    'waktu_selesai' => $waktuSelesai,
                ]);
            });
        }

        return response()->json([
            'message' => 'Tes berhasil dimulai.',
            'test' => [
                'id_peserta_tes' => $test->id_peserta_tes,
                'status_tes' => $test->status_tes,
                'waktu_mulai' => $test->waktu_mulai,
                'waktu_selesai' => $test->waktu_selesai,
            ],
        ]);
    }

    /**
     * Get questions for an active test session.
     *
     * Questions are returned in the student's unique shuffled order (Fisher-Yates).
     * For each question, the options are also shuffled according to the stored
     * shuffled_options map, so the student sees a completely unique arrangement.
     *
     * The shuffled_options map works as follows:
     *   map = ['A' => 'C', 'B' => 'A', 'C' => 'E', 'D' => 'B', 'E' => 'D']
     *   Visual "A" shows the text of original option C
     *   Visual "B" shows the text of original option A
     *   etc.
     */
    public function getQuestions(Request $request, PesertaTes $test)
    {
        $peserta = $this->authorizePeserta($request);
        abort_unless($test->id_peserta === $peserta->id_peserta, 403, 'Anda tidak berwenang.');

        if ($test->status_tes !== 'sedang_tes') {
            return response()->json(['message' => 'Status tes tidak aktif.'], 422);
        }

        if (now()->gt($test->waktu_selesai)) {
            $this->performFinish($test);
            return response()->json(['message' => 'Waktu pengerjaan tes telah habis.', 'status_tes' => 'selesai'], 422);
        }

        // Load all questions for this schedule with their base data
        $soals = SoalTes::with(['soal' => function ($query) {
            $query->select('id_soal', 'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'pilihan_e', 'gambar');
        }])
        ->where('id_jadwal', $test->id_jadwal)
        ->orderBy('nomor_urut')
        ->get();

        // Load all student answers (including shuffled_options maps)
        $answers = JawabanPeserta::where('id_peserta_tes', $test->id_peserta_tes)
            ->get()
            ->keyBy('id_soal');

        // Determine shuffled question order using Fisher-Yates with the student's seed
        $questionIds = $soals->pluck('id_soal')->toArray();
        $shuffledIds = $test->shuffle_seed
            ? FisherYatesShuffle::shuffleQuestions($questionIds, $test->shuffle_seed)
            : $questionIds;

        // Build a lookup map for soals by id_soal
        $soalMap = $soals->keyBy('id_soal');

        // Format questions in shuffled order with shuffled options
        $formattedQuestions = [];
        foreach ($shuffledIds as $displayIndex => $idSoal) {
            $soalTes = $soalMap->get($idSoal);
            if (!$soalTes || !$soalTes->soal) {
                continue;
            }

            $question = $soalTes->soal;
            $answer = $answers->get($idSoal);
            $optionMap = $answer?->shuffled_options;

            // Build the original options lookup
            $originalOptions = [
                'A' => $question->pilihan_a,
                'B' => $question->pilihan_b,
                'C' => $question->pilihan_c,
                'D' => $question->pilihan_d,
                'E' => $question->pilihan_e,
            ];

            // Apply the shuffled option map to reorder displayed options
            if ($optionMap && is_array($optionMap)) {
                // optionMap: visual label => original key
                // We need to output: pilihan_a = text of originalOptions[optionMap['A']]
                $shuffledDisplay = [];
                foreach ($optionMap as $visualLabel => $originalKey) {
                    $shuffledDisplay['pilihan_' . strtolower($visualLabel)] = $originalOptions[$originalKey] ?? null;
                }

                // Determine the student's visual answer
                // jawaban stores the visual key, so we can use it directly
                $visualAnswer = $answer?->jawaban;
            } else {
                // No shuffle map — fallback to original order
                $shuffledDisplay = [
                    'pilihan_a' => $question->pilihan_a,
                    'pilihan_b' => $question->pilihan_b,
                    'pilihan_c' => $question->pilihan_c,
                    'pilihan_d' => $question->pilihan_d,
                    'pilihan_e' => $question->pilihan_e,
                ];
                $visualAnswer = $answer?->jawaban;
            }

            $formattedQuestions[] = [
                'id_soal' => $idSoal,
                'pertanyaan' => $question->pertanyaan,
                'pilihan_a' => $shuffledDisplay['pilihan_a'] ?? null,
                'pilihan_b' => $shuffledDisplay['pilihan_b'] ?? null,
                'pilihan_c' => $shuffledDisplay['pilihan_c'] ?? null,
                'pilihan_d' => $shuffledDisplay['pilihan_d'] ?? null,
                'pilihan_e' => $shuffledDisplay['pilihan_e'] ?? null,
                'gambar' => $question->gambar,
                'nomor_urut' => $displayIndex + 1,
                'jawaban_anda' => $visualAnswer,
            ];
        }

        $remainingSeconds = (int) max(0, now()->diffInSeconds($test->waktu_selesai, false));

        return response()->json([
            'questions' => $formattedQuestions,
            'remaining_seconds' => $remainingSeconds,
            'waktu_selesai' => $test->waktu_selesai,
        ]);
    }

    /**
     * Submit an answer for a specific question during an active test.
     *
     * The student's answer is in "visual" space (the shuffled label they see).
     * We map it back to the "original" key using the shuffled_options map,
     * then compare against the question's jawaban_benar for correctness.
     *
     * Stored fields:
     *   jawaban       = the visual key the student selected (e.g., 'B')
     *   jawaban_asli  = the original key after mapping (e.g., 'D')
     *   is_correct    = jawaban_asli === jawaban_benar
     */
    public function submitAnswer(Request $request, PesertaTes $test)
    {
        $peserta = $this->authorizePeserta($request);
        abort_unless($test->id_peserta === $peserta->id_peserta, 403, 'Anda tidak berwenang.');

        if ($test->status_tes !== 'sedang_tes') {
            return response()->json(['message' => 'Status tes tidak aktif.'], 422);
        }

        if (now()->gt($test->waktu_selesai)) {
            $this->performFinish($test);
            return response()->json(['message' => 'Waktu pengerjaan tes telah habis.', 'status_tes' => 'selesai'], 422);
        }

        $validated = $request->validate([
            'id_soal' => 'required|exists:bank_soals,id_soal',
            'jawaban' => 'nullable|in:A,B,C,D,E',
        ]);

        $belongsToTest = SoalTes::where('id_jadwal', $test->id_jadwal)
            ->where('id_soal', $validated['id_soal'])
            ->exists();

        if (!$belongsToTest) {
            return response()->json(['message' => 'Soal tidak termasuk dalam jadwal tes ini.'], 422);
        }

        // Get the stored shuffled option map for this question
        $existingAnswer = JawabanPeserta::where('id_peserta_tes', $test->id_peserta_tes)
            ->where('id_soal', $validated['id_soal'])
            ->first();

        $optionMap = $existingAnswer?->shuffled_options;

        // Map the visual answer back to the original key
        $visualAnswer = $validated['jawaban'];
        $originalAnswer = null;

        if ($visualAnswer && $optionMap && is_array($optionMap)) {
            $originalAnswer = FisherYatesShuffle::mapAnswerToOriginal($visualAnswer, $optionMap);
        } elseif ($visualAnswer) {
            // No shuffle map — visual answer IS the original answer
            $originalAnswer = $visualAnswer;
        }

        // Check correctness against the original correct answer
        $soal = BankSoal::find($validated['id_soal']);
        $isCorrect = ($originalAnswer === $soal->jawaban_benar);

        JawabanPeserta::updateOrCreate([
            'id_peserta_tes' => $test->id_peserta_tes,
            'id_soal' => $validated['id_soal'],
        ], [
            'jawaban' => $visualAnswer,
            'jawaban_asli' => $originalAnswer,
            'is_correct' => $isCorrect,
            'waktu_jawab' => now(),
        ]);

        return response()->json(['message' => 'Jawaban disimpan.']);
    }

    public function finishTest(Request $request, PesertaTes $test)
    {
        $peserta = $this->authorizePeserta($request);
        abort_unless($test->id_peserta === $peserta->id_peserta, 403, 'Anda tidak berwenang.');

        if ($test->status_tes !== 'sedang_tes') {
            return response()->json(['message' => 'Tes sudah selesai atau tidak aktif.'], 422);
        }

        $this->performFinish($test);

        return response()->json([
            'message' => 'Tes berhasil diselesaikan.',
            'nilai' => $test->nilai,
            'status_kelulusan' => $test->status_kelulusan,
        ]);
    }

    /**
     * Finalize a test session and calculate the score.
     *
     * Uses jawaban_asli (the original mapped key) for correctness comparison,
     * NOT jawaban (the visual key). This ensures accurate scoring regardless
     * of how options were shuffled for this student.
     */
    private function performFinish(PesertaTes $test)
    {
        $soals = SoalTes::with('soal')->where('id_jadwal', $test->id_jadwal)->get();
        $totalBobot = $soals->sum(function ($soal) {
            return $soal->soal?->bobot ?? 1;
        });

        $answers = JawabanPeserta::where('id_peserta_tes', $test->id_peserta_tes)->get();

        $correctBobot = 0;
        foreach ($answers as $ans) {
            if ($ans->is_correct) {
                $soal = $soals->firstWhere('id_soal', $ans->id_soal);
                $correctBobot += ($soal->soal?->bobot ?? 1);
            }
        }

        $nilai = $totalBobot > 0 ? round(($correctBobot / $totalBobot) * 100, 2) : 0;

        $jadwal = $test->jadwal;
        $passingGrade = $jadwal ? $jadwal->passing_grade : 70;
        $statusKelulusan = $nilai >= $passingGrade ? 'lulus' : 'tidak_lulus';

        $test->update([
            'status_tes' => 'selesai',
            'waktu_selesai' => now(),
            'nilai' => $nilai,
            'status_kelulusan' => $statusKelulusan,
        ]);
    }

    private function queryFor(Peserta $peserta)
    {
        return PesertaTes::with([
            'jadwal:id_jadwal,nama_tes,id_kategori,tanggal_mulai,tanggal_selesai,durasi,jumlah_soal,instruksi,status',
            'jadwal.kategori:id_kategori,nama_kategori',
        ])->where('id_peserta', $peserta->id_peserta);
    }

    private function serializeTests($tests): array
    {
        return $tests->map(function (PesertaTes $test): array {
            return [
                'id_peserta_tes' => $test->id_peserta_tes,
                'status_tes' => $test->status_tes,
                'waktu_mulai' => $test->waktu_mulai,
                'waktu_selesai' => $test->waktu_selesai,
                'jadwal' => [
                    'id_jadwal' => $test->jadwal?->id_jadwal,
                    'nama_tes' => $test->jadwal?->nama_tes,
                    'kategori' => $test->jadwal?->kategori?->nama_kategori,
                    'tanggal_mulai' => $test->jadwal?->tanggal_mulai,
                    'tanggal_selesai' => $test->jadwal?->tanggal_selesai,
                    'durasi' => $test->jadwal?->durasi,
                    'jumlah_soal' => $test->jadwal?->jumlah_soal,
                    'instruksi' => $test->jadwal?->instruksi,
                    'status' => $test->jadwal?->status,
                ],
            ];
        })->all();
    }
}
