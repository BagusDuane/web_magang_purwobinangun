<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Kematian - {{ $death->registration_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 16mm 14mm 16mm;
        }
        body {
            font-family: 'Times-Roman', 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000000;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        table {
            border-collapse: collapse;
        }
        tr {
            page-break-inside: avoid;
        }
        .avoid-break {
            page-break-inside: avoid;
        }
        .header-table {
            width: 100%;
        }
        .header-logo {
            width: 14%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            width: 58px;
            height: auto;
        }
        .header-text {
            width: 86%;
            text-align: center;
            vertical-align: middle;
            padding-right: 12%;
        }
        .header-text h4 {
            margin: 0;
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h3 {
            margin: 1px 0;
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 1px 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 1px 0 0 0;
            font-size: 8pt;
        }
        .double-line {
            margin-top: 5px;
            border-top: 2.5px solid #000;
            border-bottom: 0.8px solid #000;
            height: 2px;
        }
        .form-title {
            text-align: center;
            margin: 6px 0 10px 0;
        }
        .form-title h1 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
        }
        .form-title p {
            font-size: 9pt;
            margin: 3px 0 0 0;
        }
        .content-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .content-table td {
            padding: 3.5px 6px;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.3;
            border: 1px solid #334155;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .section-header td {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            font-size: 8.5pt;
            padding: 4px 6px;
        }
        .label-col {
            width: 34%;
        }
        .val-col {
            width: 66%;
            font-weight: bold;
        }
        .sig-table {
            width: 100%;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .sig-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .form-sig-space {
            height: 44px;
        }

        /* Styling khusus Surat Keterangan Kematian Resmi */
        .doc-title {
            text-align: center;
            margin: 10px 0 12px 0;
        }
        .doc-title h1 {
            font-size: 13.5pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title p {
            font-size: 10pt;
            font-weight: bold;
            margin: 2px 0 0 0;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 2px;
        }
        .data-table {
            width: 100%;
        }
        .data-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 9.8pt;
            line-height: 1.32;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .col-num {
            width: 4%;
        }
        .col-label {
            width: 30%;
        }
        .col-colon {
            width: 3%;
        }
        .col-val {
            width: 63%;
        }
        .page-break {
            page-break-before: always;
        }
        .sig-space {
            height: 60px;
        }
        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    @php
        $romans = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $monthNum = (int) ($death->death_date ? $death->death_date->format('n') : date('n'));
        $monthRoman = $romans[$monthNum] ?? 'IX';
        $letterYear = $death->death_date ? $death->death_date->format('Y') : date('Y');
        $letterNumberSeq = (int) substr($death->registration_no, -4) ?: '82';

        $deceasedAge = null;
        if ($death->birth_date && $death->death_date) {
            $deceasedAge = (int) $death->birth_date->diffInYears($death->death_date);
        } elseif ($death->birth_date) {
            $deceasedAge = (int) $death->birth_date->diffInYears(now());
        }

        $motherAge = $death->mother_birth_date ? (int) $death->mother_birth_date->diffInYears($death->death_date ?? now()) : null;
        $fatherAge = $death->father_birth_date ? (int) $death->father_birth_date->diffInYears($death->death_date ?? now()) : null;

        $genderText = $death->gender === 'L' ? 'LAKI - LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-');
        $deceasedFullAddress = $death->deceased_address ?: (
            ($death->padukuhan ? $death->padukuhan . ', ' : '') .
            (($death->rt || $death->rw) ? 'RT ' . ($death->rt ?: '-') . ' / RW ' . ($death->rw ?: '-') . ', ' : '') .
            'Purwobinangun, Pakem, Sleman'
        );
        $reportDateFormatted = $death->report_date
            ? \Carbon\Carbon::parse($death->report_date)->translatedFormat('d F Y')
            : ($death->created_at ? $death->created_at->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y'));
        $dotsPlaceholder = '...................................................................';

        $formatBirthAndAge = function ($place, $date, $age) {
            $parts = [];
            if ($place) {
                $parts[] = strtoupper($place);
            }
            if ($date) {
                $parts[] = $date->format('d-m-Y');
            }
            $base = implode(', ', $parts);
            if ($age) {
                $base .= ($base ? '   Umur ' : 'Umur ') . $age . ' th';
            }
            return $base ?: '';
        };

        $motherBirth = $formatBirthAndAge($death->mother_birth_place, $death->mother_birth_date, $motherAge);
        $fatherBirth = $formatBirthAndAge($death->father_birth_place, $death->father_birth_date, $fatherAge);
        $applicantBirth = $formatBirthAndAge($death->applicant_birth_place, $death->applicant_birth_date, $death->applicant_age);
        $witness1Birth = $formatBirthAndAge($death->witness1_birth_place, $death->witness1_birth_date, $death->witness1_age);
        $witness2Birth = $formatBirthAndAge($death->witness2_birth_place, $death->witness2_birth_date, $death->witness2_age);

        $witness1Nik = $death->witness1_nik ?: ($death->witness_nik ?: '');
        $witness1Name = $death->witness1_name ?: ($death->witness_name ?: '');
    @endphp

    <!-- =========================================================================
         BAGIAN 1: FORMULIR DATA ISIAN PENGAJUAN AKTA KEMATIAN
    ========================================================================== -->
    <div>
        <table class="header-table">
            <tr>
                @if(!empty($logoBase64))
                    <td class="header-logo">
                        <img src="{{ $logoBase64 }}" alt="Logo Sleman">
                    </td>
                    <td class="header-text">
                @else
                    <td class="header-text" style="width: 100%; padding-right: 0;">
                @endif
                    <h4>PEMERINTAH KABUPATEN SLEMAN</h4>
                    <h3>KAPANEWON PAKEM</h3>
                    <h2>PEMERINTAH KALURAHAN PURWOBINANGUN</h2>
                    @if(!empty($aksaraBase64))
                        <div style="margin: 2px 0;">
                            <img src="{{ $aksaraBase64 }}" alt="Aksara Jawa" style="height: 13px;">
                        </div>
                    @endif
                    <p>Alamat : Jl. Pakem-Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582</p>
                    <p>Laman : <u>www.purwobinangun.slemankab.go.id</u> &nbsp;&nbsp; Surel : desapurwobinangun@slemankab.go.id</p>
                </td>
            </tr>
        </table>
        <div class="double-line" style="margin-bottom: 10px;"></div>

        <div class="form-title">
            <h1>FORMULIR PELAPORAN &amp; PERMOHONAN AKTA KEMATIAN</h1>
            <p>Nomor Registrasi: <strong>{{ $death->registration_no }}</strong> &nbsp;|&nbsp; Tanggal Lapor: <strong>{{ $reportDateFormatted }}</strong></p>
        </div>

        <table class="content-table">
            <!-- I. DATA KELUARGA -->
            <tr class="section-header">
                <td colspan="2">I. DATA KELUARGA &amp; PENDAFTARAN</td>
            </tr>
            <tr>
                <td class="label-col">Nomor Registrasi</td>
                <td class="val-col">{{ $death->registration_no }}</td>
            </tr>
            <tr>
                <td class="label-col">Nomor Kartu Keluarga (KK)</td>
                <td class="val-col">{{ $death->family_card_no ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Nama Kepala Keluarga</td>
                <td class="val-col uppercase">{{ $death->head_of_family_name ?: '-' }}</td>
            </tr>

            <!-- II. DATA JENAZAH -->
            <tr class="section-header">
                <td colspan="2">II. BIODATA LENGKAP JENAZAH</td>
            </tr>
            <tr>
                <td class="label-col">NIK Jenazah</td>
                <td class="val-col">{{ $death->deceased_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Nama Lengkap Jenazah</td>
                <td class="val-col uppercase">{{ $death->deceased_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Jenis Kelamin / Anak Ke</td>
                <td class="val-col">{{ $genderText }}{{ $death->deceased_child_order ? ' / Anak ke-' . $death->deceased_child_order : '' }}</td>
            </tr>
            <tr>
                <td class="label-col">Tempat, Tanggal Lahir / Umur</td>
                <td class="val-col uppercase">{{ $death->deceased_birth_place ?: 'SLEMAN' }}, {{ $death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-' }}{{ $deceasedAge !== null ? ' (' . $deceasedAge . ' Tahun)' : '' }}</td>
            </tr>
            <tr>
                <td class="label-col">Agama &amp; Pekerjaan</td>
                <td class="val-col uppercase">{{ $death->religion ?: '-' }} &mdash; {{ $death->deceased_job ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Alamat Lengkap Jenazah</td>
                <td class="val-col uppercase">{{ $deceasedFullAddress }}</td>
            </tr>

            <!-- III. RINCIAN PERISTIWA KEMATIAN -->
            <tr class="section-header">
                <td colspan="2">III. RINCIAN PERISTIWA KEMATIAN</td>
            </tr>
            <tr>
                <td class="label-col">Hari, Tanggal &amp; Pukul Kematian</td>
                <td class="val-col uppercase">{{ $death->death_day ? $death->death_day . ', ' : '' }}{{ $death->death_date ? $death->death_date->translatedFormat('d F Y') : '-' }}{{ $death->death_time ? ' — Pukul ' . $death->death_time . ' WIB' : '' }}</td>
            </tr>
            <tr>
                <td class="label-col">Tempat Kematian</td>
                <td class="val-col uppercase">{{ $death->death_place ?: '-' }}{{ $death->death_location_detail ? ' (' . $death->death_location_detail . ')' : '' }}</td>
            </tr>
            <tr>
                <td class="label-col">Sebab Kematian &amp; Yang Menerangkan</td>
                <td class="val-col uppercase">{{ $death->cause_of_death ?: '-' }} &mdash; {{ $death->reported_by_title ?: 'LAINNYA' }}</td>
            </tr>

            <!-- IV. DATA ORANG TUA JENAZAH -->
            <tr class="section-header">
                <td colspan="2">IV. DATA ORANG TUA KANDUNG JENAZAH</td>
            </tr>
            <tr>
                <td class="label-col">Ibu Kandung (Nama / NIK)</td>
                <td class="val-col uppercase">{{ $death->mother_name ?: '-' }} ({{ $death->mother_nik ?: '-' }})</td>
            </tr>
            <tr>
                <td class="label-col">TTL / Pekerjaan / Alamat Ibu</td>
                <td class="val-col">{{ $motherBirth ?: '-' }} &mdash; {{ $death->mother_job ?: '-' }} &mdash; {{ $death->mother_address ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Ayah Kandung (Nama / NIK)</td>
                <td class="val-col uppercase">{{ $death->father_name ?: '-' }} ({{ $death->father_nik ?: '-' }})</td>
            </tr>
            <tr>
                <td class="label-col">TTL / Pekerjaan / Alamat Ayah</td>
                <td class="val-col">{{ $fatherBirth ?: '-' }} &mdash; {{ $death->father_job ?: '-' }} &mdash; {{ $death->father_address ?: '-' }}</td>
            </tr>

            <!-- V. DATA PELAPOR & SAKSI -->
            <tr class="section-header">
                <td colspan="2">V. DATA PELAPOR (AHLI WARIS) &amp; SAKSI</td>
            </tr>
            <tr>
                <td class="label-col">Pelapor (Nama / NIK / Hubungan)</td>
                <td class="val-col">{{ strtoupper($death->applicant_name ?: '-') }} / {{ $death->applicant_nik ?: '-' }} ({{ $death->applicant_relation ?: 'Ahli Waris / Keluarga' }}){{ $death->applicant_phone ? ' | Telp: ' . $death->applicant_phone : '' }}</td>
            </tr>
            <tr>
                <td class="label-col">TTL / Pekerjaan / Alamat Pelapor</td>
                <td class="val-col">{{ $applicantBirth ?: '-' }} &mdash; {{ $death->applicant_job ?: '-' }} &mdash; {{ $death->applicant_address ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Saksi I (Nama / NIK / TTL / Alamat)</td>
                <td class="val-col">{{ strtoupper($witness1Name ?: '-') }} / {{ $witness1Nik ?: '-' }}{{ $witness1Birth ? ' / ' . $witness1Birth : '' }}<br><span style="font-weight: normal;">Alamat: {{ $death->witness1_address ?: '-' }}</span></td>
            </tr>
            <tr>
                <td class="label-col">Saksi II (Nama / NIK / TTL / Alamat)</td>
                <td class="val-col">{{ strtoupper($death->witness2_name ?: '-') }} / {{ $death->witness2_nik ?: '-' }}{{ $witness2Birth ? ' / ' . $witness2Birth : '' }}<br><span style="font-weight: normal;">Alamat: {{ $death->witness2_address ?: '-' }}</span></td>
            </tr>
        </table>

        <table class="sig-table">
            <tr>
                <td>
                    Sleman, {{ $reportDateFormatted }}<br>
                    <strong>Petugas Penerima Berkas,</strong>
                    <div class="form-sig-space"></div>
                    <u>( {{ $death->processed_by ?: '...................................................' }} )</u>
                </td>
                <td>
                    Sleman, {{ $reportDateFormatted }}<br>
                    <strong>Pemohon / Pelapor,</strong>
                    <div class="form-sig-space"></div>
                    <u><strong>( {{ strtoupper($death->applicant_name ?: '-') }} )</strong></u>
                </td>
            </tr>
        </table>
    </div>

    <!-- =========================================================================
         BAGIAN TERAKHIR: SURAT KETERANGAN KEMATIAN RESMI KALURAHAN PURWOBINANGUN
    ========================================================================== -->
    <div class="page-break">
        <!-- 1. KOP SURAT RESMI -->
        <table class="header-table">
            <tr>
                @if(!empty($logoBase64))
                    <td class="header-logo">
                        <img src="{{ $logoBase64 }}" alt="Logo Sleman">
                    </td>
                    <td class="header-text">
                @else
                    <td class="header-text" style="width: 100%; padding-right: 0;">
                @endif
                    <h4>PEMERINTAH KABUPATEN SLEMAN</h4>
                    <h3>KAPANEWON PAKEM</h3>
                    <h2>PEMERINTAH KALURAHAN PURWOBINANGUN</h2>
                    @if(!empty($aksaraBase64))
                        <div style="margin: 2px 0;">
                            <img src="{{ $aksaraBase64 }}" alt="Aksara Jawa" style="height: 13px;">
                        </div>
                    @endif
                    <p>Alamat : Jl. Pakem-Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582</p>
                    <p>Laman : <u>www.purwobinangun.slemankab.go.id</u> &nbsp;&nbsp; Surel : desapurwobinangun@slemankab.go.id</p>
                </td>
            </tr>
        </table>

        <!-- Garis Ganda -->
        <div class="double-line"></div>

        <!-- 2. JUDUL SURAT -->
        <div class="doc-title">
            <h1>SURAT KETERANGAN KEMATIAN</h1>
            <p>No. 474.3/{{ $letterNumberSeq }}/{{ $monthRoman }}/{{ $letterYear }}</p>
        </div>

        <!-- 3. DATA KEPALA KELUARGA -->
        <table class="data-table" style="margin-bottom: 8px;">
            <tr>
                <td style="width: 34%;">Nama Kepala Keluarga</td>
                <td class="col-colon">:</td>
                <td style="width: 63%;" class="uppercase">{{ $death->head_of_family_name ?: '-' }}</td>
            </tr>
            <tr>
                <td style="width: 34%;">Nomor Kartu Keluarga</td>
                <td class="col-colon">:</td>
                <td style="width: 63%;">{{ $death->family_card_no ?: '-' }}</td>
            </tr>
        </table>

        <!-- 4. SECTION JENAZAH -->
        <div class="section-title">JENAZAH</div>
        <table class="data-table">
            <tr>
                <td class="col-num">1</td>
                <td class="col-label">NIK</td>
                <td class="col-colon">:</td>
                <td class="col-val">{{ $death->deceased_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">2</td>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-colon">:</td>
                <td class="col-val uppercase">{{ $death->deceased_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">3</td>
                <td class="col-label">Jenis Kelamin / Anak ke</td>
                <td class="col-colon">:</td>
                <td class="col-val">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 68%; padding: 0;">{{ $genderText }}</td>
                            <td style="width: 32%; padding: 0; text-align: right;">Anak ke {{ $death->deceased_child_order ?: '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-num">4</td>
                <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                <td class="col-colon">:</td>
                <td class="col-val">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 68%; padding: 0;" class="uppercase">{{ $death->deceased_birth_place ?: 'SLEMAN' }}, {{ $death->birth_date ? $death->birth_date->format('d-m-Y') : '-' }}</td>
                            <td style="width: 32%; padding: 0; text-align: right;">Umur {{ $deceasedAge !== null ? $deceasedAge . ' th' : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-num">5</td>
                <td class="col-label">Agama</td>
                <td class="col-colon">:</td>
                <td class="col-val uppercase">{{ $death->religion ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">6</td>
                <td class="col-label">Pekerjaan</td>
                <td class="col-colon">:</td>
                <td class="col-val uppercase">{{ $death->deceased_job ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">7</td>
                <td class="col-label">Alamat</td>
                <td class="col-colon">:</td>
                <td class="col-val uppercase">{{ $deceasedFullAddress }}</td>
            </tr>
            <tr>
                <td class="col-num">8</td>
                <td class="col-label">Tanggal dan Jam Kematian</td>
                <td class="col-colon">:</td>
                <td class="col-val">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 60%; padding: 0;">{{ $death->death_date ? $death->death_date->format('d-m-Y') : '-' }}</td>
                            <td style="width: 40%; padding: 0; text-align: right;">Jam: {{ $death->death_time ? str_replace(':', '.', $death->death_time) . ' WIB' : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-num">9</td>
                <td class="col-label">Sebab Kematian</td>
                <td class="col-colon">:</td>
                <td class="col-val uppercase">{{ $death->cause_of_death ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">10</td>
                <td class="col-label">Tempat Kematian</td>
                <td class="col-colon">:</td>
                <td class="col-val uppercase">{{ $death->death_place ?: '-' }}{{ $death->death_location_detail ? ' (' . $death->death_location_detail . ')' : '' }}</td>
            </tr>
            <tr>
                <td class="col-num">11</td>
                <td class="col-label">Yang Menerangkan</td>
                <td class="col-colon">:</td>
                <td class="col-val uppercase">{{ $death->reported_by_title ?: 'LAINNYA' }}</td>
            </tr>
        </table>

        <!-- LANJUTAN SURAT KETERANGAN KEMATIAN: IBU, AYAH, PELAPOR, SAKSI 1, SAKSI 2 & TANDA TANGAN -->
        <div class="page-break">
            <!-- 1. IBU -->
            <div class="section-title" style="margin-top: 0;">IBU</div>
            <table class="data-table">
                <tr>
                    <td class="col-num">1</td>
                    <td class="col-label">NIK</td>
                    <td class="col-colon">:</td>
                    <td class="col-val">{{ $death->mother_nik ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">2</td>
                    <td class="col-label">Nama Lengkap</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->mother_name ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">3</td>
                    <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $motherBirth ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">4</td>
                    <td class="col-label">Alamat</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->mother_address ?: $dotsPlaceholder }}</td>
                </tr>
            </table>

            <!-- 2. AYAH -->
            <div class="section-title">AYAH</div>
            <table class="data-table">
                <tr>
                    <td class="col-num">1</td>
                    <td class="col-label">NIK</td>
                    <td class="col-colon">:</td>
                    <td class="col-val">{{ $death->father_nik ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">2</td>
                    <td class="col-label">Nama Lengkap</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->father_name ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">3</td>
                    <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $fatherBirth ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">4</td>
                    <td class="col-label">Alamat</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->father_address ?: $dotsPlaceholder }}</td>
                </tr>
            </table>

            <!-- 3. PELAPOR -->
            <div class="section-title">PELAPOR</div>
            <table class="data-table">
                <tr>
                    <td class="col-num">1</td>
                    <td class="col-label">NIK</td>
                    <td class="col-colon">:</td>
                    <td class="col-val">{{ $death->applicant_nik ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">2</td>
                    <td class="col-label">Nama Lengkap</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->applicant_name ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">3</td>
                    <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $applicantBirth ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">4</td>
                    <td class="col-label">Alamat</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->applicant_address ?: $dotsPlaceholder }}</td>
                </tr>
            </table>

            <!-- 4. SAKSI 1 -->
            <div class="section-title">SAKSI 1</div>
            <table class="data-table">
                <tr>
                    <td class="col-num">1</td>
                    <td class="col-label">NIK</td>
                    <td class="col-colon">:</td>
                    <td class="col-val">{{ $witness1Nik ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">2</td>
                    <td class="col-label">Nama Lengkap</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $witness1Name ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">3</td>
                    <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $witness1Birth ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">4</td>
                    <td class="col-label">Alamat</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->witness1_address ?: $dotsPlaceholder }}</td>
                </tr>
            </table>

            <!-- 5. SAKSI 2 -->
            <div class="section-title">SAKSI 2</div>
            <table class="data-table">
                <tr>
                    <td class="col-num">1</td>
                    <td class="col-label">NIK</td>
                    <td class="col-colon">:</td>
                    <td class="col-val">{{ $death->witness2_nik ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">2</td>
                    <td class="col-label">Nama Lengkap</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->witness2_name ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">3</td>
                    <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $witness2Birth ?: $dotsPlaceholder }}</td>
                </tr>
                <tr>
                    <td class="col-num">4</td>
                    <td class="col-label">Alamat</td>
                    <td class="col-colon">:</td>
                    <td class="col-val uppercase">{{ $death->witness2_address ?: $dotsPlaceholder }}</td>
                </tr>
            </table>

            <!-- 6. TANDA TANGAN -->
            <table class="avoid-break" style="width: 100%; margin-top: 20px;">
                <tr>
                    <td style="width: 55%;"></td>
                    <td style="width: 45%; text-align: left;">
                        <p style="margin: 0;">Purwobinangun, {{ $reportDateFormatted }}</p>
                        <div class="sig-space"></div>
                        <p style="margin: 0;">( .................................................... )</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
