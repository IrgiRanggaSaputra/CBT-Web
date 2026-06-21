<?php

namespace App\Http\Middleware;

use App\Models\PesertaTes;
use App\Models\SoalTes;
use App\Models\JawabanPeserta;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to validate test session integrity for peserta exam endpoints.
 *
 * Ensures:
 * - The authenticated user is a Peserta and owns the test session
 * - The test schedule is still active
 * - The test hasn't expired (auto-finishes if time is up)
 * - Prevents accessing completed test endpoints
 */
class EnsureTestSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user instanceof \App\Models\Peserta) {
            return response()->json(['message' => 'Akses khusus peserta.'], 403);
        }

        $test = $request->route('test');
        if ($test instanceof PesertaTes) {
            if ($test->id_peserta !== $user->id_peserta) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke sesi ujian ini.'], 403);
            }

            $jadwal = $test->jadwal;
            if (!$jadwal || $jadwal->status !== 'aktif') {
                return response()->json(['message' => 'Jadwal ujian tidak aktif.'], 422);
            }

            // Auto-finish if time is up and they are trying to access
            if ($test->status_tes === 'sedang_tes' && now()->gt($test->waktu_selesai)) {
                $this->performFinish($test);
                return response()->json(['message' => 'Waktu pengerjaan tes telah habis.', 'status_tes' => 'selesai'], 422);
            }

            // If the test has already finished, we shouldn't allow any operations on it
            if ($test->status_tes === 'selesai') {
                return response()->json(['message' => 'Ujian ini sudah selesai.'], 422);
            }
        }

        // Add security headers to every response
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    /**
     * Finalize a test session and calculate the score.
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
}
