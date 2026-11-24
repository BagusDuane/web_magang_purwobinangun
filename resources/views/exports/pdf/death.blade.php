<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Kematian - {{ $death->registration_no }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        body {
            font-family: 'Times-Roman', 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000000;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo {
            width: 70px;
            vertical-align: top;
            text-align: left;
        }
        .header-logo img {
            width: 58px;
            height: auto;
        }
        .header-text {
            text-align: center;
            vertical-align: top;
            padding-right: 60px;
        }
        .header-text h4 {
            margin: 0;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h3 {
            margin: 1px 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 1px 0;
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 1px 0 0 0;
            font-size: 8pt;
        }
        .double-line {
            margin-top: 6px;
            border-top: 3px solid #000;
            border-bottom: 0.72px solid #000;
            height: 2px;
        }
        .doc-title {
            text-align: center;
            margin: 10px 0 12px 0;
        }
        .doc-title h1 {
            font-size: 14pt;
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
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .col-num {
            width: 24px;
        }
        .col-label {
            width: 190px;
        }
        .col-colon {
            width: 15px;
        }
        .page-break {
            page-break-after: always;
        }
        .sig-block {
            margin-top: 15px;
            float: right;
            width: 250px;
            text-align: left;
            font-size: 9.5pt;
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
        $monthNum = (int)($death->death_date ? $death->death_date->format('n') : date('n'));
        $monthRoman = $romans[$monthNum] ?? 'IX';
        $letterYear = $death->death_date ? $death->death_date->format('Y') : date('Y');
        $letterNumberSeq = (int)substr($death->registration_no, -4) ?: '82';

        $deceasedAge = null;
        if ($death->birth_date && $death->death_date) {
            $deceasedAge = $death->birth_date->diffInYears($death->death_date);
        } elseif ($death->birth_date) {
            $deceasedAge = $death->birth_date->diffInYears(now());
        }

        $motherAge = $death->mother_birth_date ? $death->mother_birth_date->diffInYears($death->death_date ?? now()) : null;
        $fatherAge = $death->father_birth_date ? $death->father_birth_date->diffInYears($death->death_date ?? now()) : null;

        $genderText = $death->gender === 'L' ? 'LAKI - LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-');
        $deceasedFullAddress = $death->deceased_address ?: ('Padukuhan ' . $death->padukuhan . ', RT ' . $death->rt . ' / RW ' . $death->rw . ', Purwobinangun, Pakem, Sleman');
        $reportDateFormatted = $death->report_date ? \Carbon\Carbon::parse($death->report_date)->translatedFormat('d F Y') : ($death->created_at ? $death->created_at->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y'));
        $officerName = $death->officer_name ?: 'Teguh Kurniawan, S.Pi.';

        $formatBirthAndAge = function($place, $date, $age) {
            $parts = [];
            if ($place) $parts[] = strtoupper($place);
            if ($date) $parts[] = $date->format('d - m - Y');
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
    @endphp

    <!-- =========================================================================
         HALAMAN 1: KOP SURAT, JUDUL, DATA KEPALA KELUARGA & SECTION JENAZAH
    ========================================================================== -->
    <div>
        <!-- 1. KOP SURAT RESMI -->
        <table class="header-table">
            <tr>
                @if(!empty($logoBase64))
                    <td class="header-logo">
                        <img src="{{ $logoBase64 }}" alt="Logo Sleman">
                    </td>
                @endif
                <td class="header-text">
                    <h4>PEMERINTAH KABUPATEN SLEMAN</h4>
                    <h3>KAPANEWON PAKEM</h3>
                    <h2>PEMERINTAH KALURAHAN PURWOBINANGUN</h2>
                    @if(!empty($aksaraBase64))
                        <div style="margin: 2px 0;">
                            <img src="{{ $aksaraBase64 }}" alt="Aksara Jawa" style="height: 14px;">
                        </div>
                    @endif
                    <p>Alamat : Jl. Pakem - Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582</p>
                    <p>Laman : <u>www.purwobinangun.slemankab.go.id</u> &nbsp;&nbsp; Surel : desapurwobinangun@slemankab.go.id</p>
                </td>
            </tr>
        </table>

        <!-- Garis Ganda -->
        <div class="double-line"></div>

        <!-- 2. JUDUL SURAT -->
        <div class="doc-title">
            <h1>SURAT KETERANGAN KEMATIAN</h1>
            <p>No. 474.3/ &nbsp;{{ $letterNumberSeq }}&nbsp; /{{ $monthRoman }}/{{ $letterYear }}</p>
        </div>

        <!-- 3. DATA KEPALA KELUARGA -->
        <table class="data-table" style="margin-bottom: 8px;">
            <tr>
                <td class="col-label" style="width: 214px;">Nama Kepala Keluarga</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->head_of_family_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-label" style="width: 214px;">Nomor Kartu Keluarga</td>
                <td class="col-colon">:</td>
                <td>{{ $death->family_card_no ?: '-' }}</td>
            </tr>
        </table>

        <!-- 4. SECTION JENAZAH -->
        <div class="section-title">JENAZAH</div>
        <table class="data-table">
            <tr>
                <td class="col-num">1</td>
                <td class="col-label">NIK</td>
                <td class="col-colon">:</td>
                <td>{{ $death->deceased_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">2</td>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->deceased_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">3</td>
                <td class="col-label">Jenis Kelamin / Anak ke</td>
                <td class="col-colon">:</td>
                <td>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">{{ $genderText }}</td>
                            <td style="padding: 0; text-align: right; width: 140px;">Anak ke &nbsp; {{ $death->deceased_child_order ?: '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-num">4</td>
                <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                <td class="col-colon">:</td>
                <td>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;" class="uppercase">{{ $death->deceased_birth_place ?: 'SLEMAN' }}, {{ $death->birth_date ? $death->birth_date->format('d - m - Y') : '-' }}</td>
                            <td style="padding: 0; text-align: right; width: 140px;">Umur {{ $deceasedAge !== null ? $deceasedAge . ' th' : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-num">5</td>
                <td class="col-label">Agama</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->religion ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">6</td>
                <td class="col-label">Pekerjaan</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->deceased_job ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">7</td>
                <td class="col-label">Alamat</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $deceasedFullAddress }}</td>
            </tr>
            <tr>
                <td class="col-num">8</td>
                <td class="col-label">Tanggal dan Jam Kematian</td>
                <td class="col-colon">:</td>
                <td>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 0;">{{ $death->death_date ? $death->death_date->format('d - m - Y') : '-' }}</td>
                            <td style="padding: 0; text-align: right; width: 140px;">Jam: {{ $death->death_time ? $death->death_time . ' WIB' : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="col-num">9</td>
                <td class="col-label">Sebab Kematian</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->cause_of_death ?: '-' }}</td>
            </tr>
            <tr>
                <td class="col-num">10</td>
                <td class="col-label">Tempat Kematian</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->death_place ?: '-' }}{{ $death->death_location_detail ? ' (' . $death->death_location_detail . ')' : '' }}</td>
            </tr>
            <tr>
                <td class="col-num">11</td>
                <td class="col-label">Yang Menerangkan</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->reported_by_title ?: 'LAINNYA' }}</td>
            </tr>
        </table>
    </div>

    <!-- PAGE BREAK -->
    <div class="page-break"></div>

    <!-- =========================================================================
         HALAMAN 2: SECTION IBU, AYAH, PELAPOR, SAKSI 1, SAKSI 2 & TANDA TANGAN
    ========================================================================== -->
    <div>
        <!-- 1. IBU -->
        <div class="section-title">IBU</div>
        <table class="data-table">
            <tr>
                <td class="col-num">1</td>
                <td class="col-label">NIK</td>
                <td class="col-colon">:</td>
                <td>{{ $death->mother_nik ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">2</td>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->mother_name ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">3</td>
                <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $motherBirth }}</td>
            </tr>
            <tr>
                <td class="col-num">4</td>
                <td class="col-label">Alamat</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->mother_address ?: '' }}</td>
            </tr>
        </table>

        <!-- 2. AYAH -->
        <div class="section-title" style="margin-top: 10px;">AYAH</div>
        <table class="data-table">
            <tr>
                <td class="col-num">1</td>
                <td class="col-label">NIK</td>
                <td class="col-colon">:</td>
                <td>{{ $death->father_nik ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">2</td>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->father_name ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">3</td>
                <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $fatherBirth }}</td>
            </tr>
            <tr>
                <td class="col-num">4</td>
                <td class="col-label">Alamat</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->father_address ?: '' }}</td>
            </tr>
        </table>

        <!-- 3. PELAPOR -->
        <div class="section-title" style="margin-top: 10px;">PELAPOR</div>
        <table class="data-table">
            <tr>
                <td class="col-num">1</td>
                <td class="col-label">NIK</td>
                <td class="col-colon">:</td>
                <td>{{ $death->applicant_nik ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">2</td>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->applicant_name ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">3</td>
                <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $applicantBirth }}</td>
            </tr>
            <tr>
                <td class="col-num">4</td>
                <td class="col-label">Alamat</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->applicant_address ?: '' }}</td>
            </tr>
        </table>

        <!-- 4. SAKSI 1 -->
        <div class="section-title" style="margin-top: 10px;">SAKSI 1</div>
        <table class="data-table">
            <tr>
                <td class="col-num">1</td>
                <td class="col-label">NIK</td>
                <td class="col-colon">:</td>
                <td>{{ $death->witness1_nik ?: ($death->witness_nik ?: '') }}</td>
            </tr>
            <tr>
                <td class="col-num">2</td>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->witness1_name ?: ($death->witness_name ?: '') }}</td>
            </tr>
            <tr>
                <td class="col-num">3</td>
                <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $witness1Birth }}</td>
            </tr>
            <tr>
                <td class="col-num">4</td>
                <td class="col-label">Alamat</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->witness1_address ?: '' }}</td>
            </tr>
        </table>

        <!-- 5. SAKSI 2 -->
        <div class="section-title" style="margin-top: 10px;">SAKSI 2</div>
        <table class="data-table">
            <tr>
                <td class="col-num">1</td>
                <td class="col-label">NIK</td>
                <td class="col-colon">:</td>
                <td>{{ $death->witness2_nik ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">2</td>
                <td class="col-label">Nama Lengkap</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->witness2_name ?: '' }}</td>
            </tr>
            <tr>
                <td class="col-num">3</td>
                <td class="col-label">Tempat dan Tanggal Lahir /<br>Umur</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $witness2Birth }}</td>
            </tr>
            <tr>
                <td class="col-num">4</td>
                <td class="col-label">Alamat</td>
                <td class="col-colon">:</td>
                <td class="uppercase">{{ $death->witness2_address ?: '' }}</td>
            </tr>
        </table>

        <!-- 6. TANDA TANGAN -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 18px;">
            <tr>
                <td style="width: 55%;"></td>
                <td style="width: 45%; text-align: left;">
                    <p style="margin: 0;">Sleman, {{ $reportDateFormatted }}</p>
                    <p style="margin: 2px 0 0 0;">Mengetahui</p>
                    <p style="margin: 0;">a.n Lurah</p>
                    <p style="margin: 0;">Kaur Tata Laksana</p>
                    <div class="sig-space"></div>
                    <p style="margin: 0; font-weight: bold;">{{ $officerName }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
