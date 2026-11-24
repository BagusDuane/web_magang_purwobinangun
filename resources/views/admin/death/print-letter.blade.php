<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Kematian & Formulir Pelaporan - {{ $death->deceased_name }} - {{ $death->registration_no }}</title>
    
    <!-- Google Fonts for Serif Body, Javanese Script, and Sans UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Javanese:wght@400;700&family=Tinos:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- html2pdf for direct A4 PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Times New Roman', Times, 'Tinos', 'Bookman Old Style', serif;
            color: #000;
            background-color: #f1f5f9;
        }

        .font-javanese {
            font-family: 'Noto Sans Javanese', serif;
        }

        .font-sans-ui {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            box-sizing: border-box;
            background-color: #ffffff;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 2rem;
            padding: 12mm 15mm;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            border: 1px solid #cbd5e1;
            position: relative;
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
                color: black !important; 
            }
            .print-page {
                box-shadow: none !important;
                border: none !important;
                padding: 10mm 15mm !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                box-sizing: border-box !important;
                -webkit-box-decoration-break: clone;
                box-decoration-break: clone;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }
            .section-block {
                page-break-inside: avoid;
            }
        }

        /* Border table styling for forms */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            line-height: 1.35;
        }
        .form-table th, .form-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: top;
        }
        .form-table-nested {
            width: 100%;
            border-collapse: collapse;
        }
        .form-table-nested td {
            border: none;
            padding: 1.5px 0;
            vertical-align: top;
        }

        .checkbox-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            text-align: center;
            line-height: 13px;
            font-size: 8pt;
            font-weight: bold;
            margin-right: 4px;
            vertical-align: middle;
        }
    </style>
</head>
<body class="p-4 md:p-8 antialiased">

    <!-- Action Toolbar (No Print) -->
    <div class="max-w-[210mm] mx-auto mb-4 no-print flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl shadow-xs border border-slate-200">
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
            <button onclick="window.print()" class="font-sans-ui bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs px-3.5 py-2 rounded-lg shadow-xs transition flex items-center gap-1.5 cursor-pointer">
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
            $deceasedAge = $death->birth_date->diffInYears($death->death_date);
        } elseif ($death->birth_date) {
            $deceasedAge = $death->birth_date->diffInYears(now());
        }

        // Hitung Umur Ibu & Ayah jika ada tgl lahir
        $motherAge = $death->mother_birth_date ? $death->mother_birth_date->diffInYears($death->death_date ?? now()) : null;
        $fatherAge = $death->father_birth_date ? $death->father_birth_date->diffInYears($death->death_date ?? now()) : null;

        // Gender formatting
        $genderText = $death->gender === 'L' ? 'LAKI-LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-');

        // Alamat Jenazah
        $deceasedFullAddress = $death->deceased_address ?: ('Padukuhan ' . $death->padukuhan . ', RT ' . $death->rt . ' / RW ' . $death->rw . ', Purwobinangun, Pakem, Sleman');
        $reportDateFormatted = $death->report_date ? \Carbon\Carbon::parse($death->report_date)->translatedFormat('d F Y') : ($death->created_at ? $death->created_at->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y'));
        $deathDateFormatted = $death->death_date ? ($death->death_day ? strtoupper($death->death_day) . ' / ' : '') . $death->death_date->translatedFormat('d F Y') : '-';
    @endphp

    <div id="letter-content">

        <!-- =========================================================================
             HALAMAN 1: PERMOHONAN AKTA KEMATIAN & DOKUMEN PERSYARATAN (BERKAS WARGA)
        ========================================================================== -->
        <div class="print-page text-black text-[9pt]">
            
            <div class="flex justify-between items-start mb-2">
                <div class="text-[8.5pt]">
                    <table class="form-table-nested">
                        <tr>
                            <td class="w-10">No.</td>
                            <td class="w-2">:</td>
                            <td class="font-bold underline">{{ $death->registration_no }}</td>
                        </tr>
                    </table>
                </div>
                <div class="text-right text-[8pt] text-slate-500">
                    Formulir Permohonan Warga - Halaman 1
                </div>
            </div>

            <div class="text-center font-bold text-[11pt] tracking-wide mb-3 uppercase">
                PERMOHONAN AKTA KEMATIAN
            </div>

            <table class="form-table mb-3">
                <!-- 1. DATA KELUARGA -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">Data Keluarga</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">1</td>
                    <td class="w-52">Nomor Kartu Keluarga (KK)</td>
                    <td>: <span class="font-bold">{{ $death->family_card_no ?: '-' }}</span></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">2</td>
                    <td>Nama Kepala Keluarga</td>
                    <td>: <span class="font-bold uppercase">{{ $death->head_of_family_name ?: '-' }}</span></td>
                </tr>

                <!-- 2. DATA JENAZAH -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">Data Jenazah (Orang yang Meninggal)</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">1</td>
                    <td>NIK</td>
                    <td>: <span class="font-bold">{{ $death->deceased_nik }}</span></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">2</td>
                    <td>Nama Lengkap</td>
                    <td>: <span class="font-bold uppercase">{{ $death->deceased_name }}</span></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">3</td>
                    <td>Tempat dan Tanggal Lahir / Umur</td>
                    <td>
                        : {{ strtoupper($death->deceased_birth_place ?: 'SLEMAN') }}, {{ $death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-' }}
                        @if($deceasedAge !== null)
                            &nbsp;&bull;&nbsp; Umur: <strong>{{ $deceasedAge }} Tahun</strong>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">4</td>
                    <td>Jenis Kelamin / Anak Ke</td>
                    <td>: Jenis Kelamin: <strong>{{ $genderText }}</strong> &nbsp;|&nbsp; Anak ke: <strong>{{ $death->deceased_child_order ?: '-' }}</strong></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">5</td>
                    <td>Agama / Pekerjaan</td>
                    <td>: Agama: <strong>{{ strtoupper($death->religion ?: '-') }}</strong> &nbsp;|&nbsp; Pekerjaan: <strong>{{ strtoupper($death->deceased_job ?: 'PENSIUNAN') }}</strong></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">6</td>
                    <td>Alamat Jenazah</td>
                    <td>: {{ $deceasedFullAddress }}</td>
                </tr>

                <!-- 3. DATA KEMATIAN -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">Keterangan Peristiwa Kematian</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">1</td>
                    <td>Hari / Tanggal Kematian</td>
                    <td>: <span class="font-bold">{{ $deathDateFormatted }}</span></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">2</td>
                    <td>Waktu / Jam Kematian</td>
                    <td>: {{ $death->death_time ? $death->death_time . ' WIB' : '-' }}</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">3</td>
                    <td>Tempat Meninggal</td>
                    <td>: <strong>{{ strtoupper($death->death_place ?: 'RUMAH') }}</strong> {{ $death->death_location_detail ? '(' . $death->death_location_detail . ')' : '' }}</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">4</td>
                    <td>Penyebab Kematian</td>
                    <td>: <strong>{{ strtoupper($death->cause_of_death ?: 'SAKIT BIASA / TUA') }}</strong></td>
                </tr>

                <!-- 4. DATA PELAPOR -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">Data Pelapor / Ahli Waris</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">1</td>
                    <td>NIK Pelapor</td>
                    <td>: {{ $death->applicant_nik ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">2</td>
                    <td>Nama Lengkap Pelapor</td>
                    <td>: <span class="font-bold uppercase">{{ $death->applicant_name ?: '-' }}</span></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">3</td>
                    <td>Hubungan dengan Almarhum/ah</td>
                    <td>: <strong>{{ $death->applicant_relation ? ucfirst($death->applicant_relation) : ($death->reported_by_title ? ucfirst($death->reported_by_title) : 'Keluarga / Ahli Waris') }}</strong></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">4</td>
                    <td>Nomor HP/Telepon</td>
                    <td>: {{ $death->applicant_phone ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">5</td>
                    <td>Alamat Pelapor</td>
                    <td>: {{ $death->applicant_address ?: $deceasedFullAddress }}</td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-semibold">6</td>
                    <td>Tanggal Lapor</td>
                    <td>: {{ $reportDateFormatted }}</td>
                </tr>
            </table>

            <!-- DOKUMEN PERSYARATAN -->
            <table class="form-table mb-4">
                <tr class="bg-slate-100 font-bold">
                    <td colspan="2" class="uppercase">Dokumen Persyaratan (Diperiksa oleh Petugas)</td>
                    <td class="w-20 text-center uppercase">Status</td>
                </tr>
                @php
                    $checkDeathDocs = [
                        ['Surat Keterangan Kematian dari Dokter / RS / Puskesmas', !empty($death->doc_death_statement)],
                        ['Surat Pengantar RT / RW Setempat', !empty($death->doc_rt_rw_statement)],
                        ['Kartu Keluarga (KK) Asli / Fotokopi yang bersangkutan', !empty($death->doc_family_card)],
                        ['KTP-el Almarhum/Almarhumah Asli / Fotokopi', !empty($death->doc_deceased_ktp)],
                        ['KTP-el Pelapor / Ahli Waris', !empty($death->doc_applicant_ktp) || !empty($death->doc_heir_ktp)],
                        ['Kartu Keluarga (KK) Ahli Waris', !empty($death->doc_heir_family_card)],
                    ];
                @endphp
                @foreach($checkDeathDocs as $d)
                    <tr>
                        <td class="w-6 text-center">
                            <span class="checkbox-box">{{ $d[1] ? 'v' : '' }}</span>
                        </td>
                        <td>{{ $d[0] }}</td>
                        <td class="text-center font-semibold {{ $d[1] ? 'text-emerald-800' : 'text-slate-400' }}">
                            {{ $d[1] ? 'Ada / Terlampir' : '-' }}
                        </td>
                    </tr>
                @endforeach
            </table>

            <!-- Tanda Tangan Form Permohonan -->
            <div class="flex justify-between items-end mt-4 pt-2 text-[9pt]">
                <div class="text-center w-64">
                    <p>SLEMAN, {{ $reportDateFormatted }}</p>
                    <p class="font-semibold">Petugas Penerima Berkas,</p>
                    <div class="h-16"></div>
                    <p class="font-medium underline">( .................................................... )</p>
                </div>
                <div class="text-center w-64">
                    <p>SLEMAN, {{ $reportDateFormatted }}</p>
                    <p class="font-semibold">Pelapor / Ahli Waris,</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline uppercase">( {{ $death->applicant_name ?: '-' }} )</p>
                </div>
            </div>

        </div>

        <!-- =========================================================================
             HALAMAN 2: FORMULIR PELAPORAN KEMATIAN (KODE F-2.29)
        ========================================================================== -->
        <div class="print-page page-break text-black text-[8.5pt]">
            
            <div class="flex justify-between items-start mb-1">
                <div class="border border-black px-2 py-0.5 font-bold text-[8.5pt]">
                    Kode F-2.29
                </div>
                <div class="text-right text-[8pt] text-slate-500">
                    Formulir Pelaporan Kematian - Halaman 2
                </div>
            </div>

            <div class="text-center mb-2">
                <h3 class="font-bold text-[10.5pt] uppercase tracking-wide">FORMULIR PELAPORAN KEMATIAN</h3>
                <p class="text-[8.5pt] italic">(Untuk Penerbitan Akta Kematian)</p>
                <h4 class="font-bold text-[9.5pt] uppercase">PEMERINTAH KABUPATEN SLEMAN</h4>
            </div>

            <table class="form-table mb-2">
                <!-- DATA KELUARGA -->
                <tr>
                    <td class="w-8 text-center font-bold">1</td>
                    <td class="w-48 font-medium">Nomor Kartu Keluarga (KK)</td>
                    <td>: <span class="font-bold">{{ $death->family_card_no ?: '-' }}</span></td>
                </tr>
                <tr>
                    <td class="w-8 text-center font-bold">2</td>
                    <td class="font-medium">Nama Kepala Keluarga</td>
                    <td>: <span class="font-bold uppercase">{{ $death->head_of_family_name ?: '-' }}</span></td>
                </tr>

                <!-- JENAZAH -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">JENAZAH</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">1</td>
                    <td>NIK</td>
                    <td>: <span class="font-bold">{{ $death->deceased_nik }}</span></td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">2</td>
                    <td>Nama Lengkap</td>
                    <td>: <span class="font-bold uppercase">{{ $death->deceased_name }}</span></td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">3</td>
                    <td>Jenis Kelamin</td>
                    <td>: <strong>{{ $genderText }}</strong></td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">4</td>
                    <td>Tempat, Tanggal Lahir / Umur</td>
                    <td>: {{ strtoupper($death->deceased_birth_place ?: 'SLEMAN') }}, {{ $death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-' }} &nbsp;&bull;&nbsp; Umur: {{ $deceasedAge !== null ? $deceasedAge . ' th' : '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">5</td>
                    <td>Agama / Pekerjaan</td>
                    <td>: {{ strtoupper($death->religion ?: '-') }} &nbsp;|&nbsp; {{ strtoupper($death->deceased_job ?: 'PENSIUNAN') }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">6</td>
                    <td>Alamat Sesuai KTP</td>
                    <td>: {{ $deceasedFullAddress }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">7</td>
                    <td>Hari, Tanggal & Jam Kematian</td>
                    <td>: {{ $deathDateFormatted }} &nbsp;|&nbsp; Pukul: {{ $death->death_time ? $death->death_time . ' WIB' : '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">8</td>
                    <td>Tempat Kematian / Keterangan</td>
                    <td>: {{ strtoupper($death->death_place ?: 'RUMAH') }} {{ $death->death_location_detail ? '(' . $death->death_location_detail . ')' : '' }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">9</td>
                    <td>Sebab Kematian</td>
                    <td>: {{ strtoupper($death->cause_of_death ?: 'SAKIT BIASA / TUA') }}</td>
                </tr>

                <!-- IBU KANDUNG -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">IBU KANDUNG</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">1</td>
                    <td>NIK</td>
                    <td>: {{ $death->mother_nik ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">2</td>
                    <td>Nama Lengkap</td>
                    <td>: <span class="font-bold uppercase">{{ $death->mother_name ?: '-' }}</span></td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">3</td>
                    <td>Tempat & Tanggal Lahir / Umur</td>
                    <td>
                        : {{ $death->mother_birth_place ? strtoupper($death->mother_birth_place) . ', ' : '' }}{{ $death->mother_birth_date ? $death->mother_birth_date->translatedFormat('d F Y') : '-' }}
                        @if($motherAge)
                            &nbsp;&bull;&nbsp; Umur: {{ $motherAge }} th
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">4</td>
                    <td>Pekerjaan / Alamat</td>
                    <td>: {{ $death->mother_job ?: '-' }} &nbsp;|&nbsp; {{ $death->mother_address ?: '-' }}</td>
                </tr>

                <!-- AYAH KANDUNG -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">AYAH KANDUNG</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">1</td>
                    <td>NIK</td>
                    <td>: {{ $death->father_nik ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">2</td>
                    <td>Nama Lengkap</td>
                    <td>: <span class="font-bold uppercase">{{ $death->father_name ?: '-' }}</span></td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">3</td>
                    <td>Tempat & Tanggal Lahir / Umur</td>
                    <td>
                        : {{ $death->father_birth_place ? strtoupper($death->father_birth_place) . ', ' : '' }}{{ $death->father_birth_date ? $death->father_birth_date->translatedFormat('d F Y') : '-' }}
                        @if($fatherAge)
                            &nbsp;&bull;&nbsp; Umur: {{ $fatherAge }} th
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">4</td>
                    <td>Pekerjaan / Alamat</td>
                    <td>: {{ $death->father_job ?: '-' }} &nbsp;|&nbsp; {{ $death->father_address ?: '-' }}</td>
                </tr>

                <!-- PELAPOR -->
                <tr class="bg-slate-100 font-bold">
                    <td colspan="3" class="uppercase">PELAPOR</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">1</td>
                    <td>NIK</td>
                    <td>: {{ $death->applicant_nik ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">2</td>
                    <td>Nama Lengkap</td>
                    <td>: <span class="font-bold uppercase">{{ $death->applicant_name ?: '-' }}</span></td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">3</td>
                    <td>Umur / Pekerjaan</td>
                    <td>: {{ $death->applicant_age ? $death->applicant_age . ' Tahun' : '-' }} &nbsp;|&nbsp; {{ $death->applicant_job ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-center font-semibold">4</td>
                    <td>Alamat</td>
                    <td>: {{ $death->applicant_address ?: $deceasedFullAddress }}</td>
                </tr>
            </table>

            <!-- SAKSI-SAKSI -->
            <table class="form-table mb-3">
                <tr class="bg-slate-100 font-bold">
                    <td class="w-1/2 uppercase text-center">SAKSI I</td>
                    <td class="w-1/2 uppercase text-center">SAKSI II</td>
                </tr>
                <tr>
                    <td>
                        <table class="form-table-nested">
                            <tr><td class="w-20 font-medium">NIK</td><td class="w-2">:</td><td>{{ $death->witness1_nik ?: ($death->witness_nik ?: '-') }}</td></tr>
                            <tr><td class="font-medium">Nama Lengkap</td><td>:</td><td class="font-bold uppercase">{{ $death->witness1_name ?: ($death->witness_name ?: '-') }}</td></tr>
                            <tr><td class="font-medium">Umur</td><td>:</td><td>{{ $death->witness1_age ? $death->witness1_age . ' Tahun' : '-' }}</td></tr>
                            <tr><td class="font-medium">Alamat</td><td>:</td><td>{{ $death->witness1_address ?: 'Purwobinangun, Pakem, Sleman' }}</td></tr>
                        </table>
                    </td>
                    <td>
                        <table class="form-table-nested">
                            <tr><td class="w-20 font-medium">NIK</td><td class="w-2">:</td><td>{{ $death->witness2_nik ?: '-' }}</td></tr>
                            <tr><td class="font-medium">Nama Lengkap</td><td>:</td><td class="font-bold uppercase">{{ $death->witness2_name ?: '-' }}</td></tr>
                            <tr><td class="font-medium">Umur</td><td>:</td><td>{{ $death->witness2_age ? $death->witness2_age . ' Tahun' : '-' }}</td></tr>
                            <tr><td class="font-medium">Alamat</td><td>:</td><td>{{ $death->witness2_address ?: 'Purwobinangun, Pakem, Sleman' }}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- DATA ADMINISTRASI DESA -->
            <div class="border border-black p-2 mb-2 text-[8pt] flex justify-between items-center">
                <div>
                    <strong>DATA ADMINISTRASI:</strong><br>
                    Jenis Pelaporan: <strong>Permohonan Akta Kematian</strong><br>
                    Nama Petugas Register: <strong>{{ $death->processor ? $death->processor->name : (auth()->check() ? auth()->user()->name : 'Petugas Registrasi') }}</strong>
                </div>
                <div class="text-center pr-6">
                    Paraf Petugas:<br>
                    <div class="h-8"></div>
                    ( .................... )
                </div>
            </div>

        </div>

        <!-- =========================================================================
             HALAMAN 3 (PALING BELAKANG): SURAT KETERANGAN KEMATIAN RESMI KALURAHAN
        ========================================================================== -->
        <div class="print-page page-break text-black text-[10pt] leading-[1.6]">
            
            <!-- 1. KOP SURAT RESMI -->
            <div class="relative pb-1">
                <!-- Logo Sleman di Kiri -->
                <div class="absolute left-1 top-0">
                    <img src="{{ asset('images/logo-sleman.png') }}" alt="Logo Sleman" class="w-16 h-20 sm:w-20 sm:h-24 object-contain">
                </div>

                <!-- Teks Kop Surat Rata Tengah -->
                <div class="text-center pl-16 pr-2">
                    <h4 class="text-[12pt] font-bold tracking-wide uppercase leading-tight">
                        PEMERINTAH KABUPATEN SLEMAN
                    </h4>
                    <h4 class="text-[12pt] font-bold tracking-wide uppercase leading-tight mt-0.5">
                        KAPANEWON PAKEM
                    </h4>
                    <h3 class="text-[14pt] font-extrabold tracking-wide uppercase leading-tight mt-0.5">
                        PEMERINTAH KALURAHAN PURWOBINANGUN
                    </h3>
                    <div class="font-javanese text-[11pt] leading-tight my-0.5">
                        ꦥꦩꦫꦺꦤ꧀ꦠꦃꦏꦭꦸꦫꦲꦤ꧀ꦥꦸꦂꦮꦧꦶꦤꦔꦸꦤ꧀
                    </div>
                    <p class="text-[8pt] leading-tight text-slate-800">
                        Alamat : Jl. Pakem-Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582
                    </p>
                    <p class="text-[8pt] leading-tight mt-0.5 text-slate-800">
                        Laman : <span class="underline">www.purwobinangun.slemankab.go.id</span> &nbsp; Surel : desapurwobinangun@slemankab.go.id
                    </p>
                </div>

                <!-- Garis Ganda Pembatas Kop Surat -->
                <div class="mt-2.5" style="border-top: 3px solid #000; border-bottom: 1px solid #000; height: 3px;"></div>
            </div>

            <!-- 2. JUDUL SURAT & NOMOR -->
            <div class="text-center my-4">
                <h2 class="text-[13pt] font-extrabold tracking-wide uppercase">
                    SURAT KETERANGAN KEMATIAN
                </h2>
                <p class="text-[10pt] font-bold tracking-wide mt-0.5">
                    No. 474.3/{{ $letterNumberSeq }}/{{ $monthRoman }}/{{ $letterYear }}
                </p>
            </div>

            <!-- Data Kepala Keluarga & No KK -->
            <div class="mb-4 text-[10pt]">
                <table class="w-full">
                    <tr>
                        <td class="w-48 py-0.5">Nama Kepala Keluarga</td>
                        <td class="w-4 py-0.5">:</td>
                        <td class="py-0.5 uppercase font-medium">{{ $death->head_of_family_name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="w-48 py-0.5">Nomor Kartu Keluarga</td>
                        <td class="w-4 py-0.5">:</td>
                        <td class="py-0.5 font-medium">{{ $death->family_card_no ?: '-' }}</td>
                    </tr>
                </table>
            </div>

            <!-- 3. SECTION: JENAZAH -->
            <div class="section-block mb-4">
                <h4 class="font-extrabold uppercase tracking-wider text-[10.5pt] mb-1">
                    JENAZAH
                </h4>

                <table class="w-full text-[9.5pt]">
                    <tr>
                        <td class="w-6 py-0.5 align-top">1</td>
                        <td class="w-56 py-0.5 align-top">NIK</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">{{ $death->deceased_nik }}</td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">2</td>
                        <td class="w-56 py-0.5 align-top">Nama Lengkap</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-bold uppercase">{{ $death->deceased_name }}</td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">3</td>
                        <td class="w-56 py-0.5 align-top">Jenis Kelamin / Anak ke</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            <div class="flex items-center justify-between pr-8">
                                <span>{{ $genderText }}</span>
                                <span>Anak ke &nbsp; {{ $death->deceased_child_order ?: '-' }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">4</td>
                        <td class="w-56 py-0.5 align-top">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            <div class="flex items-center justify-between pr-8">
                                <span class="uppercase">
                                    {{ $death->deceased_birth_place ?: 'SLEMAN' }}, {{ $death->birth_date ? $death->birth_date->format('d-m-Y') : '-' }}
                                </span>
                                <span>Umur {{ $deceasedAge !== null ? $deceasedAge . ' th' : '-' }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">5</td>
                        <td class="w-56 py-0.5 align-top">Agama</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top uppercase font-medium">{{ $death->religion }}</td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">6</td>
                        <td class="w-56 py-0.5 align-top">Pekerjaan</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top uppercase font-medium">{{ $death->deceased_job ?: 'PENSIUNAN' }}</td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">7</td>
                        <td class="w-56 py-0.5 align-top">Alamat</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top uppercase font-medium">
                            {{ $deceasedFullAddress }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">8</td>
                        <td class="w-56 py-0.5 align-top">Tanggal dan Jam Kematian</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            <div class="flex items-center justify-between pr-8">
                                <span>{{ $death->death_date ? $death->death_date->format('d-m-Y') : '-' }}</span>
                                <span>Pukul &nbsp; {{ $death->death_time ? $death->death_time . ' WIB' : '-' }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">9</td>
                        <td class="w-56 py-0.5 align-top">Tempat Kematian</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top uppercase font-medium">
                            {{ $death->death_place }} {{ $death->death_location_detail ? '(' . $death->death_location_detail . ')' : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">10</td>
                        <td class="w-56 py-0.5 align-top">Penyebab Kematian</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top uppercase font-medium">{{ $death->cause_of_death }}</td>
                    </tr>
                </table>
            </div>

            <!-- 4. SECTION: IBU -->
            <div class="section-block mb-4">
                <h4 class="font-extrabold uppercase tracking-wider text-[10.5pt] mb-1">
                    IBU
                </h4>

                <table class="w-full text-[9.5pt]">
                    <tr>
                        <td class="w-6 py-0.5 align-top">1</td>
                        <td class="w-56 py-0.5 align-top">NIK</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            {{ $death->mother_nik ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">2</td>
                        <td class="w-56 py-0.5 align-top">Nama Lengkap</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->mother_name ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">3</td>
                        <td class="w-56 py-0.5 align-top">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            @if($death->mother_birth_place || $death->mother_birth_date)
                                {{ $death->mother_birth_place ?: 'SLEMAN' }}, {{ $death->mother_birth_date ? $death->mother_birth_date->format('d-m-Y') : '-' }}
                                @if($motherAge)
                                    &nbsp;&bull;&nbsp; Umur {{ $motherAge }} th
                                @endif
                            @else
                                ...................................................
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">4</td>
                        <td class="w-56 py-0.5 align-top">Pekerjaan</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->mother_job ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">5</td>
                        <td class="w-56 py-0.5 align-top">Alamat</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->mother_address ?: '...................................................' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 5. SECTION: AYAH -->
            <div class="section-block mb-4">
                <h4 class="font-extrabold uppercase tracking-wider text-[10.5pt] mb-1">
                    AYAH
                </h4>

                <table class="w-full text-[9.5pt]">
                    <tr>
                        <td class="w-6 py-0.5 align-top">1</td>
                        <td class="w-56 py-0.5 align-top">NIK</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            {{ $death->father_nik ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">2</td>
                        <td class="w-56 py-0.5 align-top">Nama Lengkap</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->father_name ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">3</td>
                        <td class="w-56 py-0.5 align-top">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            @if($death->father_birth_place || $death->father_birth_date)
                                {{ $death->father_birth_place ?: 'SLEMAN' }}, {{ $death->father_birth_date ? $death->father_birth_date->format('d-m-Y') : '-' }}
                                @if($fatherAge)
                                    &nbsp;&bull;&nbsp; Umur {{ $fatherAge }} th
                                @endif
                            @else
                                ...................................................
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">4</td>
                        <td class="w-56 py-0.5 align-top">Pekerjaan</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->father_job ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">5</td>
                        <td class="w-56 py-0.5 align-top">Alamat</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->father_address ?: '...................................................' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 6. SECTION: PELAPOR -->
            <div class="section-block mb-4">
                <h4 class="font-extrabold uppercase tracking-wider text-[10.5pt] mb-1">
                    PELAPOR
                </h4>

                <table class="w-full text-[9.5pt]">
                    <tr>
                        <td class="w-6 py-0.5 align-top">1</td>
                        <td class="w-56 py-0.5 align-top">NIK</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            {{ $death->applicant_nik ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">2</td>
                        <td class="w-56 py-0.5 align-top">Nama Lengkap</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->applicant_name ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">3</td>
                        <td class="w-56 py-0.5 align-top">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            @if($death->applicant_birth_place || $death->applicant_birth_date)
                                {{ $death->applicant_birth_place ?: 'SLEMAN' }}, {{ $death->applicant_birth_date ? $death->applicant_birth_date->format('d-m-Y') : '-' }}
                                @if($death->applicant_age)
                                    &nbsp;&bull;&nbsp; Umur {{ $death->applicant_age }} th
                                @endif
                            @elseif($death->applicant_age)
                                SLEMAN &nbsp;&bull;&nbsp; Umur {{ $death->applicant_age }} th
                            @else
                                ...................................................
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">4</td>
                        <td class="w-56 py-0.5 align-top">Pekerjaan</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->applicant_job ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">5</td>
                        <td class="w-56 py-0.5 align-top">Alamat</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->applicant_address ?: '...................................................' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 7. SECTION: SAKSI 1 -->
            <div class="section-block mb-4">
                <h4 class="font-extrabold uppercase tracking-wider text-[10.5pt] mb-1">
                    SAKSI 1
                </h4>

                <table class="w-full text-[9.5pt]">
                    <tr>
                        <td class="w-6 py-0.5 align-top">1</td>
                        <td class="w-56 py-0.5 align-top">NIK</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            {{ $death->witness1_nik ?: ($death->witness_nik ?: '...................................................') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">2</td>
                        <td class="w-56 py-0.5 align-top">Nama Lengkap</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->witness1_name ?: ($death->witness_name ?: '...................................................') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">3</td>
                        <td class="w-56 py-0.5 align-top">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            @if($death->witness1_birth_place || $death->witness1_birth_date)
                                {{ $death->witness1_birth_place ?: 'SLEMAN' }}, {{ $death->witness1_birth_date ? $death->witness1_birth_date->format('d-m-Y') : '-' }}
                                @if($death->witness1_age)
                                    &nbsp;&bull;&nbsp; Umur {{ $death->witness1_age }} th
                                @endif
                            @elseif($death->witness1_age)
                                SLEMAN, 23-03-1987 &nbsp;&bull;&nbsp; Umur {{ $death->witness1_age }} th
                            @else
                                ...................................................
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">4</td>
                        <td class="w-56 py-0.5 align-top">Alamat</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->witness1_address ?: '...................................................' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 8. SECTION: SAKSI 2 -->
            <div class="section-block mb-4">
                <h4 class="font-extrabold uppercase tracking-wider text-[10.5pt] mb-1">
                    SAKSI 2
                </h4>

                <table class="w-full text-[9.5pt]">
                    <tr>
                        <td class="w-6 py-0.5 align-top">1</td>
                        <td class="w-56 py-0.5 align-top">NIK</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium">
                            {{ $death->witness2_nik ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">2</td>
                        <td class="w-56 py-0.5 align-top">Nama Lengkap</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->witness2_name ?: '...................................................' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">3</td>
                        <td class="w-56 py-0.5 align-top">Tempat dan Tanggal Lahir / Umur</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            @if($death->witness2_birth_place || $death->witness2_birth_date)
                                {{ $death->witness2_birth_place ?: 'SLEMAN' }}, {{ $death->witness2_birth_date ? $death->witness2_birth_date->format('d-m-Y') : '-' }}
                                @if($death->witness2_age)
                                    &nbsp;&bull;&nbsp; Umur {{ $death->witness2_age }} th
                                @endif
                            @elseif($death->witness2_age)
                                SLEMAN, 07-04-1976 &nbsp;&bull;&nbsp; Umur {{ $death->witness2_age }} th
                            @else
                                ...................................................
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="w-6 py-0.5 align-top">4</td>
                        <td class="w-56 py-0.5 align-top">Alamat</td>
                        <td class="w-4 py-0.5 align-top">:</td>
                        <td class="py-0.5 align-top font-medium uppercase">
                            {{ $death->witness2_address ?: '...................................................' }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 9. TANDA TANGAN (SIGNATURE BLOCK) -->
            <div class="section-block pt-4 flex justify-end text-[10pt]">
                <div class="text-left w-72 pr-4">
                    <p>Sleman, {{ $death->created_at ? $death->created_at->translatedFormat('d F Y') : date('d F Y') }}</p>
                    
                    <!-- Ruang tanda tangan & cap stempel -->
                    <div class="h-24"></div>

                    <p class="font-medium text-[10.5pt]">
                        ( .................................................... )
                    </p>
                </div>
            </div>

        </div>

    </div>

    <!-- Script Download PDF menggunakan html2pdf -->
    <script>
        function downloadPDF() {
            const btn = document.getElementById('btn-download-pdf');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyiapkan PDF...';

            const element = document.getElementById('letter-content');
            const safeName = "{{ preg_replace('/[^A-Za-z0-9]/', '_', $death->deceased_name) }}";
            const regNo = "{{ $death->registration_no }}";

            const opt = {
                margin: [10, 15, 12, 15],
                filename: `Surat_Keterangan_Kematian_${safeName}_${regNo}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, letterRendering: true, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }).catch(function(err) {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('Gagal membuat PDF otomatis. Mengalihkan ke opsi cetak/simpan PDF browser...');
                window.print();
            });
        }
    </script>

</body>
</html>
