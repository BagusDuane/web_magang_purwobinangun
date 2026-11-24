<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Kelahiran - {{ $birth->registration_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-logo {
            width: 70px;
            vertical-align: middle;
            text-align: center;
        }
        .header-logo img {
            width: 60px;
            height: auto;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        .header-text h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h2 {
            margin: 2px 0;
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 2px 0 0 0;
            font-size: 8pt;
            font-style: italic;
        }
        .doc-title {
            text-align: center;
            margin: 10px 0 14px 0;
        }
        .doc-title h1 {
            font-size: 12.5pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            color: #095b8c;
        }
        .doc-title p {
            font-size: 8.5pt;
            margin: 3px 0 0 0;
            color: #475569;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .content-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 9pt;
            border: 1px solid #cbd5e1;
        }
        .section-header {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #095b8c;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .label-col {
            width: 32%;
            color: #334155;
        }
        .val-col {
            width: 68%;
            font-weight: bold;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .sig-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9pt;
        }
        .sig-space {
            height: 50px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <table class="header-table">
        <tr>
            @if(!empty($logoBase64))
                <td class="header-logo">
                    <img src="{{ $logoBase64 }}" alt="Logo Sleman">
                </td>
            @endif
            <td class="header-text">
                <h3>PEMERINTAH KABUPATEN SLEMAN</h3>
                <h3>KAPANEWON PAKEM</h3>
                <h2>PEMERINTAH KALURAHAN PURWOBINANGUN</h2>
                <p>Alamat: Jl. Boyong No. 1 Purwobinangun, Pakem, Sleman, D.I. Yogyakarta 55582</p>
            </td>
        </tr>
    </table>

    <!-- JUDUL SURAT -->
    <div class="doc-title">
        <h1>SURAT KETERANGAN KELAHIRAN</h1>
        <p>Nomor Registrasi: <strong>{{ $birth->registration_no }}</strong></p>
    </div>

    <p style="font-size: 9pt; margin-bottom: 8px;">
        Pemerintah Kalurahan Purwobinangun dengan ini menerangkan bahwa telah tercatat pendaftaran permohonan penerbitan Akta Kelahiran atas nama warga di bawah ini:
    </p>

    <!-- TABEL DATA LENGKAP -->
    <table class="content-table">
        <!-- 1. DATA KELUARGA -->
        <tr class="section-header">
            <td colspan="2">I. DATA KELUARGA & PENDAFTARAN</td>
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
            <td class="label-col">Tanggal Pendaftaran</td>
            <td class="val-col">{{ $birth->created_at ? $birth->created_at->translatedFormat('d F Y H:i') : '-' }} WIB</td>
        </tr>

        <!-- 2. DATA BAYI -->
        <tr class="section-header">
            <td colspan="2">II. DATA ANAK / BAYI</td>
        </tr>
        <tr>
            <td class="label-col">Nama Lengkap Bayi</td>
            <td class="val-col">{{ strtoupper($birth->child_name) }}</td>
        </tr>
        <tr>
            <td class="label-col">NIK Anak</td>
            <td class="val-col">{{ $birth->child_nik ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Jenis Kelamin</td>
            <td class="val-col">{{ $birth->gender === 'L' ? 'LAKI-LAKI' : ($birth->gender === 'P' ? 'PEREMPUAN' : '-') }}</td>
        </tr>
        <tr>
            <td class="label-col">Tempat Lahir</td>
            <td class="val-col">{{ strtoupper($birth->birth_place ?: 'Sleman') }}</td>
        </tr>
        <tr>
            <td class="label-col">Hari & Tanggal Lahir</td>
            <td class="val-col">{{ ($birth->birth_day ? strtoupper($birth->birth_day) . ', ' : '') . ($birth->birth_date ? \Carbon\Carbon::parse($birth->birth_date)->translatedFormat('d F Y') : '-') }}</td>
        </tr>
        <tr>
            <td class="label-col">Pukul / Jam Lahir</td>
            <td class="val-col">{{ $birth->birth_time ? $birth->birth_time . ' WIB' : '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Anak Ke / Jenis Kelahiran</td>
            <td class="val-col">Anak ke-{{ $birth->birth_order ?: '1' }} / {{ $birth->birth_type ?: 'Tunggal' }}</td>
        </tr>
        <tr>
            <td class="label-col">Berat & Panjang Bayi</td>
            <td class="val-col">{{ $birth->weight_kg ?: '-' }} kg ({{ $birth->weight_gram ?: '-' }} gram) / {{ $birth->length_cm ?: '-' }} cm</td>
        </tr>
        <tr>
            <td class="label-col">Tempat Bersalin / Penolong</td>
            <td class="val-col">{{ $birth->birth_place_type ?: '-' }} / {{ $birth->birth_helper ?: '-' }}</td>
        </tr>

        <!-- 3. DATA ORANG TUA -->
        <tr class="section-header">
            <td colspan="2">III. DATA ORANG TUA KANDUNG</td>
        </tr>
        <tr>
            <td class="label-col">Nama Ayah / NIK</td>
            <td class="val-col">{{ strtoupper($birth->father_name ?: '-') }} / {{ $birth->father_nik ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Pekerjaan & Alamat Ayah</td>
            <td class="val-col">{{ $birth->father_job ?: '-' }} &mdash; {{ $birth->father_address ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Nama Ibu Kandung / NIK</td>
            <td class="val-col">{{ strtoupper($birth->mother_name ?: '-') }} / {{ $birth->mother_nik ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Pekerjaan & Alamat Ibu</td>
            <td class="val-col">{{ $birth->mother_job ?: '-' }} &mdash; {{ $birth->mother_address ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Tanggal Perkawinan</td>
            <td class="val-col">{{ $birth->marriage_date ? \Carbon\Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-' }}</td>
        </tr>

        <!-- 4. DATA PELAPOR & SAKSI -->
        <tr class="section-header">
            <td colspan="2">IV. DATA PELAPOR & SAKSI</td>
        </tr>
        <tr>
            <td class="label-col">Pelapor (Nama / NIK / Hubungan)</td>
            <td class="val-col">{{ strtoupper($birth->applicant_name ?: '-') }} / {{ $birth->applicant_nik ?: '-' }} ({{ $birth->applicant_relation ?: 'Keluarga' }})</td>
        </tr>
        <tr>
            <td class="label-col">Saksi I (Nama / NIK)</td>
            <td class="val-col">{{ $birth->witness_1_name ?: '-' }} / {{ $birth->witness_1_nik ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Saksi II (Nama / NIK)</td>
            <td class="val-col">{{ $birth->witness_2_name ?: '-' }} / {{ $birth->witness_2_nik ?: '-' }}</td>
        </tr>
    </table>

    <!-- TANDA TANGAN -->
    <table class="sig-table">
        <tr>
            <td>
                Mengetahui / Memeriksa:<br>
                <strong>Petugas Registrasi Kalurahan,</strong>
                <div class="sig-space"></div>
                <u>( ..................................................... )</u>
            </td>
            <td>
                Purwobinangun, {{ $birth->created_at ? $birth->created_at->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Lurah Purwobinangun,</strong>
                <div class="sig-space"></div>
                <u><strong>( R. HADI PURWANTO )</strong></u>
            </td>
        </tr>
    </table>

</body>
</html>
