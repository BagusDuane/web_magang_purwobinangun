<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan & Formulir Pelaporan Kelahiran - {{ $birth->registration_no }}</title>
    
    <!-- Google Fonts for Serif Body, Javanese Script, and Sans UI -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Javanese:wght@400;700&family=Tinos:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Times New Roman', Times, 'Tinos', 'Bookman Old Style', serif;
            color: #000;
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
<body class="bg-slate-100 p-4 md:p-8 antialiased">

    <!-- Action Toolbar (No Print) -->
    <div class="max-w-[210mm] mx-auto mb-4 no-print flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl shadow-xs border border-slate-200">
        <div class="flex items-center gap-2">
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('admin.birth.show', $birth) }}" class="font-sans-ui text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Detail Pengajuan
                </a>
            @else
                <a href="{{ url()->previous() ?: route('submissions.index') }}" class="font-sans-ui text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="font-sans-ui bg-[#059cb8] hover:bg-[#095b8c] text-white font-bold text-xs px-4 py-2 rounded-lg shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i class="fa-solid fa-print"></i> Cetak Berkas Lengkap & Surat (A4)
            </button>
        </div>
    </div>

    @php
        // Helper formatting
        $childDob = $birth->birth_date ? \Carbon\Carbon::parse($birth->birth_date) : null;
        $childDobFormatted = $childDob ? ($birth->birth_day ? strtoupper($birth->birth_day) . ' / ' : '') . $childDob->translatedFormat('d F Y') : '-';
        $reportDate = $birth->created_at ? $birth->created_at->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y');
        $genderText = $birth->gender === 'L' ? 'LAKI-LAKI' : ($birth->gender === 'P' ? 'PEREMPUAN' : '-');
        
        $applicantName = $birth->applicant_name ?: ($birth->father_name ?: ($birth->mother_name ?: '-'));
        $applicantNik = $birth->applicant_nik ?: ($birth->father_nik ?: ($birth->mother_nik ?: '-'));
        $applicantJob = $birth->applicant_job ?: ($birth->father_job ?: ($birth->mother_job ?: '-'));
        
        $applicantFullAddress = '';
        if ($birth->padukuhan || $birth->rt) {
            $applicantFullAddress = ($birth->padukuhan ? 'Padukuhan ' . $birth->padukuhan . ', ' : '') . ($birth->rt ? 'RT ' . $birth->rt . ' / RW ' . $birth->rw : '');
        } elseif ($birth->address) {
            $applicantFullAddress = $birth->address;
        } else {
            $applicantFullAddress = '-';
        }

        // Format Umur Pelapor & Saksi
        $applicantAge = $birth->applicant_age;
        if (!$applicantAge && $birth->applicant_birth_date) {
            $applicantAge = \Carbon\Carbon::parse($birth->applicant_birth_date)->age;
        }

        $w1Age = $birth->witness_1_age;
        if (!$w1Age && $birth->witness_1_birth_date) {
            $w1Age = \Carbon\Carbon::parse($birth->witness_1_birth_date)->age;
        }

        $w2Age = $birth->witness_2_age;
        if (!$w2Age && $birth->witness_2_birth_date) {
            $w2Age = \Carbon\Carbon::parse($birth->witness_2_birth_date)->age;
        }
    @endphp

    <!-- =========================================================================
         HALAMAN 1: PERMOHONAN AKTA KELAHIRAN & DOKUMEN PERSYARATAN (BERKAS WARGA)
    ========================================================================== -->
    <div class="print-page text-black text-[9pt]">
        
        <div class="flex justify-between items-start mb-2">
            <div class="text-[8.5pt]">
                <table class="form-table-nested">
                    <tr>
                        <td class="w-10">No.</td>
                        <td class="w-2">:</td>
                        <td class="font-bold underline">{{ $birth->registration_no }}</td>
                    </tr>
                </table>
            </div>
            <div class="text-right text-[8pt] text-slate-500">
                Formulir Permohonan Warga - Halaman 1
            </div>
        </div>

        <div class="text-center font-bold text-[11pt] tracking-wide mb-3">
            PERMOHONAN AKTA KELAHIRAN
        </div>

        <table class="form-table mb-3">
            <!-- 1. DATA KELUARGA -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">Data Keluarga</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">1</td>
                <td class="w-52">Nomor Kartu Keluarga (KK)</td>
                <td>: <span class="font-bold">{{ $birth->family_card_no ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">2</td>
                <td>Nama Kepala Keluarga</td>
                <td>: <span class="font-bold uppercase">{{ $birth->father_name ?: ($birth->mother_name ?: $applicantName) }}</span></td>
            </tr>

            <!-- 2. DATA ANAK -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">Data Anak</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">1</td>
                <td>NIK Anak (bila ada)</td>
                <td>: {{ $birth->child_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">2</td>
                <td>Nama Lengkap Anak</td>
                <td>: <span class="font-bold uppercase">{{ $birth->child_name }}</span></td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">3</td>
                <td>Tempat dan Tanggal Lahir</td>
                <td>: {{ strtoupper($birth->birth_place ?: 'SLEMAN') }}, {{ $childDob ? $childDob->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">4</td>
                <td>Anak ke / Jenis Kelamin</td>
                <td>: Anak ke : <strong>{{ $birth->birth_order ?: '1' }}</strong> &nbsp;&nbsp;|&nbsp;&nbsp; Jenis Kelamin : <strong>{{ $genderText }}</strong></td>
            </tr>

            <!-- 3. DATA IBU KANDUNG -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">Data Ibu Kandung</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">1</td>
                <td>NIK</td>
                <td>: {{ $birth->mother_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">2</td>
                <td>Nama Lengkap</td>
                <td>: <span class="font-bold uppercase">{{ $birth->mother_name ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">3</td>
                <td>Alamat</td>
                <td>: {{ $birth->mother_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">4</td>
                <td>Kawin Sah</td>
                <td>
                    : Tanggal : {{ $birth->marriage_date ? \Carbon\Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-' }}
                </td>
            </tr>

            <!-- 4. DATA AYAH KANDUNG -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">Data Ayah Kandung</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">1</td>
                <td>NIK</td>
                <td>: {{ $birth->father_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">2</td>
                <td>Nama Lengkap</td>
                <td>: <span class="font-bold uppercase">{{ $birth->father_name ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">3</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->father_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>

            <!-- 5. DATA PELAPOR -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">Data Pelapor</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">1</td>
                <td>NIK</td>
                <td>: {{ $birth->applicant_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">2</td>
                <td>Nama Lengkap</td>
                <td>: <span class="font-bold uppercase">{{ $birth->applicant_name ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">3</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->applicant_address ?: $applicantFullAddress }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">4</td>
                <td>Nomor HP/Telepon</td>
                <td>: {{ $birth->applicant_phone ?: '-' }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">5</td>
                <td>Tanggal Lapor</td>
                <td>: {{ $reportDate }}</td>
            </tr>
            <tr>
                <td class="w-8 text-center font-semibold">6</td>
                <td>Hubungan dengan Bayi</td>
                <td>: {{ $birth->applicant_relation ? ucfirst($birth->applicant_relation) : 'Orang Tua / Pemohon' }}</td>
            </tr>
        </table>

        <!-- DOKUMEN PERSYARATAN -->
        <table class="form-table mb-4">
            <tr class="bg-slate-100 font-bold">
                <td colspan="2" class="uppercase">Dokumen Persyaratan (Diperiksa oleh Petugas)</td>
                <td class="w-20 text-center uppercase">Status</td>
            </tr>
            @php
                $checkDocs = [
                    ['Surat Keterangan Lahir dari dokter/ bidan/ penolong kelahiran', !empty($birth->doc_birth_cert)],
                    ['Surat Keterangan Kelahiran dari Kalurahan', true],
                    ['Fotokopi buku nikah/ kutipan akta perkawinan orangtua (dilegalisir)', !empty($birth->doc_marriage_cert)],
                    ['Fotokopi Kartu Keluarga (KK) orangtua/wali', !empty($birth->doc_family_card)],
                    ['Fotokopi KTP-el orangtua/wali/pelapor', !empty($birth->doc_parents_ktp)],
                    ['Fotokopi KTP-el 2 (Dua) orang saksi', !empty($birth->doc_witness_ktp)],
                    ['SPTJM Kebenaran Data Kelahiran', false],
                    ['SPTJM Kebenaran Sebagai Pasangan Suami Istri', false],
                ];
            @endphp
            @foreach($checkDocs as $d)
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
                <p>SLEMAN, {{ $reportDate }}</p>
                <p class="font-semibold">Petugas Penerima Berkas,</p>
                <div class="h-16"></div>
                <p class="font-medium underline">( .................................................... )</p>
            </div>
            <div class="text-center w-64">
                <p>SLEMAN, {{ $reportDate }}</p>
                <p class="font-semibold">Pemohon / Pelapor,</p>
                <div class="h-16">
                    @if($birth->applicant_signature)
                        <img src="{{ asset('storage/' . $birth->applicant_signature) }}" alt="Tanda Tangan" class="h-16 mx-auto object-contain">
                    @endif
                </div>
                <p class="font-bold underline uppercase">( {{ $birth->applicant_name ?: $applicantName }} )</p>
            </div>
        </div>

    </div>

    <!-- =========================================================================
         HALAMAN 2: FORMULIR PELAPORAN KELAHIRAN (KODE F-2.02)
    ========================================================================== -->
    <div class="print-page page-break text-black text-[8.5pt]">
        
        <div class="flex justify-between items-start mb-1">
            <div class="border border-black px-2 py-0.5 font-bold text-[8.5pt]">
                Kode F-2.02
            </div>
            <div class="text-right text-[8pt] text-slate-500">
                Formulir Pelaporan Kelahiran - Halaman 2
            </div>
        </div>

        <div class="text-center mb-2">
            <h3 class="font-bold text-[10.5pt] uppercase tracking-wide">FORMULIR PELAPORAN KELAHIRAN</h3>
            <p class="text-[8.5pt] italic">(Untuk Mendapatkan Akta Kelahiran)</p>
            <h4 class="font-bold text-[9.5pt] uppercase">PEMERINTAH KABUPATEN SLEMAN</h4>
        </div>

        <table class="form-table mb-2">
            <!-- DATA KELUARGA -->
            <tr>
                <td class="w-8 text-center font-bold">1</td>
                <td class="w-48 font-medium">Nomor Kartu Keluarga (KK)</td>
                <td>: <span class="font-bold">{{ $birth->family_card_no ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="w-8 text-center font-bold">2</td>
                <td class="font-medium">Nama Kepala Keluarga</td>
                <td>: <span class="font-bold uppercase">{{ $birth->father_name ?: ($birth->mother_name ?: $applicantName) }}</span></td>
            </tr>

            <!-- BAYI / ANAK -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">BAYI / ANAK</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">1</td>
                <td>NIK</td>
                <td>: {{ $birth->child_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">2</td>
                <td>Nama Lengkap</td>
                <td>: <span class="font-bold uppercase">{{ $birth->child_name }}</span></td>
            </tr>
            <tr>
                <td class="text-center font-semibold">3</td>
                <td>Jenis Kelamin</td>
                <td>: <strong>{{ $genderText }}</strong></td>
            </tr>
            <tr>
                <td class="text-center font-semibold">4</td>
                <td>Tempat Dilahirkan</td>
                <td>: {{ strtoupper($birth->birth_place ?: 'SLEMAN') }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">5</td>
                <td>Tempat Kelahiran (Kategori)</td>
                <td>
                    : <strong>{{ $birth->birth_place_type ? strtoupper($birth->birth_place_type) : 'RUMAH / RS' }}</strong>
                    <span class="text-slate-500 text-[7.5pt] ml-2">(1. RS/RB &nbsp; 2. Puskesmas &nbsp; 3. Polindes &nbsp; 4. Rumah &nbsp; 5. Lainnya)</span>
                </td>
            </tr>
            <tr>
                <td class="text-center font-semibold">6</td>
                <td>Hari / Tgl, Bln, Thn Lahir</td>
                <td>: <span class="font-bold">{{ $childDobFormatted }}</span></td>
            </tr>
            <tr>
                <td class="text-center font-semibold">7</td>
                <td>Waktu / Jam Kelahiran</td>
                <td>: {{ $birth->birth_time ?: '-' }} {{ $birth->birth_time ? 'WIB' : '' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">8</td>
                <td>Jenis Kelahiran</td>
                <td>
                    : <strong>{{ $birth->birth_type ? strtoupper($birth->birth_type) : 'TUNGGAL' }}</strong>
                    <span class="text-slate-500 text-[7.5pt] ml-2">(1. Tunggal &nbsp; 2. Kembar 2 &nbsp; 3. Kembar 3 &nbsp; 4. Kembar 4)</span>
                </td>
            </tr>
            <tr>
                <td class="text-center font-semibold">9</td>
                <td>Kelahiran Ke</td>
                <td>: <strong>{{ $birth->birth_order ?: '1' }}</strong></td>
            </tr>
            <tr>
                <td class="text-center font-semibold">10</td>
                <td>Penolong Kelahiran</td>
                <td>
                    : <strong>{{ $birth->birth_helper ? strtoupper($birth->birth_helper) : 'DOKTER / BIDAN' }}</strong>
                    <span class="text-slate-500 text-[7.5pt] ml-2">(1. Dokter &nbsp; 2. Bidan/Perawat &nbsp; 3. Dukun &nbsp; 4. Lainnya)</span>
                </td>
            </tr>
            <tr>
                <td class="text-center font-semibold">11</td>
                <td>Berat & Panjang Bayi</td>
                <td>: Berat: <strong>{{ $birth->weight_gram ?: ($birth->weight_kg ? $birth->weight_kg * 1000 : '-') }}</strong> gram &nbsp;|&nbsp; Panjang: <strong>{{ $birth->length_cm ?: '-' }}</strong> cm</td>
            </tr>

            <!-- IBU -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">IBU KANDUNG</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">1</td>
                <td>NIK</td>
                <td>: {{ $birth->mother_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">2</td>
                <td>Nama Lengkap</td>
                <td>: <span class="font-bold uppercase">{{ $birth->mother_name ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="text-center font-semibold">3</td>
                <td>Tempat & Tanggal Lahir / Umur</td>
                <td>
                    : {{ $birth->mother_birth_date ? \Carbon\Carbon::parse($birth->mother_birth_date)->translatedFormat('d F Y') : '-' }}
                    @if($birth->mother_age)
                        &nbsp;&bull;&nbsp; Umur: {{ $birth->mother_age }} tahun
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-center font-semibold">4</td>
                <td>Pekerjaan</td>
                <td>: {{ $birth->mother_job ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">5</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->mother_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">6</td>
                <td>Kewarganegaraan</td>
                <td>: {{ $birth->mother_citizenship ?: 'WNI' }}</td>
            </tr>

            <!-- AYAH -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">AYAH KANDUNG</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">1</td>
                <td>NIK</td>
                <td>: {{ $birth->father_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">2</td>
                <td>Nama Lengkap</td>
                <td>: <span class="font-bold uppercase">{{ $birth->father_name ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="text-center font-semibold">3</td>
                <td>Tempat & Tanggal Lahir / Umur</td>
                <td>
                    : {{ $birth->father_birth_date ? \Carbon\Carbon::parse($birth->father_birth_date)->translatedFormat('d F Y') : '-' }}
                    @if($birth->father_age)
                        &nbsp;&bull;&nbsp; Umur: {{ $birth->father_age }} tahun
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-center font-semibold">4</td>
                <td>Pekerjaan</td>
                <td>: {{ $birth->father_job ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">5</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->father_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">6</td>
                <td>Kewarganegaraan</td>
                <td>: {{ $birth->father_citizenship ?: 'WNI' }}</td>
            </tr>

            <!-- PELAPOR -->
            <tr class="bg-slate-100 font-bold">
                <td colspan="3" class="uppercase">PELAPOR</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">1</td>
                <td>NIK</td>
                <td>: {{ $birth->applicant_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">2</td>
                <td>Nama Lengkap</td>
                <td>: <span class="font-bold uppercase">{{ $birth->applicant_name ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="text-center font-semibold">3</td>
                <td>Umur / Pekerjaan</td>
                <td>: {{ $applicantAge ? $applicantAge . ' Tahun' : '-' }} &nbsp;|&nbsp; {{ $birth->applicant_job ?: '-' }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">4</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->applicant_address ?: $applicantFullAddress }}</td>
            </tr>
            <tr>
                <td class="text-center font-semibold">5</td>
                <td>Tanggal Lapor</td>
                <td>: {{ $reportDate }}</td>
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
                        <tr><td class="w-20 font-medium">NIK</td><td class="w-2">:</td><td>{{ $birth->witness_1_nik ?: '-' }}</td></tr>
                        <tr><td class="font-medium">Nama Lengkap</td><td>:</td><td class="font-bold uppercase">{{ $birth->witness_1_name ?: '-' }}</td></tr>
                        <tr><td class="font-medium">Umur</td><td>:</td><td>{{ $w1Age ? $w1Age . ' Tahun' : '-' }}</td></tr>
                        <tr><td class="font-medium">Pekerjaan</td><td>:</td><td>{{ $birth->witness_1_job ?: '-' }}</td></tr>
                        <tr><td class="font-medium">Alamat</td><td>:</td><td>{{ $birth->witness_1_address ?: 'Purwobinangun, Pakem, Sleman' }}</td></tr>
                    </table>
                </td>
                <td>
                    <table class="form-table-nested">
                        <tr><td class="w-20 font-medium">NIK</td><td class="w-2">:</td><td>{{ $birth->witness_2_nik ?: '-' }}</td></tr>
                        <tr><td class="font-medium">Nama Lengkap</td><td>:</td><td class="font-bold uppercase">{{ $birth->witness_2_name ?: '-' }}</td></tr>
                        <tr><td class="font-medium">Umur</td><td>:</td><td>{{ $w2Age ? $w2Age . ' Tahun' : '-' }}</td></tr>
                        <tr><td class="font-medium">Pekerjaan</td><td>:</td><td>{{ $birth->witness_2_job ?: '-' }}</td></tr>
                        <tr><td class="font-medium">Alamat</td><td>:</td><td>{{ $birth->witness_2_address ?: 'Purwobinangun, Pakem, Sleman' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- DATA ADMINISTRASI DESA -->
        <div class="border border-black p-2 mb-2 text-[8pt] flex justify-between items-center">
            <div>
                <strong>DATA ADMINISTRASI:</strong><br>
                Jenis Pelaporan: <strong>Permohonan Akta Kelahiran Baru</strong><br>
                Nama Petugas Register: <strong>{{ $birth->processor ? $birth->processor->name : (auth()->check() ? auth()->user()->name : 'Petugas Registrasi') }}</strong>
            </div>
            <div class="text-center pr-6">
                Paraf Petugas:<br>
                <div class="h-8"></div>
                ( .................... )
            </div>
        </div>

    </div>

    <!-- =========================================================================
         HALAMAN 3 (PALING BELAKANG): SURAT KETERANGAN RESMI KALURAHAN PURWOBINANGUN
    ========================================================================== -->
    <div class="print-page page-break text-black text-[11pt] leading-[1.65]">
        
        <!-- Kop Surat Resmi -->
        <div class="relative pb-1">
            <!-- Logo Sleman -->
            <div class="absolute left-0 top-1">
                <img src="{{ asset('images/logo-sleman.png') }}" alt="Logo Sleman" class="w-20 h-24 object-contain">
            </div>

            <!-- Teks Kop Surat -->
            <div class="text-center pl-16 pr-2">
                <h4 class="text-[14pt] font-bold tracking-wide uppercase leading-tight">
                    PEMERINTAH KABUPATEN SLEMAN
                </h4>
                <h4 class="text-[14pt] font-bold tracking-wide uppercase leading-tight mt-0.5">
                    KAPANEWON PAKEM
                </h4>
                <h3 class="text-[16pt] font-extrabold tracking-wide uppercase leading-tight mt-0.5">
                    PEMERINTAH KALURAHAN PURWOBINANGUN
                </h3>
                <div class="font-javanese text-[12pt] leading-tight my-0.5">
                    ꦥꦩꦫꦺꦤ꧀ꦠꦃꦏꦭꦸꦫꦲꦤ꧀ꦥꦸꦂꦮꦧꦶꦤꦔꦸꦤ꧀
                </div>
                <p class="text-[9pt] leading-tight">
                    Alamat : Jl. Pakem-Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582
                </p>
                <p class="text-[9pt] leading-tight mt-0.5">
                    Laman : www.purwobinangun.slemankab.go.id Surel : puwobinangun@slemankab.go.id
                </p>
            </div>

            <!-- Garis Double Pembatas Kop -->
            <div class="mt-2.5" style="border-top: 2.5px solid #000; border-bottom: 1px solid #000; height: 3px;"></div>
        </div>

        <!-- Judul Surat -->
        <div class="text-center my-5">
            <h2 class="text-[13pt] font-bold tracking-wider uppercase underline">
                SURAT KETERANGAN
            </h2>
            <p class="text-[11pt] mt-1 tracking-wide">
                Nomor : 474.1/ &nbsp;&nbsp;{{ substr($birth->registration_no, -4) }}&nbsp;&nbsp; / &nbsp;&nbsp;{{ $birth->created_at ? $birth->created_at->format('m') : date('m') }}&nbsp;&nbsp; / &nbsp;&nbsp;{{ $birth->created_at ? $birth->created_at->format('Y') : date('Y') }}
            </p>
        </div>

        <!-- Isi Surat -->
        <div class="text-[11pt] leading-[1.65] space-y-4 text-black">
            
            <!-- Yang bertandatangan di bawah ini -->
            <div>
                <table class="w-full">
                    <tr>
                        <td class="w-64 align-top">Yang bertandatangan di bawah ini</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top"></td>
                    </tr>
                </table>
                <table class="w-full ml-10 mt-1">
                    <tr>
                        <td class="w-36 align-top">Nama</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top font-medium">...................................................</td>
                    </tr>
                    <tr>
                        <td class="w-36 align-top">Jabatan</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top font-medium">...................................................</td>
                    </tr>
                </table>
            </div>

            <!-- Dengan ini menerangkan bahwa -->
            <div>
                <table class="w-full">
                    <tr>
                        <td class="w-64 align-top">Dengan ini menerangkan bahwa</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top"></td>
                    </tr>
                </table>
                <table class="w-full ml-10 mt-1">
                    <tr>
                        <td class="w-36 align-top">Nama Lengkap</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top font-bold uppercase">{{ $applicantName }}</td>
                    </tr>
                    <tr>
                        <td class="w-36 align-top">NIK</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top">{{ $applicantNik }}</td>
                    </tr>
                    <tr>
                        <td class="w-36 align-top">Tanggal Lahir</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top">
                            @if($birth->applicant_birth_date)
                                {{ \Carbon\Carbon::parse($birth->applicant_birth_date)->translatedFormat('d F Y') }}
                            @elseif($birth->father_birth_date)
                                {{ \Carbon\Carbon::parse($birth->father_birth_date)->translatedFormat('d F Y') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="w-36 align-top">Pekerjaan</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top">{{ $applicantJob }}</td>
                    </tr>
                    <tr>
                        <td class="w-36 align-top">Alamat</td>
                        <td class="w-4 align-top">:</td>
                        <td class="align-top leading-snug">
                            @if($birth->padukuhan || $birth->rt)
                                {{ $birth->padukuhan ? 'Padukuhan ' . $birth->padukuhan . ', ' : '' }}{{ $birth->rt ? 'RT ' . $birth->rt . ' / RW ' . $birth->rw : '' }}<br>
                            @elseif($birth->address)
                                {{ $birth->address }}<br>
                            @endif
                            Kalurahan Purwobinangun<br>
                            Kapanewon Pakem<br>
                            Kabupaten Sleman<br>
                            Provinsi Di Yogyakarta
                        </td>
                    </tr>
                    <tr>
                        <td class="w-36 align-top pt-2">Pergi ke</td>
                        <td class="w-4 align-top pt-2">:</td>
                        <td class="align-top pt-2 leading-snug">
                            Dinas Kependudukan dan Catatan Sipil<br>
                            Sleman
                        </td>
                    </tr>
                    <tr>
                        <td class="w-36 align-top pt-2">Kaperluan</td>
                        <td class="w-4 align-top pt-2">:</td>
                        <td class="align-top pt-2">
                            Mohon Akta Kelahiran untuk {{ strtoupper($birth->child_name) }}
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Paragraf Penutup -->
            <div class="pt-2 space-y-3 text-justify" style="text-indent: 2.5rem;">
                <p>
                    Sehubungan dengan maksud yang bersangkutan, mohon yang berwenang memberikan bantuan serta fasilitas seperlunya.
                </p>
                <p>
                    Demikian surat keterangan ini dibuat dengan sebenarnya, untuk dipergunakan sebagaimana mestinya.
                </p>
            </div>

            <!-- Tanda Tangan Pengesahan -->
            <div class="pt-6 flex justify-end">
                <div class="text-left w-72 pr-2">
                    <p>
                        Purwobinangun, {{ $reportDate }}
                    </p>
                    
                    <!-- Ruang tanda tangan & cap stempel -->
                    <div class="h-24"></div>

                    <p class="font-medium">
                        ( .................................................... )
                    </p>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
