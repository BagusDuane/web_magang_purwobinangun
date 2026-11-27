<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan & Formulir Pelaporan Kelahiran - {{ $birth->registration_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 14mm 8mm 14mm;
        }
        body {
            font-family: 'Times-Roman', 'Times New Roman', Times, serif;
            font-size: 8.2pt;
            color: #000000;
            line-height: 1.22;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        tr {
            page-break-inside: avoid;
        }
        .page-break {
            page-break-before: always;
        }
        .avoid-break {
            page-break-inside: avoid;
        }
        .bold {
            font-weight: bold;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }

        /* Form Table Styling */
        .form-table {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 5px;
        }
        .form-table td, .form-table th {
            border: 1px solid #000;
            padding: 2px 4.5px;
            vertical-align: top;
            font-size: 8pt;
        }
        .section-header {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .nested-table {
            width: 100%;
            border: none;
        }
        .nested-table td {
            border: none;
            padding: 1px 0;
            vertical-align: top;
            font-size: 7.8pt;
        }

        .checkbox-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        /* Page 3: Surat Keterangan Styling */
        .sk-page {
            font-family: 'Bookman Old Style', Georgia, 'Times-Roman', serif;
            font-size: 10.5pt;
            line-height: 1.45;
        }
        .sk-kop-table {
            width: 100%;
        }
        .sk-kop-logo {
            width: 18%;
            vertical-align: middle;
            text-align: center;
        }
        .sk-kop-logo img {
            width: 66px;
            height: auto;
        }
        .sk-kop-text {
            width: 82%;
            text-align: center;
            vertical-align: middle;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.18;
        }
        .sk-kop-text h4 {
            margin: 0;
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sk-kop-text h3 {
            margin: 1px 0;
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sk-kop-text h2 {
            margin: 1px 0;
            font-size: 13.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .sk-kop-text p {
            margin: 1px 0 0 0;
            font-size: 8pt;
        }
        .sk-double-line {
            margin-top: 4px;
            border-top: 1px solid #000;
            border-bottom: 2.2px solid #000;
            height: 2px;
            margin-bottom: 12px;
        }
        .sk-title {
            text-align: center;
            margin: 10px 0 16px 0;
        }
        .sk-title h1 {
            font-size: 15pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .sk-title p {
            font-size: 10.5pt;
            margin: 4px 0 0 0;
        }
        .sk-indent-table {
            width: 88%;
            margin-left: 50px;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .sk-indent-table td {
            padding: 1.8px 0;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .sk-paragraph {
            text-align: justify;
            text-indent: 34px;
            margin: 10px 0 8px 14px;
            font-size: 10.5pt;
            line-height: 1.45;
        }
    </style>
</head>
<body>

    @php
        $romans = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $refDate = $birth->created_at ?: now();
        $monthRoman = $romans[(int) $refDate->format('n')] ?? 'I';
        $letterYear = $refDate->format('Y');
        $letterSeq = (int) substr($birth->registration_no, -4) ?: 1;

        $childDob = $birth->birth_date ? \Carbon\Carbon::parse($birth->birth_date) : null;
        $childDobFormatted = $childDob ? ($birth->birth_day ? strtoupper($birth->birth_day) . ' / ' : '') . $childDob->translatedFormat('d F Y') : '-';
        $reportDateFormatted = $refDate->translatedFormat('d F Y');
        $genderText = $birth->gender === 'L' ? 'LAKI-LAKI' : ($birth->gender === 'P' ? 'PEREMPUAN' : '-');

        $applicantName = $birth->applicant_name ?: ($birth->father_name ?: ($birth->mother_name ?: '-'));
        $applicantNik = $birth->applicant_nik ?: ($birth->father_nik ?: ($birth->mother_nik ?: '-'));
        $applicantJob = $birth->applicant_job ?: ($birth->father_job ?: ($birth->mother_job ?: '-'));

        $applicantDobObj = $birth->applicant_birth_date ?: ($birth->father_birth_date ?: $birth->mother_birth_date);
        $applicantDobFormatted = $applicantDobObj ? \Carbon\Carbon::parse($applicantDobObj)->translatedFormat('d F Y') : '-';

        $applicantFullAddress = '';
        if ($birth->padukuhan || $birth->rt) {
            $applicantFullAddress = ($birth->padukuhan ? 'Padukuhan ' . $birth->padukuhan . ', ' : '') . ($birth->rt ? 'RT ' . $birth->rt . ' / RW ' . $birth->rw : '');
        } elseif ($birth->address) {
            $applicantFullAddress = $birth->address;
        } else {
            $applicantFullAddress = '-';
        }

        $applicantStreetParts = [];
        if ($birth->padukuhan) {
            $applicantStreetParts[] = $birth->padukuhan;
        } elseif ($birth->address) {
            $applicantStreetParts[] = $birth->address;
        }
        if ($birth->rt || $birth->rw) {
            $applicantStreetParts[] = 'RT ' . str_pad((string) ($birth->rt ?: '0'), 3, '0', STR_PAD_LEFT) . ' RW ' . str_pad((string) ($birth->rw ?: '0'), 3, '0', STR_PAD_LEFT);
        }
        $applicantStreetLine = !empty($applicantStreetParts) ? implode(' ', $applicantStreetParts) : ($birth->father_address ?: ($birth->mother_address ?: '-'));

        $applicantAge = $birth->applicant_age ?: ($birth->applicant_birth_date ? \Carbon\Carbon::parse($birth->applicant_birth_date)->age : null);
        $w1Age = $birth->witness_1_age ?: ($birth->witness_1_birth_date ? \Carbon\Carbon::parse($birth->witness_1_birth_date)->age : null);
        $w2Age = $birth->witness_2_age ?: ($birth->witness_2_birth_date ? \Carbon\Carbon::parse($birth->witness_2_birth_date)->age : null);

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

    <!-- =========================================================================
         HALAMAN 1: PERMOHONAN AKTA KELAHIRAN & DOKUMEN PERSYARATAN (BERKAS WARGA)
    ========================================================================== -->
    <div>
        <table style="margin-bottom: 4px;">
            <tr>
                <td style="width: 50%; font-size: 8pt;">
                    <strong>No. :</strong> <u>{{ $birth->registration_no }}</u>
                </td>
                <td style="width: 50%; text-align: right; font-size: 7.5pt; color: #475569;">
                    Formulir Permohonan Warga - Halaman 1
                </td>
            </tr>
        </table>

        <div style="text-align: center; font-weight: bold; font-size: 11pt; letter-spacing: 0.3px; margin-bottom: 6px;">
            PERMOHONAN AKTA KELAHIRAN
        </div>

        <table class="form-table">
            <!-- 1. DATA KELUARGA -->
            <tr class="section-header">
                <td colspan="3">DATA KELUARGA</td>
            </tr>
            <tr>
                <td style="width: 5%; text-align: center;">1</td>
                <td style="width: 35%;">Nomor Kartu Keluarga (KK)</td>
                <td style="width: 60%; font-weight: bold;">: {{ $birth->family_card_no ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Kepala Keluarga</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->father_name ?: ($birth->mother_name ?: $applicantName) }}</td>
            </tr>

            <!-- 2. DATA ANAK -->
            <tr class="section-header">
                <td colspan="3">DATA ANAK</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK Anak (bila ada)</td>
                <td>: {{ $birth->child_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap Anak</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->child_name }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Tempat dan Tanggal Lahir</td>
                <td class="uppercase">: {{ $birth->birth_place ?: 'SLEMAN' }}, {{ $childDob ? $childDob->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Anak ke / Jenis Kelamin</td>
                <td>: Anak ke : <strong>{{ $birth->birth_order ?: '1' }}</strong> &nbsp;|&nbsp; Jenis Kelamin : <strong>{{ $genderText }}</strong></td>
            </tr>

            <!-- 3. DATA IBU KANDUNG -->
            <tr class="section-header">
                <td colspan="3">DATA IBU KANDUNG</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK</td>
                <td>: {{ $birth->mother_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->mother_name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Alamat</td>
                <td>: {{ $birth->mother_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Kawin Sah</td>
                <td>: Tanggal : {{ $birth->marriage_date ? \Carbon\Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-' }}</td>
            </tr>

            <!-- 4. DATA AYAH KANDUNG -->
            <tr class="section-header">
                <td colspan="3">DATA AYAH KANDUNG</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK</td>
                <td>: {{ $birth->father_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->father_name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->father_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>

            <!-- 5. DATA PELAPOR -->
            <tr class="section-header">
                <td colspan="3">DATA PELAPOR</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK</td>
                <td>: {{ $birth->applicant_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->applicant_name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->applicant_address ?: $applicantFullAddress }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Nomor HP/Telepon</td>
                <td>: {{ $birth->applicant_phone ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td>Tanggal Lapor</td>
                <td>: {{ $reportDateFormatted }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">6</td>
                <td>Hubungan dengan Bayi</td>
                <td>: {{ $birth->applicant_relation ? ucfirst($birth->applicant_relation) : 'Orang Tua / Pemohon' }}</td>
            </tr>
        </table>

        <!-- DOKUMEN PERSYARATAN -->
        <table class="form-table" style="margin-top: 4px;">
            <tr class="section-header">
                <td colspan="2">DOKUMEN PERSYARATAN (DIPERIKSA OLEH PETUGAS)</td>
                <td style="width: 20%; text-align: center;">STATUS</td>
            </tr>
            @foreach($checkDocs as $d)
                <tr>
                    <td style="width: 5%; text-align: center;">
                        <span class="checkbox-box">{{ $d[1] ? 'v' : '' }}</span>
                    </td>
                    <td style="width: 75%;">{{ $d[0] }}</td>
                    <td style="width: 20%; text-align: center; font-weight: bold; color: {{ $d[1] ? '#065f46' : '#94a3b8' }};">
                        {{ $d[1] ? 'Ada / Terlampir' : '-' }}
                    </td>
                </tr>
            @endforeach
        </table>

        <!-- Tanda Tangan Form Permohonan -->
        <table style="width: 100%; margin-top: 10px;" class="avoid-break">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top; font-size: 8.2pt;">
                    SLEMAN, {{ $reportDateFormatted }}<br>
                    <strong>Petugas Penerima Berkas,</strong>
                    <div style="height: 38px;"></div>
                    <u>( .................................................... )</u>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top; font-size: 8.2pt;">
                    SLEMAN, {{ $reportDateFormatted }}<br>
                    <strong>Pemohon / Pelapor,</strong>
                    <div style="height: 38px;">
                        @if(!empty($signatureBase64))
                            <img src="{{ $signatureBase64 }}" alt="Tanda Tangan" style="height: 36px; width: auto;">
                        @endif
                    </div>
                    <u><strong class="uppercase">( {{ $birth->applicant_name ?: $applicantName }} )</strong></u>
                </td>
            </tr>
        </table>
    </div>

    <!-- =========================================================================
         HALAMAN 2: FORMULIR PELAPORAN KELAHIRAN (KODE F-2.02)
    ========================================================================== -->
    <div class="page-break">
        <table style="margin-bottom: 3px;">
            <tr>
                <td style="width: 50%; font-size: 8pt;">
                    <span style="border: 1px solid #000; padding: 1px 5px; font-weight: bold;">Kode F-2.02</span>
                </td>
                <td style="width: 50%; text-align: right; font-size: 7.5pt; color: #475569;">
                    Formulir Pelaporan Kelahiran - Halaman 2
                </td>
            </tr>
        </table>

        <div style="text-align: center; margin-bottom: 5px;">
            <div style="font-weight: bold; font-size: 10pt; text-transform: uppercase;">FORMULIR PELAPORAN KELAHIRAN</div>
            <div style="font-size: 8pt; font-style: italic;">(Untuk Mendapatkan Akta Kelahiran)</div>
            <div style="font-weight: bold; font-size: 9pt; text-transform: uppercase;">PEMERINTAH KABUPATEN SLEMAN</div>
        </div>

        <table class="form-table" style="margin-bottom: 4px;">
            <!-- DATA KELUARGA -->
            <tr>
                <td style="width: 5%; text-align: center; font-weight: bold;">1</td>
                <td style="width: 35%;">Nomor Kartu Keluarga (KK)</td>
                <td style="width: 60%; font-weight: bold;">: {{ $birth->family_card_no ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center; font-weight: bold;">2</td>
                <td>Nama Kepala Keluarga</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->father_name ?: ($birth->mother_name ?: $applicantName) }}</td>
            </tr>

            <!-- BAYI / ANAK -->
            <tr class="section-header">
                <td colspan="3">BAYI / ANAK</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK</td>
                <td>: {{ $birth->child_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->child_name }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Jenis Kelamin</td>
                <td>: <strong>{{ $genderText }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Tempat Dilahirkan</td>
                <td class="uppercase">: {{ $birth->birth_place ?: 'SLEMAN' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td>Tempat Kelahiran (Kategori)</td>
                <td>
                    : <strong>{{ $birth->birth_place_type ? strtoupper($birth->birth_place_type) : 'RUMAH / RS' }}</strong>
                    <span style="font-size: 7pt; color: #475569; margin-left: 4px;">(1. RS/RB  2. Puskesmas  3. Polindes  4. Rumah  5. Lainnya)</span>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">6</td>
                <td>Hari / Tgl, Bln, Thn Lahir</td>
                <td>: <strong>{{ $childDobFormatted }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: center;">7</td>
                <td>Waktu / Jam Kelahiran</td>
                <td>: {{ $birth->birth_time ?: '-' }} {{ $birth->birth_time ? 'WIB' : '' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">8</td>
                <td>Jenis Kelahiran</td>
                <td>
                    : <strong>{{ $birth->birth_type ? strtoupper($birth->birth_type) : 'TUNGGAL' }}</strong>
                    <span style="font-size: 7pt; color: #475569; margin-left: 4px;">(1. Tunggal  2. Kembar 2  3. Kembar 3  4. Kembar 4)</span>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">9</td>
                <td>Kelahiran Ke</td>
                <td>: <strong>{{ $birth->birth_order ?: '1' }}</strong></td>
            </tr>
            <tr>
                <td style="text-align: center;">10</td>
                <td>Penolong Kelahiran</td>
                <td>
                    : <strong>{{ $birth->birth_helper ? strtoupper($birth->birth_helper) : 'DOKTER / BIDAN' }}</strong>
                    <span style="font-size: 7pt; color: #475569; margin-left: 4px;">(1. Dokter  2. Bidan/Perawat  3. Dukun  4. Lainnya)</span>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">11</td>
                <td>Berat & Panjang Bayi</td>
                <td>: Berat: <strong>{{ $birth->weight_gram ?: ($birth->weight_kg ? $birth->weight_kg * 1000 : '-') }}</strong> gram &nbsp;|&nbsp; Panjang: <strong>{{ $birth->length_cm ?: '-' }}</strong> cm</td>
            </tr>

            <!-- IBU -->
            <tr class="section-header">
                <td colspan="3">IBU KANDUNG</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK</td>
                <td>: {{ $birth->mother_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->mother_name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Tempat & Tanggal Lahir / Umur</td>
                <td>
                    : {{ $birth->mother_birth_date ? \Carbon\Carbon::parse($birth->mother_birth_date)->translatedFormat('d F Y') : '-' }}
                    @if($birth->mother_age)
                        &nbsp;&bull;&nbsp; Umur: {{ $birth->mother_age }} tahun
                    @endif
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Pekerjaan</td>
                <td>: {{ $birth->mother_job ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->mother_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">6</td>
                <td>Kewarganegaraan</td>
                <td>: {{ $birth->mother_citizenship ?: 'WNI' }}</td>
            </tr>

            <!-- AYAH -->
            <tr class="section-header">
                <td colspan="3">AYAH KANDUNG</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK</td>
                <td>: {{ $birth->father_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->father_name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Tempat & Tanggal Lahir / Umur</td>
                <td>
                    : {{ $birth->father_birth_date ? \Carbon\Carbon::parse($birth->father_birth_date)->translatedFormat('d F Y') : '-' }}
                    @if($birth->father_age)
                        &nbsp;&bull;&nbsp; Umur: {{ $birth->father_age }} tahun
                    @endif
                </td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Pekerjaan</td>
                <td>: {{ $birth->father_job ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->father_address ?: ($applicantFullAddress ?: '-') }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">6</td>
                <td>Kewarganegaraan</td>
                <td>: {{ $birth->father_citizenship ?: 'WNI' }}</td>
            </tr>

            <!-- PELAPOR -->
            <tr class="section-header">
                <td colspan="3">PELAPOR</td>
            </tr>
            <tr>
                <td style="text-align: center;">1</td>
                <td>NIK</td>
                <td>: {{ $birth->applicant_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td>Nama Lengkap</td>
                <td style="font-weight: bold;" class="uppercase">: {{ $birth->applicant_name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td>Umur / Pekerjaan</td>
                <td>: {{ $applicantAge ? $applicantAge . ' Tahun' : '-' }} &nbsp;|&nbsp; {{ $birth->applicant_job ?: '-' }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td>Alamat (Sesuai KTP)</td>
                <td>: {{ $birth->applicant_address ?: $applicantFullAddress }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td>Tanggal Lapor</td>
                <td>: {{ $reportDateFormatted }}</td>
            </tr>
        </table>

        <!-- SAKSI-SAKSI -->
        <table class="form-table" style="margin-bottom: 4px;">
            <tr class="section-header">
                <td style="width: 50%; text-align: center;">SAKSI I</td>
                <td style="width: 50%; text-align: center;">SAKSI II</td>
            </tr>
            <tr>
                <td>
                    <table class="nested-table">
                        <tr><td style="width: 25%;">NIK</td><td style="width: 3%;">:</td><td>{{ $birth->witness_1_nik ?: '-' }}</td></tr>
                        <tr><td>Nama Lengkap</td><td>:</td><td style="font-weight: bold;" class="uppercase">{{ $birth->witness_1_name ?: '-' }}</td></tr>
                        <tr><td>Umur</td><td>:</td><td>{{ $w1Age ? $w1Age . ' Tahun' : '-' }}</td></tr>
                        <tr><td>Pekerjaan</td><td>:</td><td>{{ $birth->witness_1_job ?: '-' }}</td></tr>
                        <tr><td>Alamat</td><td>:</td><td>{{ $birth->witness_1_address ?: 'Purwobinangun, Pakem, Sleman' }}</td></tr>
                    </table>
                </td>
                <td>
                    <table class="nested-table">
                        <tr><td style="width: 25%;">NIK</td><td style="width: 3%;">:</td><td>{{ $birth->witness_2_nik ?: '-' }}</td></tr>
                        <tr><td>Nama Lengkap</td><td>:</td><td style="font-weight: bold;" class="uppercase">{{ $birth->witness_2_name ?: '-' }}</td></tr>
                        <tr><td>Umur</td><td>:</td><td>{{ $w2Age ? $w2Age . ' Tahun' : '-' }}</td></tr>
                        <tr><td>Pekerjaan</td><td>:</td><td>{{ $birth->witness_2_job ?: '-' }}</td></tr>
                        <tr><td>Alamat</td><td>:</td><td>{{ $birth->witness_2_address ?: 'Purwobinangun, Pakem, Sleman' }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- DATA ADMINISTRASI DESA -->
        <table style="width: 100%; border: 1px solid #000; padding: 2px 5px; font-size: 7.8pt;" class="avoid-break">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <strong>DATA ADMINISTRASI:</strong><br>
                    Jenis Pelaporan: <strong>Permohonan Akta Kelahiran Baru</strong><br>
                    Nama Petugas Register: <strong>{{ $birth->processor ? $birth->processor->name : 'Petugas Registrasi' }}</strong>
                </td>
                <td style="width: 30%; text-align: center; vertical-align: top;">
                    Paraf Petugas:<br>
                    <div style="height: 22px;"></div>
                    ( .................... )
                </td>
            </tr>
        </table>
    </div>

    <!-- =========================================================================
         HALAMAN 3: SURAT KETERANGAN RESMI KALURAHAN PURWOBINANGUN
    ========================================================================== -->
    <div class="page-break sk-page">
        <!-- Kop Surat Resmi -->
        <table class="sk-kop-table">
            <tr>
                @if(!empty($logoBase64))
                    <td class="sk-kop-logo">
                        <img src="{{ $logoBase64 }}" alt="Logo Sleman">
                    </td>
                    <td class="sk-kop-text">
                @else
                    <td class="sk-kop-text" style="width: 100%;">
                @endif
                    <h4>PEMERINTAH KABUPATEN SLEMAN</h4>
                    <h3>KAPANEWON PAKEM</h3>
                    <h2>PEMERINTAH KALURAHAN PURWOBINANGUN</h2>
                    @if(!empty($aksaraBase64))
                        <div style="margin: 2px 0;">
                            <img src="{{ $aksaraBase64 }}" alt="Aksara Jawa" style="height: 14px;">
                        </div>
                    @endif
                    <p>Alamat : Jl. Pakem-Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582</p>
                    <p>Laman : www.purwobinangun.slemankab.go.id &nbsp; Surel : puwobinangun@slemankab.go.id</p>
                </td>
            </tr>
        </table>
        <div class="sk-double-line"></div>

        <!-- Judul Surat Keterangan -->
        <div class="sk-title">
            <h1>SURAT KETERANGAN</h1>
            <p>Nomor : 474.1/ {{ $letterSeq }} / {{ $monthRoman }} / {{ $letterYear }}</p>
        </div>

        <div>
            <!-- Yang bertandatangan di bawah ini -->
            <table style="width: 96%; margin-left: 20px;">
                <tr>
                    <td style="width: 38%;">Yang bertandatangan di bawah ini</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 59%;"></td>
                </tr>
            </table>
            <table class="sk-indent-table">
                <tr>
                    <td style="width: 26%;">Nama</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 71%;">...................................................</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>...................................................</td>
                </tr>
            </table>

            <!-- Dengan ini menerangkan bahwa -->
            <table style="width: 96%; margin-left: 20px; margin-top: 6px;">
                <tr>
                    <td style="width: 38%;">Dengan ini menerangkan bahwa</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 59%;"></td>
                </tr>
            </table>
            <table class="sk-indent-table">
                <tr>
                    <td style="width: 26%;">Nama Lengkap</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 71%;" class="bold uppercase">{{ $applicantName }}</td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $applicantNik }}</td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td>:</td>
                    <td>{{ $applicantDobFormatted }}</td>
                </tr>
                <tr>
                    <td>Pekerjaan</td>
                    <td>:</td>
                    <td>{{ $applicantJob }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>
                        {{ $applicantStreetLine }}<br>
                        Kalurahan {{ $birth->applicant_village ?: 'Purwobinangun' }}<br>
                        Kapanewon {{ $birth->applicant_district ?: 'Pakem' }}<br>
                        Kabupaten {{ $birth->applicant_city ?: 'Sleman' }}<br>
                        Provinsi {{ $birth->applicant_province ?: 'DI Yogyakarta' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 4px;">Pergi ke</td>
                    <td style="padding-top: 4px;">:</td>
                    <td style="padding-top: 4px;">
                        Dinas Kependudukan dan Catatan Sipil<br>
                        Sleman
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 4px;">Keperluan</td>
                    <td style="padding-top: 4px;">:</td>
                    <td style="padding-top: 4px;">
                        Mohon Akta Kelahiran untuk <strong>{{ strtoupper($birth->child_name) }}</strong>
                    </td>
                </tr>
            </table>

            <!-- Paragraf Penutup -->
            <p class="sk-paragraph">
                Sehubungan dengan maksud yang bersangkutan, mohon yang berwenang memberikan bantuan serta fasilitas seperlunya.
            </p>
            <p class="sk-paragraph">
                Demikian surat keterangan ini dibuat dengan sebenarnya, untuk dipergunakan sebagaimana mestinya.
            </p>

            <!-- Tanda Tangan Pengesahan -->
            <table class="avoid-break" style="width: 100%; margin-top: 18px;">
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 50%; text-align: center; font-size: 10.5pt; line-height: 1.45;">
                        <p style="margin: 0;">Purwobinangun, {{ $reportDateFormatted }}</p>
                        <p style="margin: 0;">a.n Lurah Purwobinangun</p>
                        <div style="height: 58px;"></div>
                        <p style="margin: 0;">( .................................................... )</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>