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
    
    <!-- html2pdf for direct A4 PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

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
            border-collapse: collapse;
        }
        .data-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 10.5pt;
            font-family: 'Times New Roman', Times, serif;
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
            <button onclick="downloadPDF()" id="btn-download-pdf" class="font-sans-ui bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs px-3 py-2 rounded-lg shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-download"></i> Unduh Langsung
            </button>
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

        // Hitung Umur Ibu & Ayah
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

    <div id="letter-content">

        <!-- =========================================================================
             HALAMAN 1: KOP SURAT, JUDUL, DATA KEPALA KELUARGA & SECTION JENAZAH
        ========================================================================== -->
        <div class="print-page">
            
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
                        <td class="w-56">Nama Kepala Keluarga</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->head_of_family_name ?: '-' }}</td>
                    </tr>
                    <tr>
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
                        <td>
                            <div class="flex items-center justify-between pr-8">
                                <span>{{ $genderText }}</span>
                                <span>Anak ke &nbsp; {{ $death->deceased_child_order ?: '-' }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Tempat dan Tanggal Lahir /<br>Umur</td>
                        <td class="w-4">:</td>
                        <td>
                            <div class="flex items-center justify-between pr-8">
                                <span class="uppercase">
                                    {{ $death->deceased_birth_place ?: 'SLEMAN' }}, {{ $death->birth_date ? $death->birth_date->format('d - m - Y') : '-' }}
                                </span>
                                <span>Umur {{ $deceasedAge !== null ? $deceasedAge . ' th' : '-' }}</span>
                            </div>
                        </td>
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
                        <td>{{ $death->mother_nik ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->mother_name ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir /<br>Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $motherBirth }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->mother_address ?: '' }}</td>
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
                        <td>{{ $death->father_nik ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->father_name ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir /<br>Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $fatherBirth }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->father_address ?: '' }}</td>
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
                        <td>{{ $death->applicant_nik ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->applicant_name ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir /<br>Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $applicantBirth }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->applicant_address ?: '' }}</td>
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
                        <td>{{ $death->witness1_nik ?: ($death->witness_nik ?: '') }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->witness1_name ?: ($death->witness_name ?: '') }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir /<br>Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $witness1Birth }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->witness1_address ?: '' }}</td>
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
                        <td>{{ $death->witness2_nik ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">2</td>
                        <td class="w-56">Nama Lengkap</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->witness2_name ?: '' }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">3</td>
                        <td class="w-56">Tempat dan Tanggal Lahir /<br>Umur</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $witness2Birth }}</td>
                    </tr>
                    <tr>
                        <td class="w-7">4</td>
                        <td class="w-56">Alamat</td>
                        <td class="w-4">:</td>
                        <td class="uppercase">{{ $death->witness2_address ?: '' }}</td>
                    </tr>
                </table>
            </div>

            <!-- 6. TANDA TANGAN (SIGNATURE BLOCK) -->
            <div class="pt-2 flex justify-end">
                <div class="text-left w-72 pr-4">
                    <p>Sleman, {{ $reportDateFormatted }}</p>
                    <p class="mt-0.5">Mengetahui</p>
                    <p>a.n Lurah</p>
                    <p>Kaur Tata Laksana</p>
                    
                    <!-- Ruang tanda tangan & cap stempel -->
                    <div class="h-20"></div>

                    <p class="font-medium text-[10pt]">
                        {{ $officerName }}
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
            const safeName = "{{ preg_replace('/[^A-Za-z0-9]/', '_', $death->deceased_name ?: 'Jenazah') }}";
            const regNo = "{{ $death->registration_no }}";

            const opt = {
                margin: [0, 0, 0, 0],
                filename: `Surat_Keterangan_Kematian_${safeName}_${regNo}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, letterRendering: true, scrollY: 0 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'] }
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
