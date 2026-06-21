<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAPOR HASIL UJIAN - {{ $test->peserta?->nama_lengkap }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            color: #1e293b;
            background: #fff;
            padding: 40px;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #1e293b;
            padding-bottom: 16px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
        }
        .meta-grid {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 30px;
        }
        .meta-col {
            flex: 1;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 6px 0;
            vertical-align: top;
            font-size: 13px;
        }
        .meta-table td.label {
            font-weight: 600;
            width: 140px;
            color: #475569;
        }
        .meta-table td.colon {
            width: 15px;
            color: #64748b;
            text-align: center;
        }
        .meta-table td.value {
            color: #0f172a;
            font-weight: 500;
        }
        .score-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-around;
            background: #f8fafc;
            margin-bottom: 35px;
        }
        .score-item {
            text-align: center;
        }
        .score-item span {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
        }
        .score-item strong {
            display: block;
            font-size: 28px;
            font-weight: 800;
            margin-top: 4px;
            color: #0f172a;
        }
        .score-item .status {
            font-size: 14px;
            font-weight: 700;
            padding: 6px 18px;
            border-radius: 20px;
            margin-top: 6px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status.lulus {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .status.tidak_lulus {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 16px;
            text-transform: uppercase;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .details-table th, .details-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
            font-size: 12px;
        }
        .details-table th {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
        }
        .details-table td.center {
            text-align: center;
        }
        .result-badge {
            font-weight: 700;
        }
        .result-badge.benar {
            color: #15803d;
        }
        .result-badge.salah {
            color: #b91c1c;
        }
        .signature-area {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        .signature {
            text-align: center;
            width: 220px;
            font-size: 13px;
        }
        .signature p {
            margin: 0;
        }
        .signature .date {
            margin-bottom: 70px;
            color: #475569;
        }
        .signature .name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }
        .no-print-bar {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .print-btn {
            background: #6366f1;
            color: #fff;
            border: 0;
            padding: 8px 16px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .print-btn:hover {
            background: #4f46e5;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Helper bar for printing -->
    <div class="no-print-bar">
        <span>Silakan simpan halaman ini sebagai PDF atau cetak secara fisik.</span>
        <button class="print-btn" onclick="window.print()">Cetak Halaman</button>
    </div>

    <!-- Rapor Header -->
    <div class="header">
        <h1>Laporan Hasil Ujian (Rapor)</h1>
        <p>Sistem Computer Based Test (CBT) Web</p>
    </div>

    <!-- Meta Information -->
    <div class="meta-grid">
        <div class="meta-col">
            <table class="meta-table">
                <tr>
                    <td class="label">Nama Lengkap</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $test->peserta?->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Peserta</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $test->peserta?->nomor_peserta }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $test->peserta?->email ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="meta-col">
            <table class="meta-table">
                <tr>
                    <td class="label">Ujian</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $test->jadwal?->nama_tes }}</td>
                </tr>
                <tr>
                    <td class="label">Kategori Soal</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $test->jadwal?->kategori?->nama_kategori ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Mulai</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $test->waktu_mulai ? $test->waktu_mulai->format('d M Y, H:i') : '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Score Card Overview -->
    <div class="score-card">
        <div class="score-item">
            <span>Jawaban Benar</span>
            <strong>{{ $test->jawabanPesertas->where('is_correct', true)->count() }}</strong>
        </div>
        <div class="score-item">
            <span>Jawaban Salah</span>
            <strong>{{ $test->jawabanPesertas->where('is_correct', false)->whereNotNull('jawaban')->count() }}</strong>
        </div>
        <div class="score-item">
            <span>Tidak Dijawab</span>
            <strong>{{ $test->jawabanPesertas->whereNull('jawaban')->count() }}</strong>
        </div>
        <div class="score-item">
            <span>Nilai Akhir</span>
            <strong>{{ $test->nilai ?? '0.00' }}</strong>
        </div>
        <div class="score-item">
            <span>Status Kelulusan</span>
            <div>
                <span class="status {{ $test->status_kelulusan === 'lulus' ? 'lulus' : 'tidak_lulus' }}">
                    {{ $test->status_kelulusan === 'lulus' ? 'Lulus' : 'Tidak Lulus' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Section: Rincian Pengerjaan -->
    <div class="section-title">Rincian Jawaban Soal</div>
    <table class="details-table">
        <thead>
            <tr>
                <th style="width: 50px;" class="center">No</th>
                <th>Pertanyaan</th>
                <th style="width: 200px;">Pilihan Jawaban</th>
                <th style="width: 80px;" class="center">Jawaban Anda</th>
                <th style="width: 80px;" class="center">Kunci Jawaban</th>
                <th style="width: 80px;" class="center">Hasil</th>
            </tr>
        </thead>
        <tbody>
            @forelse($test->jawabanPesertas as $index => $jawaban)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{!! $jawaban->soal?->pertanyaan !!}</td>
                    <td>
                        A: {{ $jawaban->soal?->pilihan_a }}<br>
                        B: {{ $jawaban->soal?->pilihan_b }}<br>
                        C: {{ $jawaban->soal?->pilihan_c }}<br>
                        D: {{ $jawaban->soal?->pilihan_d }}<br>
                        @if($jawaban->soal?->pilihan_e)
                            E: {{ $jawaban->soal?->pilihan_e }}
                        @endif
                    </td>
                    <td class="center"><strong>{{ $jawaban->jawaban ?? '-' }}</strong></td>
                    <td class="center"><strong>{{ $jawaban->soal?->jawaban_benar }}</strong></td>
                    <td class="center">
                        <span class="result-badge {{ $jawaban->is_correct ? 'benar' : 'salah' }}">
                            {{ $jawaban->is_correct ? 'BENAR' : 'SALAH' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Tidak ada data jawaban.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Area -->
    <div class="signature-area">
        <div class="signature">
            <p class="date">Jakarta, {{ now()->format('d F Y') }}</p>
            <p>Pengawas Ujian,</p>
            <div style="height: 60px;"></div>
            <p class="name">Administrator CBT</p>
            <p style="font-size: 11px; color: #64748b;">Sistem CBT Web</p>
        </div>
    </div>

    <!-- Automatically trigger window.print -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Wait 1 second before prompting to print to allow styles to settle
            setTimeout(() => {
                window.print();
            }, 1000);
        });
    </script>
</body>
</html>
