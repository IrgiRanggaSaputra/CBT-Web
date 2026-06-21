<?php

namespace App\Http\Controllers;

use App\Models\PesertaTes;
use Illuminate\Http\Request;

class ReportPrintController extends Controller
{
    /**
     * Print the exam report card for a student.
     */
    public function printReport(PesertaTes $test)
    {
        $test->load(['peserta', 'jadwal.kategori', 'jawabanPesertas.soal']);
        
        return view('print-report', [
            'test' => $test,
        ]);
    }
}
