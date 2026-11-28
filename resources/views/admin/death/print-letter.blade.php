<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Kematian - {{ $death->deceased_name }} - {{ $death->registration_no }}</title>
    
    <!-- Google Fonts for Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #f1f5f9;
        }

        .font-sans-ui {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            box-sizing: border-box;
            background-color: #ffffff;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 2rem;
            padding: 14mm 18mm 14mm 18mm;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 1px solid #cbd5e1;
            position: relative;
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
        }

        .page-break {
            page-break-before: always;
            break-before: page;
        }

        @media print {
            .no-print { 
                display: none !important; 
            }
            body { 
                background: white !important; 
                padding: 0 !important; 
                margin: 0 !important;
                font-family: 'Times New Roman', Times, serif !important;
                color: black !important; 
            }
            .print-page {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                box-sizing: border-box !important;
                font-family: 'Times New Roman', Times, serif !important;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }
        }

        /* Borderless clean table layout matching the official template */
        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .data-table tr, tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .data-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 10pt;
            font-family: 'Times New Roman', Times, serif;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body class="p-4 md:p-8 antialiased">

    <!-- Action Toolbar (No Print) -->
    <div class="max-w-[210mm] mx-auto mb-4 no-print relative z-30 pointer-events-auto flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl shadow-xs border border-slate-200">
        <div class="flex items-center gap-2">
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('admin.death.show', $death) }}" class="font-sans-ui text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Detail
                </a>
            @else
                <a href="{{ url()->previous() ?: route('submissions.index') }}" class="font-sans-ui text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            @endif
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-slate-500 font-semibold font-sans-ui mr-1 hidden sm:inline">Unduh Berkas:</span>
            <a href="{{ auth()->check() && auth()->user()->isAdmin() ? route('admin.death.export', [$death, 'pdf']) : route('death.export', [$death, 'pdf']) }}" class="font-sans-ui bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs px-3 py-2 rounded-lg shadow-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-file-pdf"></i> PDF (.pdf)
            </a>
            <a href="{{ auth()->check() && auth()->user()->isAdmin() ? route('admin.death.export', [$death, 'docx']) : route('death.export', [$death, 'docx']) }}" class="font-sans-ui bg-blue-700 hover:bg-blue-800 text-white font-bold text-xs px-3 py-2 rounded-lg shadow-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-file-word"></i> Word (.docx)
            </a>
            <a href="{{ auth()->check() && auth()->user()->isAdmin() ? route('admin.death.export', [$death, 'xlsx']) : route('death.export', [$death, 'xlsx']) }}" class="font-sans-ui bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs px-3 py-2 rounded-lg shadow-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-file-excel"></i> Excel (.xlsx)
            </a>
            <button type="button" id="btn-print-a4" onclick="window.focus(); window.print();" class="font-sans-ui relative z-10 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs px-3.5 py-2 rounded-lg shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-print"></i> Cetak (A4)
            </button>
        </div>
    </div>

    @php
        $romans = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $monthNum = (int)($death->death_date ? $death->death_date->format('n') : date('n'));
        $monthRoman = $romans[$monthNum] ?? 'IX';
        $letterYear = $death->death_date ? $death->death_date->format('Y') : date('Y');
        $letterNumberSeq = (int)substr($death->registration_no, -4) ?: '82';

        // Hitung Umur Jenazah
        $deceasedAge = null;
        if ($death->birth_date && $death->death_date) {
            $deceasedAge = (int) $death->birth_date->diffInYears($death->death_date);
        } elseif ($death->birth_date) {
            $deceasedAge = (int) $death->birth_date->diffInYears(now());
        }

        // Hitung Umur Ibu & Ayah
        $motherAge = $death->mother_birth_date ? (int) $death->mother_birth_date->diffInYears($death->death_date ?? now()) : null;
        $fatherAge = $death->father_birth_date ? (int) $death->father_birth_date->diffInYears($death->death_date ?? now()) : null;

        $genderText = $death->gender === 'L' ? 'LAKI - LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-');

        $deceasedFullAddress = $death->deceased_address ?: ('Padukuhan ' . $death->padukuhan . ', RT ' . $death->rt . ' / RW ' . $death->rw . ', Purwobinangun, Pakem, Sleman');
        $reportDateFormatted = $death->report_date ? \Carbon\Carbon::parse($death->report_date)->translatedFormat('d F Y') : ($death->created_at ? $death->created_at->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y'));
        $officerName = $death->officer_name ?: 'Teguh Kurniawan, S.Pi.';

        $formatBirthAndAge = function($place, $date, $age) {
            $parts = [];
            if ($place) $parts[] = ucwords(strtolower($place));
            if ($date) $parts[] = $date->format('d - m - Y');
            $base = implode(', ', $parts);
            if ($age) {
                $base .= ($base ? ' / ' : '') . $age . ' tahun';
            }
            return $base ?: '';
        };

        $formatChildOrder = function ($order) {
            if ($order === null || trim((string) $order) === '' || trim((string) $order) === '-') {
                return '';
            }
            $words = [
                1 => 'Satu', 2 => 'Dua', 3 => 'Tiga', 4 => 'Empat', 5 => 'Lima',
                6 => 'Enam', 7 => 'Tujuh', 8 => 'Delapan', 9 => 'Sembilan', 10 => 'Sepuluh',
                11 => 'Sebelas', 12 => 'Dua Belas', 13 => 'Tiga Belas', 14 => 'Empat Belas', 15 => 'Lima Belas',
                16 => 'Enam Belas', 17 => 'Tujuh Belas', 18 => 'Delapan Belas', 19 => 'Sembilan Belas', 20 => 'Dua Puluh',
            ];
            $str = trim((string) $order);
            if (preg_match('/^(\d+)\s*\((.*?)\)$/i', $str)) {
                return $str;
            }
            if (is_numeric($str)) {
                $num = (int) $str;
                return isset($words[$num]) ? "{$num} ({$words[$num]})" : (string) $num;
            }
            foreach ($words as $n => $w) {
                if (strcasecmp($str, $w) === 0) {
                    return "{$n} ({$w})";
                }
            }
            if (preg_match('/\b(\d+)\b/', $str, $m)) {
                $num = (int) $m[1];
                return isset($words[$num]) ? "{$num} ({$words[$num]})" : $str;
            }
            return $str;
        };
        $childOrderFormatted = $formatChildOrder($death->deceased_child_order);

        $motherBirth = $formatBirthAndAge($death->mother_birth_place, $death->mother_birth_date, $motherAge);
        $fatherBirth = $formatBirthAndAge($death->father_birth_place, $death->father_birth_date, $fatherAge);
        $applicantBirth = $formatBirthAndAge($death->applicant_birth_place, $death->applicant_birth_date, $death->applicant_age);
        $witness1Birth = $formatBirthAndAge($death->witness1_birth_place, $death->witness1_birth_date, $death->witness1_age);
        $witness2Birth = $formatBirthAndAge($death->witness2_birth_place, $death->witness2_birth_date, $death->witness2_age);
        $dotsPlaceholder = '...................................................................';
        $w1NikVal = $death->witness1_nik ?: ($death->witness_nik ?: '');
        $w1NameVal = $death->witness1_name ?: ($death->witness_name ?: '');
    @endphp

    <div id="letter-content">

        <!-- =========================================================================
             HALAMAN 1: FORMULIR DATA ISIAN PENGAJUAN AKTA KEMATIAN
        ========================================================================== -->
        <div class="print-page">
            <div class="flex justify-between items-start mb-2 text-[8.5pt]">
                <div>No. Registrasi: <strong class="underline">{{ $death->registration_no }}</strong></div>
                <div class="text-slate-500 text-[8pt]">Formulir Data Pengajuan - Halaman 1</div>
            </div>

            <div class="text-center font-bold text-[11.5pt] uppercase underline mb-3">
                FORMULIR PELAPORAN &amp; PERMOHONAN AKTA KEMATIAN
            </div>

            <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 12px;">
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000; padding: 3px 6px;">I. DATA KELUARGA &amp; PENDAFTARAN</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px; width: 34%;">Nomor Kartu Keluarga (KK)</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ $death->family_card_no ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Nama Kepala Keluarga</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;" class="uppercase">{{ $death->head_of_family_name ?: '-' }}</td>
                </tr>

                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000; padding: 3px 6px;">II. BIODATA LENGKAP JENAZAH</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">NIK &amp; Nama Lengkap Jenazah</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;" class="uppercase">{{ $death->deceased_nik ?: '-' }} &mdash; {{ $death->deceased_name ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Jenis Kelamin / Anak Ke</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ $genderText }}{{ $childOrderFormatted ? ' / ' . $childOrderFormatted : '' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Tempat, Tanggal Lahir / Umur</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ ucwords(strtolower($death->deceased_birth_place ?: 'Sleman')) }}, {{ $death->birth_date ? $death->birth_date->format('d - m - Y') : '-' }}{{ $deceasedAge !== null ? ' / ' . $deceasedAge . ' tahun' : '' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Agama &amp; Pekerjaan</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;" class="uppercase">{{ $death->religion ?: '-' }} &mdash; {{ $death->deceased_job ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Alamat Jenazah</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;" class="uppercase">{{ $deceasedFullAddress }}</td>
                </tr>

                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000; padding: 3px 6px;">III. RINCIAN PERISTIWA KEMATIAN</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Hari, Tanggal &amp; Pukul Kematian</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;" class="uppercase">{{ $death->death_day ? $death->death_day . ', ' : '' }}{{ $death->death_date ? $death->death_date->translatedFormat('d F Y') : '-' }}{{ $death->death_time ? ' — Pukul ' . $death->death_time . ' WIB' : '' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Tempat &amp; Sebab Kematian</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;" class="uppercase">{{ $death->death_place ?: '-' }}{{ $death->death_location_detail ? ' (' . $death->death_location_detail . ')' : '' }} &mdash; {{ $death->cause_of_death ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Yang Menerangkan</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;" class="uppercase">{{ $death->reported_by_title ?: 'LAINNYA' }}</td>
                </tr>

                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000; padding: 3px 6px;">IV. DATA ORANG TUA KANDUNG JENAZAH</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Ibu Kandung (Nama / NIK / TTL / Alamat)</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ strtoupper($death->mother_name ?: '-') }} ({{ $death->mother_nik ?: '-' }}) &mdash; {{ $motherBirth ?: '-' }} &mdash; {{ $death->mother_address ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Ayah Kandung (Nama / NIK / TTL / Alamat)</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ strtoupper($death->father_name ?: '-') }} ({{ $death->father_nik ?: '-' }}) &mdash; {{ $fatherBirth ?: '-' }} &mdash; {{ $death->father_address ?: '-' }}</td>
                </tr>

                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="border: 1px solid #000; padding: 3px 6px;">V. DATA PELAPOR (AHLI WARIS) &amp; SAKSI</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Pelapor (Nama / NIK / Hubungan / Telp)</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ strtoupper($death->applicant_name ?: '-') }} / {{ $death->applicant_nik ?: '-' }} ({{ $death->applicant_relation ?: 'Ahli Waris' }}){{ $death->applicant_phone ? ' | ' . $death->applicant_phone : '' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">TTL / Pekerjaan / Alamat Pelapor</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ $applicantBirth ?: '-' }} &mdash; {{ $death->applicant_job ?: '-' }} &mdash; {{ $death->applicant_address ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Saksi I (Nama / NIK / Alamat)</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ strtoupper($w1NameVal ?: '-') }} ({{ $w1NikVal ?: '-' }}) &mdash; {{ $death->witness1_address ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 3px 6px;">Saksi II (Nama / NIK / Alamat)</td>
                    <td style="border: 1px solid #000; padding: 3px 6px; font-weight: bold;">{{ strtoupper($death->witness2_name ?: '-') }} ({{ $death->witness2_nik ?: '-' }}) &mdash; {{ $death->witness2_address ?: '-' }}</td>
                </tr>
            </table>

            <div class="flex justify-between items-end mt-6 text-[9pt]">
                <div class="text-center w-64">
                    <p>Sleman, {{ $reportDateFormatted }}</p>
                    <p class="font-semibold">Petugas Penerima Berkas,</p>
                    <div class="h-14"></div>
                    <p class="underline">( {{ $death->processed_by ?: '...................................................' }} )</p>
                </div>
                <div class="text-center w-64">
                    <p>Sleman, {{ $reportDateFormatted }}</p>
                    <p class="font-semibold">Pemohon / Pelapor,</p>
                    <div class="h-14"></div>
                    <p class="font-bold underline uppercase">( {{ $death->applicant_name ?: '-' }} )</p>
                </div>
            </div>
        </div>

        <!-- =========================================================================
             BAGIAN TERAKHIR: SURAT KETERANGAN KEMATIAN RESMI KALURAHAN PURWOBINANGUN
        ========================================================================== -->
        <div class="print-page page-break">
            
            <!-- 1. KOP SURAT RESMI KALURAHAN PURWOBINANGUN -->
            <div class="relative pb-1">
                <!-- Logo Sleman di Kiri -->
                <div class="absolute left-1 top-0">
                    <img src="{{ asset('images/logo-sleman.png') }}" alt="Logo Sleman" class="w-16 h-20 object-contain">
                </div>

                <!-- Teks Kop Surat Rata Tengah -->
                <div class="text-center pl-16 pr-2">
                    <h4 class="text-[10.5pt] font-bold tracking-wide uppercase leading-tight">
                        PEMERINTAH KABUPATEN SLEMAN
                    </h4>
                    <h4 class="text-[11.5pt] font-bold tracking-wide uppercase leading-tight mt-0.5">
                        KAPANEWON PAKEM
                    </h4>
                    <h3 class="text-[13.5pt] font-bold tracking-wide uppercase leading-tight mt-0.5">
                        PEMERINTAH KALURAHAN PURWOBINANGUN
                    </h3>
                    <div class="my-0.5 flex justify-center items-center">
                        <img src="{{ asset('images/aksara-purwobinangun.jpg') }}" alt="Aksara Jawa" class="h-4 object-contain">
                    </div>
                    <p class="text-[8.5pt] leading-tight text-slate-900">
                        Alamat : Jl. Pakem - Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582
                    </p>
                    <p class="text-[8.5pt] leading-tight mt-0.5 text-slate-900">
                        Laman : <span class="underline">www.purwobinangun.slemankab.go.id</span> &nbsp;&nbsp; Surel : desapurwobinangun@slemankab.go.id
                    </p>
                </div>

                <!-- Garis Ganda Pembatas Kop Surat -->
                <div class="mt-2" style="border-top: 3px solid #000; border-bottom: 0.72px solid #000; height: 2px;"></div>
            </div>

            <!-- 2. JUDUL SURAT & NOMOR -->
            <div class="text-center my-3.5">
                <h2 class="text-[15pt] font-bold tracking-wide uppercase">
                    SURAT KETERANGAN KEMATIAN
                </h2>
                <p class="text-[10.5pt] font-bold tracking-wide mt-0.5">
                    No. 474.3/ &nbsp;{{ $letterNumberSeq }}&nbsp; /{{ $monthRoman }}/{{ $letterYear }}
                </p>
            </div>

            <!-- 3. DATA KEPALA KELUARGA & NOMOR KK -->
            <div class="mb-3.5">
                <table class="data-table">
                    <tr>
                        <td class="w-7"></td>
                        <td class="w-56">Nama Kepala Keluarga</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->head_of_family_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7"></td>
                        <td class="w-56">Nomor Kartu Keluarga</td>
                        <td class="w-4">:</td>
                        <td>{{ $death->family_card_no ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- 4. SECTION: JENAZAH -->
            <div class="mb-2">
                <h3 class="font-bold text-[10.5pt] uppercase mb-1">
                    JENAZAH
                </h3>

                <table class="data-table">
                    <tr>
                        <td class="w-7">1</td>
                        <td class="w-56">NIK</td>
                        <td class="w-4">:</td>
                        <td>{{ $death->deceased_nik ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->deceased_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Jenis Kelamin / Anak ke</td>
                        <td class="w-4">:</td>
                        <td>{{ $genderText }}{{ $childOrderFormatted ? ' / ' . $childOrderFormatted : '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4">:</td>
                        <td>{{ ucwords(strtolower($death->deceased_birth_place ?: 'Sleman')) }}, {{ $death->birth_date ? $death->birth_date->format('d - m - Y') : '-' }}{{ $deceasedAge !== null ? ' / ' . $deceasedAge . ' tahun' : '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">5</td>
                        <td class="w-56">Agama</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->religion ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">6</td>
                        <td class="w-56">Pekerjaan</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->deceased_job ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">7</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $deceasedFullAddress }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">8</td>
                        <td class="w-56">Tanggal dan Jam Kematian</td>
                        <td class="w-4">:</td>
                        <td>
                            <div class="flex items-center justify-between pr-8">
                                <span>{{ $death->death_date ? $death->death_date->format('d - m - Y') : '-' }}</span>
                                <span>Jam: {{ $death->death_time ? $death->death_time . ' WIB' : '-' }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-7">9</td>
                        <td class="w-56">Sebab Kematian</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->cause_of_death ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">10</td>
                        <td class="w-56">Tempat Kematian</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->death_place ?: '-' }}{{ $death->death_location_detail ? ' (' . $death->death_location_detail . ')' : '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">11</td>
                        <td class="w-56">Yang Menerangkan</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->reported_by_title ?: 'LAINNYA' }}</td>
                    </tr>
                </table>
            </div>

        </div>

        <!-- =========================================================================
             HALAMAN 2: SECTION IBU, AYAH, PELAPOR, SAKSI 1, SAKSI 2 & TANDA TANGAN
        ========================================================================== -->
        <div class="print-page page-break">
            
            <!-- 1. SECTION: IBU -->
            <div class="mb-3">
                <h3 class="font-bold text-[10.5pt] uppercase mb-1">
                    IBU
                </h3>

                <table class="data-table">
                    <tr>
                        <td class="w-7">1</td>
                        <td class="w-56">NIK</td>
                        <td class="w-4">:</td>
                        <td>{{ $death->mother_nik ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->mother_name ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $motherBirth ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->mother_address ?: $dotsPlaceholder }}</td>
                    </tr>
                </table>
            </div>

            <!-- 2. SECTION: AYAH -->
            <div class="mb-3">
                <h3 class="font-bold text-[10.5pt] uppercase mb-1">
                    AYAH
                </h3>

                <table class="data-table">
                    <tr>
                        <td class="w-7">1</td>
                        <td class="w-56">NIK</td>
                        <td class="w-4">:</td>
                        <td>{{ $death->father_nik ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->father_name ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $fatherBirth ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->father_address ?: $dotsPlaceholder }}</td>
                    </tr>
                </table>
            </div>

            <!-- 3. SECTION: PELAPOR -->
            <div class="mb-3">
                <h3 class="font-bold text-[10.5pt] uppercase mb-1">
                    PELAPOR
                </h3>

                <table class="data-table">
                    <tr>
                        <td class="w-7">1</td>
                        <td class="w-56">NIK</td>
                        <td class="w-4">:</td>
                        <td>{{ $death->applicant_nik ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->applicant_name ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $applicantBirth ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->applicant_address ?: $dotsPlaceholder }}</td>
                    </tr>
                </table>
            </div>

            <!-- 4. SECTION: SAKSI 1 -->
            <div class="mb-3">
                <h3 class="font-bold text-[10.5pt] uppercase mb-1">
                    SAKSI 1
                </h3>

                <table class="data-table">
                    <tr>
                        <td class="w-7">1</td>
                        <td class="w-56">NIK</td>
                        <td class="w-4">:</td>
                        <td>{{ $w1NikVal ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $w1NameVal ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $witness1Birth ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->witness1_address ?: $dotsPlaceholder }}</td>
                    </tr>
                </table>
            </div>

            <!-- 5. SECTION: SAKSI 2 -->
            <div class="mb-4">
                <h3 class="font-bold text-[10.5pt] uppercase mb-1">
                    SAKSI 2
                </h3>

                <table class="data-table">
                    <tr>
                        <td class="w-7">1</td>
                        <td class="w-56">NIK</td>
                        <td class="w-4">:</td>
                        <td>{{ $death->witness2_nik ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->witness2_name ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $witness2Birth ?: $dotsPlaceholder }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->witness2_address ?: $dotsPlaceholder }}</td>
                    </tr>
                </table>
            </div>

            <!-- 6. TANDA TANGAN (SIGNATURE BLOCK) -->
            <div class="pt-4 flex justify-end">
                <div class="text-left w-72 pr-4">
                    <p>Purwobinangun, {{ $reportDateFormatted }}</p>
                    
                    <!-- Ruang tanda tangan & cap stempel -->
                    <div class="h-24"></div>

                    <p class="font-medium text-[10pt]">
                        ( .................................................... )
                    </p>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
