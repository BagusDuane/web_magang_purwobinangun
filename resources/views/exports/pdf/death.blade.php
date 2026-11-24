<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Kematian - {{ $death->registration_no }}</title>
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
            color: #be123c;
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
            background-color: #fee2e2;
            font-weight: bold;
            color: #be123c;
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

    @php
        $deceasedAge = null;
        if ($death->birth_date && $death->death_date) {
            $deceasedAge = $death->birth_date->diffInYears($death->death_date);
        }
        $deathDateFormatted = $death->death_date ? ($death->death_day ? strtoupper($death->death_day) . ', ' : '') . $death->death_date->translatedFormat('d F Y') : '-';
        $deceasedFullAddress = $death->deceased_address ?: ('Padukuhan ' . $death->padukuhan . ', RT ' . $death->rt . ' / RW ' . $death->rw . ', Purwobinangun, Pakem, Sleman');
    @endphp

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
        <h1>SURAT KETERANGAN KEMATIAN</h1>
        <p>Nomor Registrasi: <strong>{{ $death->registration_no }}</strong></p>
    </div>

    <p style="font-size: 9pt; margin-bottom: 8px;">
        Pemerintah Kalurahan Purwobinangun dengan ini menerangkan bahwa telah tercatat pendaftaran permohonan penerbitan Akta Kematian atas nama warga di bawah ini:
    </p>

    <!-- TABEL DATA LENGKAP -->
    <table class="content-table">
        <!-- 1. DATA KELUARGA -->
        <tr class="section-header">
            <td colspan="2">I. DATA KELUARGA & PENDAFTARAN</td>
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
            <td class="val-col">{{ strtoupper($death->head_of_family_name ?: '-') }}</td>
        </tr>
        <tr>
            <td class="label-col">Tanggal Pendaftaran</td>
            <td class="val-col">{{ $death->created_at ? $death->created_at->translatedFormat('d F Y H:i') : '-' }} WIB</td>
        </tr>

        <!-- 2. DATA JENAZAH -->
        <tr class="section-header">
            <td colspan="2">II. DATA JENAZAH (YANG MENINGGAL DUNIA)</td>
        </tr>
        <tr>
            <td class="label-col">Nama Lengkap Jenazah</td>
            <td class="val-col">{{ strtoupper($death->deceased_name) }}</td>
        </tr>
        <tr>
            <td class="label-col">NIK Jenazah</td>
            <td class="val-col">{{ $death->deceased_nik }}</td>
        </tr>
        <tr>
            <td class="label-col">Jenis Kelamin</td>
            <td class="val-col">{{ $death->gender === 'L' ? 'LAKI-LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-') }}</td>
        </tr>
        <tr>
            <td class="label-col">Tempat & Tanggal Lahir / Umur</td>
            <td class="val-col">{{ strtoupper($death->deceased_birth_place ?: 'Sleman') }}, {{ $death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-' }} {{ $deceasedAge !== null ? "({$deceasedAge} Tahun)" : '' }}</td>
        </tr>
        <tr>
            <td class="label-col">Agama & Pekerjaan</td>
            <td class="val-col">{{ strtoupper($death->religion ?: '-') }} &mdash; {{ strtoupper($death->deceased_job ?: '-') }}</td>
        </tr>
        <tr>
            <td class="label-col">Alamat Jenazah</td>
            <td class="val-col">{{ $deceasedFullAddress }}</td>
        </tr>

        <!-- 3. RINCIAN PERISTIWA KEMATIAN -->
        <tr class="section-header">
            <td colspan="2">III. RINCIAN PERISTIWA KEMATIAN</td>
        </tr>
        <tr>
            <td class="label-col">Hari & Tanggal Kematian</td>
            <td class="val-col">{{ $deathDateFormatted }}</td>
        </tr>
        <tr>
            <td class="label-col">Waktu / Jam Kematian</td>
            <td class="val-col">{{ $death->death_time ? $death->death_time . ' WIB' : '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Tempat Kematian</td>
            <td class="val-col">{{ strtoupper($death->death_place ?: 'RUMAH') }} {{ $death->death_location_detail ? "({$death->death_location_detail})" : '' }}</td>
        </tr>
        <tr>
            <td class="label-col">Sebab Kematian</td>
            <td class="val-col">{{ strtoupper($death->cause_of_death ?: 'SAKIT BIASA / TUA') }}</td>
        </tr>
        <tr>
            <td class="label-col">Yang Menerangkan Kematian</td>
            <td class="val-col">{{ $death->reported_by_title ?: 'Dokter / Rumah Sakit / RT' }}</td>
        </tr>

        <!-- 4. DATA ORANG TUA JENAZAH -->
        <tr class="section-header">
            <td colspan="2">IV. DATA ORANG TUA JENAZAH</td>
        </tr>
        <tr>
            <td class="label-col">Nama Ayah / NIK</td>
            <td class="val-col">{{ strtoupper($death->father_name ?: '-') }} / {{ $death->father_nik ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Nama Ibu Kandung / NIK</td>
            <td class="val-col">{{ strtoupper($death->mother_name ?: '-') }} / {{ $death->mother_nik ?: '-' }}</td>
        </tr>

        <!-- 5. DATA PELAPOR & SAKSI -->
        <tr class="section-header">
            <td colspan="2">V. DATA PELAPOR & SAKSI</td>
        </tr>
        <tr>
            <td class="label-col">Pelapor (Nama / NIK / Hubungan)</td>
            <td class="val-col">{{ strtoupper($death->applicant_name ?: '-') }} / {{ $death->applicant_nik ?: '-' }} ({{ $death->applicant_relation ?: 'Ahli Waris / Keluarga' }})</td>
        </tr>
        <tr>
            <td class="label-col">Saksi I (Nama / NIK)</td>
            <td class="val-col">{{ $death->witness1_name ?: ($death->witness_name ?: '-') }} / {{ $death->witness1_nik ?: ($death->witness_nik ?: '-') }}</td>
        </tr>
        <tr>
            <td class="label-col">Saksi II (Nama / NIK)</td>
            <td class="val-col">{{ $death->witness2_name ?: '-' }} / {{ $death->witness2_nik ?: '-' }}</td>
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
                Purwobinangun, {{ $death->created_at ? $death->created_at->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Lurah Purwobinangun,</strong>
                <div class="sig-space"></div>
                <u><strong>( R. HADI PURWANTO )</strong></u>
            </td>
        </tr>
    </table>

</body>
</html>
