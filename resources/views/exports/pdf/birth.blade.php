<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Kelahiran - {{ $birth->registration_no }}</title>
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
            margin-bottom: 10px;
        }
        .doc-title {
            text-align: center;
            margin: 6px 0 10px 0;
        }
        .doc-title h1 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title p {
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
        .sig-space {
            height: 46px;
        }
        .page-break {
            page-break-before: always;
        }

        /* Styling khusus Halaman Terakhir: Surat Keterangan Resmi */
        .sk-title {
            text-align: center;
            margin: 14px 0 18px 0;
        }
        .sk-title h1 {
            font-size: 13.5pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .sk-title p {
            font-size: 10.5pt;
            margin: 4px 0 0 0;
        }
        .sk-body {
            font-size: 10.5pt;
            line-height: 1.5;
        }
        .sk-table {
            width: 96%;
            margin-left: 14px;
        }
        .sk-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .sk-indent-table {
            width: 90%;
            margin-left: 48px;
            margin-top: 2px;
            margin-bottom: 8px;
        }
        .sk-indent-table td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 10.5pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .sk-paragraph {
            text-align: justify;
            text-indent: 36px;
            margin: 10px 0 10px 14px;
            font-size: 10.5pt;
            line-height: 1.5;
        }
        .uppercase {
            text-transform: uppercase;
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
        $reportDateFormatted = $refDate->translatedFormat('d F Y');
        $genderText = $birth->gender === 'L' ? 'LAKI-LAKI' : ($birth->gender === 'P' ? 'PEREMPUAN' : '-');

        $formatFullAddress = function (?string $addr, ?string $rt, ?string $rw, ?string $village, ?string $district, ?string $city, ?string $province, ?string $padukuhan = null): string {
            $parts = [];
            if ($addr) {
                $parts[] = trim($addr);
            } elseif ($padukuhan) {
                $parts[] = 'Padukuhan ' . trim($padukuhan);
            }
            if ($rt || $rw) {
                $parts[] = 'RT ' . ($rt ?: '-') . ' / RW ' . ($rw ?: '-');
            }
            $region = array_filter([$village, $district, $city, $province]);
            if (!empty($region)) {
                $parts[] = implode(', ', $region);
            }
            return !empty($parts) ? implode(', ', $parts) : '-';
        };

        $fatherFullAddr = $formatFullAddress($birth->father_address, $birth->father_rt, $birth->father_rw, $birth->father_village, $birth->father_district, $birth->father_city, $birth->father_province);
        $motherFullAddr = $formatFullAddress($birth->mother_address, $birth->mother_rt, $birth->mother_rw, $birth->mother_village, $birth->mother_district, $birth->mother_city, $birth->mother_province);
        $applicantFullAddr = $formatFullAddress($birth->address, $birth->rt, $birth->rw, $birth->applicant_village ?: 'Purwobinangun', $birth->applicant_district ?: 'Pakem', $birth->applicant_city ?: 'Sleman', $birth->applicant_province ?: 'DI Yogyakarta', $birth->padukuhan);
        $witness1FullAddr = $formatFullAddress($birth->witness_1_address, $birth->witness_1_rt, $birth->witness_1_rw, $birth->witness_1_village, $birth->witness_1_district, $birth->witness_1_city, $birth->witness_1_province);
        $witness2FullAddr = $formatFullAddress($birth->witness_2_address, $birth->witness_2_rt, $birth->witness_2_rw, $birth->witness_2_village, $birth->witness_2_district, $birth->witness_2_city, $birth->witness_2_province);

        $fatherAgeVal = $birth->father_age ?: ($birth->father_birth_date ? (int) $birth->father_birth_date->diffInYears($childDob ?? now()) : null);
        $motherAgeVal = $birth->mother_age ?: ($birth->mother_birth_date ? (int) $birth->mother_birth_date->diffInYears($childDob ?? now()) : null);
        $applicantAgeVal = $birth->applicant_age ?: ($birth->applicant_birth_date ? (int) $birth->applicant_birth_date->diffInYears(now()) : null);
        $w1AgeVal = $birth->witness_1_age ?: ($birth->witness_1_birth_date ? (int) $birth->witness_1_birth_date->diffInYears(now()) : null);
        $w2AgeVal = $birth->witness_2_age ?: ($birth->witness_2_birth_date ? (int) $birth->witness_2_birth_date->diffInYears(now()) : null);

        $applicantName = $birth->applicant_name ?: ($birth->father_name ?: ($birth->mother_name ?: '-'));
        $applicantNik = $birth->applicant_nik ?: ($birth->father_nik ?: ($birth->mother_nik ?: '-'));
        $applicantJob = $birth->applicant_job ?: ($birth->father_job ?: ($birth->mother_job ?: '-'));

        $applicantDobObj = $birth->applicant_birth_date ?: ($birth->father_birth_date ?: $birth->mother_birth_date);
        $applicantDobFormatted = $applicantDobObj ? \Carbon\Carbon::parse($applicantDobObj)->translatedFormat('d F Y') : '-';

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

        $weightStr = ($birth->weight_gram)
            ? $birth->weight_gram . ' gram' . ($birth->weight_kg ? ' (' . $birth->weight_kg . ' kg)' : '')
            : ($birth->weight_kg ? $birth->weight_kg . ' kg' : '-');
    @endphp

    <!-- =========================================================================
         BAGIAN 1: FORMULIR DATA ISIAN PENGAJUAN AKTA KELAHIRAN
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
                    <p>Laman : www.purwobinangun.slemankab.go.id &nbsp; Surel : puwobinangun@slemankab.go.id</p>
                </td>
            </tr>
        </table>
        <div class="double-line"></div>

        <div class="doc-title">
            <h1>FORMULIR PELAPORAN &amp; PERMOHONAN AKTA KELAHIRAN</h1>
            <p>Nomor Registrasi: <strong>{{ $birth->registration_no }}</strong> &nbsp;|&nbsp; Tanggal Lapor: <strong>{{ $reportDateFormatted }}</strong></p>
        </div>

        <table class="content-table">
            <!-- I. DATA KELUARGA -->
            <tr class="section-header">
                <td colspan="2">I. DATA KELUARGA &amp; PENDAFTARAN</td>
            </tr>
            <tr>
                <td class="label-col">Nomor Registrasi</td>
                <td class="val-col">{{ $birth->registration_no }}</td>
            </tr>
            <tr>
                <td class="label-col">Nomor Kartu Keluarga (KK)</td>
                <td class="val-col">{{ $birth->family_card_no ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Nama Kepala Keluarga</td>
                <td class="val-col uppercase">{{ $birth->father_name ?: ($birth->mother_name ?: $applicantName) }}</td>
            </tr>

            <!-- II. DATA BAYI / ANAK -->
            <tr class="section-header">
                <td colspan="2">II. BIODATA LENGKAP BAYI / ANAK</td>
            </tr>
            <tr>
                <td class="label-col">Nama Lengkap Bayi / Anak</td>
                <td class="val-col uppercase">{{ $birth->child_name }}</td>
            </tr>
            <tr>
                <td class="label-col">NIK Bayi / Anak</td>
                <td class="val-col">{{ $birth->child_nik ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Jenis Kelamin</td>
                <td class="val-col">{{ $genderText }}</td>
            </tr>
            <tr>
                <td class="label-col">Tempat Dilahirkan / Kabupaten-Kota</td>
                <td class="val-col">{{ $birth->birth_place_type ?: '-' }} &mdash; {{ strtoupper($birth->birth_place ?: 'SLEMAN') }}</td>
            </tr>
            <tr>
                <td class="label-col">Hari, Tanggal &amp; Pukul Kelahiran</td>
                <td class="val-col">{{ ($birth->birth_day ? strtoupper($birth->birth_day) . ', ' : '') . ($childDob ? $childDob->translatedFormat('d F Y') : '-') }}{{ $birth->birth_time ? ' — Pukul ' . $birth->birth_time . ' WIB' : '' }}</td>
            </tr>
            <tr>
                <td class="label-col">Jenis Kelahiran / Kelahiran Anak Ke</td>
                <td class="val-col">{{ $birth->birth_type ?: 'Tunggal' }} / Anak ke-{{ $birth->birth_order ?: '1' }}</td>
            </tr>
            <tr>
                <td class="label-col">Penolong Kelahiran</td>
                <td class="val-col">{{ $birth->birth_helper ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Berat &amp; Panjang Bayi</td>
                <td class="val-col">{{ $weightStr }} / {{ $birth->length_cm ? $birth->length_cm . ' cm' : '-' }}</td>
            </tr>

            <!-- III. DATA KEDUA ORANG TUA -->
            <tr class="section-header">
                <td colspan="2">III. DATA KEDUA ORANG TUA KANDUNG</td>
            </tr>
            <tr>
                <td class="label-col">Nama Lengkap &amp; NIK Ayah</td>
                <td class="val-col">{{ strtoupper($birth->father_name ?: '-') }} ({{ $birth->father_nik ?: '-' }})</td>
            </tr>
            <tr>
                <td class="label-col">Tgl Lahir / Umur &amp; Pekerjaan Ayah</td>
                <td class="val-col">{{ $birth->father_birth_date ? $birth->father_birth_date->translatedFormat('d F Y') : '-' }}{{ $fatherAgeVal !== null ? ' (' . $fatherAgeVal . ' th)' : '' }} &mdash; {{ $birth->father_job ?: '-' }} ({{ $birth->father_citizenship ?: 'WNI' }})</td>
            </tr>
            <tr>
                <td class="label-col">Alamat Ayah</td>
                <td class="val-col">{{ $fatherFullAddr }}</td>
            </tr>
            <tr>
                <td class="label-col">Nama Lengkap &amp; NIK Ibu Kandung</td>
                <td class="val-col">{{ strtoupper($birth->mother_name ?: '-') }} ({{ $birth->mother_nik ?: '-' }})</td>
            </tr>
            <tr>
                <td class="label-col">Tgl Lahir / Umur &amp; Pekerjaan Ibu</td>
                <td class="val-col">{{ $birth->mother_birth_date ? $birth->mother_birth_date->translatedFormat('d F Y') : '-' }}{{ $motherAgeVal !== null ? ' (' . $motherAgeVal . ' th)' : '' }} &mdash; {{ $birth->mother_job ?: '-' }} ({{ $birth->mother_citizenship ?: 'WNI' }})</td>
            </tr>
            <tr>
                <td class="label-col">Alamat Ibu Kandung</td>
                <td class="val-col">{{ $motherFullAddr }}</td>
            </tr>
            <tr>
                <td class="label-col">Tanggal Pencatatan Perkawinan</td>
                <td class="val-col">{{ $birth->marriage_date ? \Carbon\Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-' }}</td>
            </tr>

            <!-- IV. DATA PELAPOR & SAKSI -->
            <tr class="section-header">
                <td colspan="2">IV. DATA PELAPOR &amp; SAKSI KELAHIRAN</td>
            </tr>
            <tr>
                <td class="label-col">Nama Lengkap &amp; NIK Pelapor</td>
                <td class="val-col">{{ strtoupper($applicantName) }} ({{ $applicantNik }})</td>
            </tr>
            <tr>
                <td class="label-col">Umur, Pekerjaan &amp; Hubungan Pelapor</td>
                <td class="val-col">{{ $applicantAgeVal !== null ? $applicantAgeVal . ' th' : '-' }} &mdash; {{ $applicantJob }} ({{ $birth->applicant_relation ?: 'Keluarga' }}){{ $birth->applicant_phone ? ' | Telp: ' . $birth->applicant_phone : '' }}</td>
            </tr>
            <tr>
                <td class="label-col">Alamat Pelapor</td>
                <td class="val-col">{{ $applicantFullAddr }}</td>
            </tr>
            <tr>
                <td class="label-col">Saksi I (Nama / NIK / Umur / Pekerjaan)</td>
                <td class="val-col">{{ strtoupper($birth->witness_1_name ?: '-') }} / {{ $birth->witness_1_nik ?: '-' }}{{ $w1AgeVal !== null ? ' / ' . $w1AgeVal . ' th' : '' }}{{ $birth->witness_1_job ? ' / ' . $birth->witness_1_job : '' }}<br><span style="font-weight: normal;">Alamat: {{ $witness1FullAddr }}</span></td>
            </tr>
            <tr>
                <td class="label-col">Saksi II (Nama / NIK / Umur / Pekerjaan)</td>
                <td class="val-col">{{ strtoupper($birth->witness_2_name ?: '-') }} / {{ $birth->witness_2_nik ?: '-' }}{{ $w2AgeVal !== null ? ' / ' . $w2AgeVal . ' th' : '' }}{{ $birth->witness_2_job ? ' / ' . $birth->witness_2_job : '' }}<br><span style="font-weight: normal;">Alamat: {{ $witness2FullAddr }}</span></td>
            </tr>
        </table>

        <table class="sig-table">
            <tr>
                <td>
                    Sleman, {{ $reportDateFormatted }}<br>
                    <strong>Petugas Penerima Berkas,</strong>
                    <div class="sig-space"></div>
                    <u>( {{ $birth->processed_by ?: '...................................................' }} )</u>
                </td>
                <td>
                    Sleman, {{ $reportDateFormatted }}<br>
                    <strong>Pemohon / Pelapor,</strong>
                    <div class="sig-space">
                        @if(!empty($signatureBase64))
                            <img src="{{ $signatureBase64 }}" alt="Tanda Tangan Pelapor" style="height: 38px; width: auto;">
                        @endif
                    </div>
                    <u><strong>( {{ strtoupper($applicantName) }} )</strong></u>
                </td>
            </tr>
        </table>
    </div>

    <!-- =========================================================================
         HALAMAN TERAKHIR: SURAT KETERANGAN RESMI KALURAHAN PURWOBINANGUN
    ========================================================================== -->
    <div class="page-break">
        <!-- Kop Surat Resmi -->
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
                    <p>Laman : www.purwobinangun.slemankab.go.id &nbsp; Surel : puwobinangun@slemankab.go.id</p>
                </td>
            </tr>
        </table>
        <div class="double-line"></div>

        <!-- Judul Surat Keterangan -->
        <div class="sk-title">
            <h1>SURAT KETERANGAN</h1>
            <p>Nomor : 474.1/ &nbsp;{{ $letterSeq }}&nbsp; / &nbsp;{{ $monthRoman }}&nbsp; / &nbsp;{{ $letterYear }}</p>
        </div>

        <div class="sk-body">
            <!-- Yang bertandatangan di bawah ini -->
            <table class="sk-table">
                <tr>
                    <td style="width: 36%;">Yang bertandatangan di bawah ini</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 61%;"></td>
                </tr>
            </table>
            <table class="sk-indent-table">
                <tr>
                    <td style="width: 24%;">Nama</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 73%;">...................................................</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>...................................................</td>
                </tr>
            </table>

            <!-- Dengan ini menerangkan bahwa -->
            <table class="sk-table" style="margin-top: 8px;">
                <tr>
                    <td style="width: 36%;">Dengan ini menerangkan bahwa</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 61%;"></td>
                </tr>
            </table>
            <table class="sk-indent-table">
                <tr>
                    <td style="width: 24%;">Nama Lengkap</td>
                    <td style="width: 3%;">:</td>
                    <td style="width: 73%;"><strong>{{ $applicantName }}</strong></td>
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
                    <td style="padding-top: 6px;">Pergi ke</td>
                    <td style="padding-top: 6px;">:</td>
                    <td style="padding-top: 6px;">
                        Dinas Kependudukan dan Catatan Sipil<br>
                        Sleman
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 6px;">Keperluan</td>
                    <td style="padding-top: 6px;">:</td>
                    <td style="padding-top: 6px;">Mohon Akta Kelahiran untuk <strong>{{ strtoupper($birth->child_name) }}</strong></td>
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
            <table class="avoid-break" style="width: 100%; margin-top: 22px;">
                <tr>
                    <td style="width: 55%;"></td>
                    <td style="width: 45%; text-align: left; font-size: 10.5pt;">
                        <p style="margin: 0;">Purwobinangun, {{ $reportDateFormatted }}</p>
                        <div style="height: 64px;"></div>
                        <p style="margin: 0;">( .................................................... )</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
