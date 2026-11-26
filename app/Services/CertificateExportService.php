<?php

namespace App\Services;

use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateExportService
{
    /**
     * Export certificate to specified format (xlsx, docx, pdf).
     */
    public function export(string $type, Model $certificate, string $format)
    {
        $format = strtolower($format);

        if (!in_array($format, ['xlsx', 'docx', 'pdf'])) {
            abort(400, 'Format ekspor tidak didukung. Pilihan format yang tersedia: xlsx, docx, pdf.');
        }

        if ($type === 'birth' && $certificate instanceof BirthCertificate) {
            return match ($format) {
                'pdf'  => $this->exportBirthPdf($certificate),
                'docx' => $this->exportBirthDocx($certificate),
                'xlsx' => $this->exportBirthXlsx($certificate),
            };
        }

        if ($type === 'death' && $certificate instanceof DeathCertificate) {
            return match ($format) {
                'pdf'  => $this->exportDeathPdf($certificate),
                'docx' => $this->exportDeathDocx($certificate),
                'xlsx' => $this->exportDeathXlsx($certificate),
            };
        }

        abort(404, 'Tipe sertifikat tidak valid.');
    }

    /* =========================================================================
     * BIRTH CERTIFICATE EXPORTS (Kelahiran)
     * ========================================================================= */

    public function exportBirthPdf(BirthCertificate $birth)
    {
        $logoBase64 = $this->getLogoBase64();
        $filename = 'Surat_Kelahiran_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $birth->registration_no) . '.pdf';

        $pdf = Pdf::loadView('exports.pdf.birth', [
            'birth' => $birth,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'portrait')->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        return $pdf->download($filename);
    }

    public function exportBirthDocx(BirthCertificate $birth): StreamedResponse
    {
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop'    => 1100,
            'marginBottom' => 1100,
            'marginLeft'   => 1200,
            'marginRight'  => 1200,
        ]);

        // Kop Surat
        $this->addDocxKopSurat($section);

        // Judul Dokumen
        $section->addText('SURAT KETERANGAN KELAHIRAN', [
            'name' => 'Calibri', 'size' => 14, 'bold' => true, 'underline' => 'single',
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);

        $section->addText('Nomor Registrasi: ' . $birth->registration_no, [
            'name' => 'Calibri', 'size' => 10, 'italic' => true,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);

        $section->addText('Pemerintah Kalurahan Purwobinangun dengan ini menerangkan bahwa telah tercatat pelaporan peristiwa kelahiran warga dengan rincian data sebagai berikut:', [
            'size' => 10.5,
        ], ['spaceAfter' => 140]);

        // Tabel Data
        $tableStyle = [
            'borderColor' => 'D0D5DD',
            'borderSize'  => 6,
            'cellMargin'  => 80,
        ];
        $phpWord->addTableStyle('BirthTable', $tableStyle);
        $table = $section->addTable('BirthTable');

        $rows = [
            ['I. DATA KELUARGA & PENDAFTARAN', '', true],
            ['Nomor Registrasi', $birth->registration_no],
            ['Nomor Kartu Keluarga (KK)', $birth->family_card_no ?: '-'],
            ['Tanggal Pengajuan', $birth->created_at ? $birth->created_at->translatedFormat('d F Y') : '-'],

            ['II. DATA ANAK / BAYI', '', true],
            ['Nama Lengkap Anak', strtoupper($birth->child_name)],
            ['NIK Anak (jika ada)', $birth->child_nik ?: '-'],
            ['Jenis Kelamin', $birth->gender === 'L' ? 'LAKI-LAKI' : ($birth->gender === 'P' ? 'PEREMPUAN' : '-')],
            ['Tempat Lahir', strtoupper($birth->birth_place ?: 'Sleman')],
            ['Hari & Tanggal Lahir', ($birth->birth_day ? strtoupper($birth->birth_day) . ', ' : '') . ($birth->birth_date ? Carbon::parse($birth->birth_date)->translatedFormat('d F Y') : '-')],
            ['Waktu / Jam Lahir', $birth->birth_time ? $birth->birth_time . ' WIB' : '-'],
            ['Anak Ke / Jenis Kelahiran', ($birth->birth_order ? 'Anak ke-' . $birth->birth_order : '-') . ' / ' . ($birth->birth_type ?: 'Tunggal')],
            ['Berat & Panjang Bayi', ($birth->weight_kg ?: '-') . ' kg (' . ($birth->weight_gram ?: '-') . ' gram) / ' . ($birth->length_cm ?: '-') . ' cm'],
            ['Tempat Bersalin / Penolong', ($birth->birth_place_type ?: '-') . ' / ' . ($birth->birth_helper ?: '-')],

            ['III. DATA ORANG TUA', '', true],
            ['Nama Lengkap Ayah', strtoupper($birth->father_name ?: '-')],
            ['NIK Ayah', $birth->father_nik ?: '-'],
            ['Pekerjaan / Alamat Ayah', ($birth->father_job ?: '-') . ' / ' . ($birth->father_address ?: '-')],
            ['Nama Lengkap Ibu Kandung', strtoupper($birth->mother_name ?: '-')],
            ['NIK Ibu Kandung', $birth->mother_nik ?: '-'],
            ['Pekerjaan / Alamat Ibu', ($birth->mother_job ?: '-') . ' / ' . ($birth->mother_address ?: '-')],
            ['Tanggal Pencatatan Perkawinan', $birth->marriage_date ? Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-'],

            ['IV. DATA PELAPOR & SAKSI', '', true],
            ['Nama Pelapor', strtoupper($birth->applicant_name ?: '-')],
            ['NIK Pelapor', $birth->applicant_nik ?: '-'],
            ['Hubungan dengan Bayi', $birth->applicant_relation ?: 'Orang Tua / Keluarga'],
            ['Saksi I (Nama / NIK)', ($birth->witness_1_name ?: '-') . ' / ' . ($birth->witness_1_nik ?: '-')],
            ['Saksi II (Nama / NIK)', ($birth->witness_2_name ?: '-') . ' / ' . ($birth->witness_2_nik ?: '-')],
        ];

        foreach ($rows as $item) {
            $table->addRow();
            if (!empty($item[2])) {
                $cell = $table->addCell(9000, ['gridSpan' => 2, 'bgColor' => 'E8F2F7']);
                $cell->addText($item[0], ['bold' => true, 'color' => '095B8C', 'size' => 10]);
            } else {
                $c1 = $table->addCell(3400);
                $c1->addText($item[0], ['size' => 9.5, 'color' => '333333']);
                $c2 = $table->addCell(5600);
                $c2->addText($item[1], ['size' => 9.5, 'bold' => true]);
            }
        }

        // Tanda Tangan
        $section->addTextBreak(1);
        $this->addDocxSignature($section, $birth->created_at ? $birth->created_at->translatedFormat('d F Y') : Carbon::now()->translatedFormat('d F Y'));

        $filename = 'Surat_Kelahiran_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $birth->registration_no) . '.docx';

        return response()->streamDownload(function () use ($phpWord) {
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function exportBirthXlsx(BirthCertificate $birth): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Surat Kelahiran');

        // Header Kalurahan
        $sheet->setCellValue('A1', 'PEMERINTAH KABUPATEN SLEMAN - KAPANEWON PAKEM - KALURAHAN PURWOBINANGUN');
        $sheet->setCellValue('A2', 'DATA SURAT KETERANGAN & FORMULIR PELAPORAN KELAHIRAN');
        $sheet->setCellValue('A3', 'Nomor Registrasi: ' . $birth->registration_no . ' | Tanggal Cetak: ' . Carbon::now()->translatedFormat('d F Y H:i') . ' WIB');

        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('A3:C3');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '095B8C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059CB8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9.5],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Table Header
        $sheet->setCellValue('A5', 'NO');
        $sheet->setCellValue('B5', 'PARAMETER / BIDANG DATA');
        $sheet->setCellValue('C5', 'NILAI DATA / KETERANGAN');

        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

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
        $applicantFullAddr = $formatFullAddress($birth->address, $birth->rt, $birth->rw, $birth->applicant_village, $birth->applicant_district, $birth->applicant_city, $birth->applicant_province, $birth->padukuhan);
        $witness1FullAddr = $formatFullAddress($birth->witness_1_address, $birth->witness_1_rt, $birth->witness_1_rw, $birth->witness_1_village, $birth->witness_1_district, $birth->witness_1_city, $birth->witness_1_province);
        $witness2FullAddr = $formatFullAddress($birth->witness_2_address, $birth->witness_2_rt, $birth->witness_2_rw, $birth->witness_2_village, $birth->witness_2_district, $birth->witness_2_city, $birth->witness_2_province);

        $fatherAgeVal = $birth->father_age ?: ($birth->father_birth_date ? $birth->father_birth_date->diffInYears($birth->birth_date ?? now()) : null);
        $motherAgeVal = $birth->mother_age ?: ($birth->mother_birth_date ? $birth->mother_birth_date->diffInYears($birth->birth_date ?? now()) : null);
        $applicantAgeVal = $birth->applicant_age ?: ($birth->applicant_birth_date ? $birth->applicant_birth_date->diffInYears(now()) : null);

        $weightStr = ($birth->weight_kg !== null && $birth->weight_kg !== '')
            ? $birth->weight_kg . ' kg' . ($birth->weight_gram ? ' (' . $birth->weight_gram . ' gram)' : '')
            : ($birth->weight_gram ? $birth->weight_gram . ' gram' : '-');

        $dataRows = [
            ['section' => 'I. DATA KELUARGA & PENDAFTARAN'],
            ['1', 'Nomor Registrasi', $birth->registration_no],
            ['2', 'Nomor Kartu Keluarga (KK)', $birth->family_card_no ?: '-'],
            ['3', 'Status Permohonan', $birth->status_label],
            ['4', 'Tanggal Pendaftaran', $birth->created_at ? $birth->created_at->translatedFormat('d F Y H:i') . ' WIB' : '-'],

            ['section' => 'II. DATA ANAK / BAYI'],
            ['5', 'Nama Lengkap Anak', strtoupper($birth->child_name ?: '-')],
            ['6', 'NIK Anak', $birth->child_nik ?: '-'],
            ['7', 'Jenis Kelamin', $birth->gender === 'L' ? 'LAKI-LAKI' : ($birth->gender === 'P' ? 'PEREMPUAN' : '-')],
            ['8', 'Tempat Lahir', strtoupper($birth->birth_place ?: 'SLEMAN')],
            ['9', 'Hari, Tanggal Lahir', ($birth->birth_day ? strtoupper($birth->birth_day) . ', ' : '') . ($birth->birth_date ? Carbon::parse($birth->birth_date)->translatedFormat('d F Y') : '-')],
            ['10', 'Pukul / Jam Lahir', $birth->birth_time ? $birth->birth_time . ' WIB' : '-'],
            ['11', 'Anak Ke', (string) ($birth->birth_order ?: '1')],
            ['12', 'Jenis Kelahiran', $birth->birth_type ?: 'Tunggal'],
            ['13', 'Penolong Kelahiran', $birth->birth_helper ?: '-'],
            ['14', 'Tempat Dilahirkan', $birth->birth_place_type ?: '-'],
            ['15', 'Berat Lahir', $weightStr],
            ['16', 'Panjang Lahir', $birth->length_cm ? $birth->length_cm . ' cm' : '-'],

            ['section' => 'III. DATA ORANG TUA KANDUNG'],
            ['17', 'NIK Ayah', $birth->father_nik ?: '-'],
            ['18', 'Nama Lengkap Ayah', strtoupper($birth->father_name ?: '-')],
            ['19', 'Tanggal Lahir / Umur Ayah', ($birth->father_birth_date ? $birth->father_birth_date->translatedFormat('d F Y') : '-') . ($fatherAgeVal !== null ? " ({$fatherAgeVal} Tahun)" : '')],
            ['20', 'Pekerjaan Ayah', $birth->father_job ?: '-'],
            ['21', 'Kewarganegaraan Ayah', ($birth->father_citizenship ?: 'WNI') . ($birth->father_nationality ? ' (' . $birth->father_nationality . ')' : '')],
            ['22', 'Alamat Ayah', $fatherFullAddr],
            ['23', 'NIK Ibu Kandung', $birth->mother_nik ?: '-'],
            ['24', 'Nama Lengkap Ibu Kandung', strtoupper($birth->mother_name ?: '-')],
            ['25', 'Tanggal Lahir / Umur Ibu', ($birth->mother_birth_date ? $birth->mother_birth_date->translatedFormat('d F Y') : '-') . ($motherAgeVal !== null ? " ({$motherAgeVal} Tahun)" : '')],
            ['26', 'Pekerjaan Ibu', $birth->mother_job ?: '-'],
            ['27', 'Kewarganegaraan Ibu', ($birth->mother_citizenship ?: 'WNI') . ($birth->mother_nationality ? ' (' . $birth->mother_nationality . ')' : '')],
            ['28', 'Alamat Ibu', $motherFullAddr],
            ['29', 'Tanggal Pencatatan Perkawinan', $birth->marriage_date ? Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-'],

            ['section' => 'IV. DATA PELAPOR & SAKSI'],
            ['30', 'NIK Pelapor', $birth->applicant_nik ?: '-'],
            ['31', 'Nama Lengkap Pelapor', strtoupper($birth->applicant_name ?: '-')],
            ['32', 'Tanggal Lahir / Umur Pelapor', ($birth->applicant_birth_date ? $birth->applicant_birth_date->translatedFormat('d F Y') : '-') . ($applicantAgeVal !== null ? " ({$applicantAgeVal} Tahun)" : '')],
            ['33', 'Pekerjaan Pelapor', $birth->applicant_job ?: '-'],
            ['34', 'Hubungan dengan Bayi', $birth->applicant_relation ?: 'Keluarga'],
            ['35', 'Telepon / WhatsApp Pelapor', $birth->applicant_phone ?: '-'],
            ['36', 'Alamat Pelapor', $applicantFullAddr],
            ['37', 'Saksi I (NIK / Nama)', ($birth->witness_1_nik ?: '-') . ' / ' . strtoupper($birth->witness_1_name ?: '-')],
            ['38', 'Pekerjaan & Alamat Saksi I', ($birth->witness_1_job ?: '-') . ' — ' . $witness1FullAddr],
            ['39', 'Saksi II (NIK / Nama)', ($birth->witness_2_nik ?: '-') . ' / ' . strtoupper($birth->witness_2_name ?: '-')],
            ['40', 'Pekerjaan & Alamat Saksi II', ($birth->witness_2_job ?: '-') . ' — ' . $witness2FullAddr],
            ['41', 'Petugas Pemroses', $birth->processed_by ?: '-'],
            ['42', 'Catatan Verifikator', $birth->rejection_note ?: '-'],
        ];

        $currentRow = 6;
        foreach ($dataRows as $r) {
            if (isset($r['section'])) {
                $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
                $sheet->setCellValueExplicit("A{$currentRow}", $r['section'], DataType::TYPE_STRING);
                $sheet->getStyle("A{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '095B8C']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F2F7']],
                ]);
            } else {
                $sheet->setCellValueExplicit("A{$currentRow}", (string) $r[0], DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("B{$currentRow}", (string) $r[1], DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C{$currentRow}", (string) ($r[2] ?? '-'), DataType::TYPE_STRING);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getStyle("C{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getStyle("C{$currentRow}")->getFont()->setBold(true);
            }
            $currentRow++;
        }

        // Border
        $sheet->getStyle("A5:C" . ($currentRow - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(56);

        $filename = 'Surat_Kelahiran_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $birth->registration_no) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /* =========================================================================
     * DEATH CERTIFICATE EXPORTS (Kematian)
     * ========================================================================= */

    public function exportDeathPdf(DeathCertificate $death)
    {
        $logoBase64 = $this->getLogoBase64();
        $aksaraBase64 = $this->getAksaraBase64();
        $filename = 'Surat_Kematian_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $death->registration_no) . '.pdf';

        $pdf = Pdf::loadView('exports.pdf.death', [
            'death' => $death,
            'logoBase64' => $logoBase64,
            'aksaraBase64' => $aksaraBase64,
        ])->setPaper('a4', 'portrait')->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'times-roman',
        ]);

        return $pdf->download($filename);
    }

    public function exportDeathDocx(DeathCertificate $death): StreamedResponse
    {
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10.5);

        $section = $phpWord->addSection([
            'marginTop'    => 1100,
            'marginBottom' => 1100,
            'marginLeft'   => 1200,
            'marginRight'  => 1200,
        ]);

        // Kop Surat Resmi Kalurahan Purwobinangun
        $this->addDocxDeathKopSurat($section);

        $romans = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $monthNum = (int)($death->death_date ? $death->death_date->format('n') : date('n'));
        $monthRoman = $romans[$monthNum] ?? 'IX';
        $letterYear = $death->death_date ? $death->death_date->format('Y') : date('Y');
        $letterNumberSeq = (int)substr($death->registration_no, -4) ?: '82';

        // Judul Dokumen
        $section->addText('SURAT KETERANGAN KEMATIAN', [
            'name' => 'Times New Roman', 'size' => 14, 'bold' => true,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);

        $section->addText("No. 474.3/ {$letterNumberSeq} /{$monthRoman}/{$letterYear}", [
            'name' => 'Times New Roman', 'size' => 10.5, 'bold' => true,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 140]);

        // Data Kepala Keluarga & Nomor KK
        $borderlessTableStyle = [
            'borderColor' => 'FFFFFF',
            'borderSize'  => 0,
            'cellMargin'  => 20,
        ];
        $phpWord->addTableStyle('BorderlessKK', $borderlessTableStyle);
        $kkTable = $section->addTable('BorderlessKK');

        $kkTable->addRow();
        $kkTable->addCell(3400)->addText('Nama Kepala Keluarga', ['size' => 10, 'name' => 'Times New Roman']);
        $kkTable->addCell(200)->addText(':', ['size' => 10, 'name' => 'Times New Roman']);
        $kkTable->addCell(5400)->addText(strtoupper($death->head_of_family_name ?: '-'), ['size' => 10, 'name' => 'Times New Roman']);

        $kkTable->addRow();
        $kkTable->addCell(3400)->addText('Nomor Kartu Keluarga', ['size' => 10, 'name' => 'Times New Roman']);
        $kkTable->addCell(200)->addText(':', ['size' => 10, 'name' => 'Times New Roman']);
        $kkTable->addCell(5400)->addText($death->family_card_no ?: '-', ['size' => 10, 'name' => 'Times New Roman']);

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

        // Section JENAZAH
        $section->addText('JENAZAH', ['size' => 10.5, 'bold' => true, 'name' => 'Times New Roman'], ['spaceBefore' => 100, 'spaceAfter' => 40]);

        $phpWord->addTableStyle('BorderlessJenazah', $borderlessTableStyle);
        $jTable = $section->addTable('BorderlessJenazah');

        $addDocxRow = function($table, $num, $label, $val, $valRight = null) {
            $table->addRow();
            $table->addCell(350)->addText($num, ['size' => 10, 'name' => 'Times New Roman']);
            $table->addCell(3050)->addText($label, ['size' => 10, 'name' => 'Times New Roman']);
            $table->addCell(200)->addText(':', ['size' => 10, 'name' => 'Times New Roman']);
            if ($valRight !== null) {
                $table->addCell(3400)->addText($val, ['size' => 10, 'name' => 'Times New Roman']);
                $table->addCell(2000)->addText($valRight, ['size' => 10, 'name' => 'Times New Roman'], ['alignment' => Jc::RIGHT]);
            } else {
                $table->addCell(5400, ['gridSpan' => 2])->addText($val, ['size' => 10, 'name' => 'Times New Roman']);
            }
        };

        $addDocxRow($jTable, '1', 'NIK', $death->deceased_nik ?: '-');
        $addDocxRow($jTable, '2', 'Nama Lengkap', strtoupper($death->deceased_name ?: '-'));
        $addDocxRow($jTable, '3', 'Jenis Kelamin / Anak ke', $genderText, 'Anak ke   ' . ($death->deceased_child_order ?: '-'));
        $deceasedBirthStr = strtoupper($death->deceased_birth_place ?: 'SLEMAN') . ', ' . ($death->birth_date ? $death->birth_date->format('d - m - Y') : '-');
        $addDocxRow($jTable, '4', "Tempat dan Tanggal Lahir /\nUmur", $deceasedBirthStr, 'Umur ' . ($deceasedAge !== null ? $deceasedAge . ' th' : '-'));
        $addDocxRow($jTable, '5', 'Agama', strtoupper($death->religion ?: '-'));
        $addDocxRow($jTable, '6', 'Pekerjaan', strtoupper($death->deceased_job ?: '-'));
        $addDocxRow($jTable, '7', 'Alamat', strtoupper($deceasedFullAddress));
        $deathDateStr = $death->death_date ? $death->death_date->format('d - m - Y') : '-';
        $deathTimeStr = 'Jam: ' . ($death->death_time ? $death->death_time . ' WIB' : '-');
        $addDocxRow($jTable, '8', 'Tanggal dan Jam Kematian', $deathDateStr, $deathTimeStr);
        $addDocxRow($jTable, '9', 'Sebab Kematian', strtoupper($death->cause_of_death ?: '-'));
        $deathPlaceStr = strtoupper($death->death_place ?: '-') . ($death->death_location_detail ? ' (' . strtoupper($death->death_location_detail) . ')' : '');
        $addDocxRow($jTable, '10', 'Tempat Kematian', $deathPlaceStr);
        $addDocxRow($jTable, '11', 'Yang Menerangkan', strtoupper($death->reported_by_title ?: 'LAINNYA'));

        // ========================
        // HALAMAN 2
        // ========================
        $section->addPageBreak();

        $addSectionBlock = function($title, $nik, $name, $birth, $address, $spaceBefore = 80) use ($section, $addDocxRow, $borderlessTableStyle) {
            $section->addText($title, ['size' => 10.5, 'bold' => true, 'name' => 'Times New Roman'], ['spaceBefore' => $spaceBefore, 'spaceAfter' => 40]);
            $table = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 0, 'cellMargin' => 20]);
            $addDocxRow($table, '1', 'NIK', $nik ?: '');
            $addDocxRow($table, '2', 'Nama Lengkap', strtoupper($name ?: ''));
            $addDocxRow($table, '3', "Tempat dan Tanggal Lahir /\nUmur", strtoupper($birth ?: ''));
            $addDocxRow($table, '4', 'Alamat', strtoupper($address ?: ''));
        };

        // IBU
        $addSectionBlock('IBU', $death->mother_nik, $death->mother_name, $motherBirth, $death->mother_address, 0);

        // AYAH
        $addSectionBlock('AYAH', $death->father_nik, $death->father_name, $fatherBirth, $death->father_address);

        // PELAPOR
        $addSectionBlock('PELAPOR', $death->applicant_nik, $death->applicant_name, $applicantBirth, $death->applicant_address);

        // SAKSI 1
        $witness1Nik = $death->witness1_nik ?: ($death->witness_nik ?: '');
        $witness1Name = $death->witness1_name ?: ($death->witness_name ?: '');
        $addSectionBlock('SAKSI 1', $witness1Nik, $witness1Name, $witness1Birth, $death->witness1_address);

        // SAKSI 2
        $addSectionBlock('SAKSI 2', $death->witness2_nik, $death->witness2_name, $witness2Birth, $death->witness2_address);

        // Tanda Tangan
        $section->addTextBreak(1);
        $this->addDocxDeathSignature($section, $reportDateFormatted, $officerName);

        $filename = 'Surat_Kematian_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $death->registration_no) . '.docx';

        return response()->streamDownload(function () use ($phpWord) {
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function exportDeathXlsx(DeathCertificate $death): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Surat Kematian');

        $sheet->setCellValue('A1', 'PEMERINTAH KABUPATEN SLEMAN - KAPANEWON PAKEM - KALURAHAN PURWOBINANGUN');
        $sheet->setCellValue('A2', 'DATA SURAT KETERANGAN & FORMULIR PELAPORAN KEMATIAN');
        $sheet->setCellValue('A3', 'Nomor Registrasi: ' . $death->registration_no . ' | Tanggal Cetak: ' . Carbon::now()->translatedFormat('d F Y H:i') . ' WIB');

        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('A3:C3');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '095B8C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BE123C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9.5],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A5', 'NO');
        $sheet->setCellValue('B5', 'PARAMETER / BIDANG DATA');
        $sheet->setCellValue('C5', 'NILAI DATA / KETERANGAN');

        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $romans = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $monthNum = (int) ($death->death_date ? $death->death_date->format('n') : date('n'));
        $monthRoman = $romans[$monthNum] ?? 'IX';
        $letterYear = $death->death_date ? $death->death_date->format('Y') : date('Y');
        $letterNumberSeq = (int) substr($death->registration_no, -4) ?: '82';
        $officialLetterNo = "474.3/ {$letterNumberSeq} /{$monthRoman}/{$letterYear}";

        $deceasedAge = null;
        if ($death->birth_date && $death->death_date) {
            $deceasedAge = $death->birth_date->diffInYears($death->death_date);
        } elseif ($death->birth_date) {
            $deceasedAge = $death->birth_date->diffInYears(now());
        }

        $motherAge = $death->mother_birth_date ? $death->mother_birth_date->diffInYears($death->death_date ?? now()) : null;
        $fatherAge = $death->father_birth_date ? $death->father_birth_date->diffInYears($death->death_date ?? now()) : null;

        $deathDateFormatted = $death->death_date ? ($death->death_day ? strtoupper($death->death_day) . ', ' : '') . $death->death_date->translatedFormat('d F Y') : '-';
        $deceasedFullAddress = $death->deceased_address ?: ('Padukuhan ' . ($death->padukuhan ?: '-') . ', RT ' . ($death->rt ?: '-') . ' / RW ' . ($death->rw ?: '-') . ', Purwobinangun, Pakem, Sleman');

        $formatBirthInfo = function ($place, $date, $age): string {
            $parts = [];
            if ($place) {
                $parts[] = strtoupper($place);
            }
            if ($date) {
                $parts[] = $date->translatedFormat('d F Y');
            }
            $str = !empty($parts) ? implode(', ', $parts) : '-';
            if ($age !== null && $age !== '') {
                $str .= " ({$age} Tahun)";
            }
            return $str;
        };

        $witness1Nik = $death->witness1_nik ?: ($death->witness_nik ?: '-');
        $witness1Name = $death->witness1_name ?: ($death->witness_name ?: '-');

        $dataRows = [
            ['section' => 'I. DATA KELUARGA & PENDAFTARAN'],
            ['1', 'Nomor Registrasi', $death->registration_no],
            ['2', 'Nomor Surat Keterangan', $officialLetterNo],
            ['3', 'Nomor Kartu Keluarga (KK)', $death->family_card_no ?: '-'],
            ['4', 'Nama Kepala Keluarga', strtoupper($death->head_of_family_name ?: '-')],
            ['5', 'Status Permohonan', $death->status_label],
            ['6', 'Tanggal Pendaftaran / Lapor', $death->report_date ? $death->report_date->translatedFormat('d F Y') : ($death->created_at ? $death->created_at->translatedFormat('d F Y H:i') . ' WIB' : '-')],

            ['section' => 'II. DATA JENAZAH (YANG MENINGGAL)'],
            ['7', 'NIK Jenazah', $death->deceased_nik ?: '-'],
            ['8', 'Nama Lengkap Jenazah', strtoupper($death->deceased_name ?: '-')],
            ['9', 'Jenis Kelamin / Anak Ke', ($death->gender === 'L' ? 'LAKI-LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-')) . ($death->deceased_child_order ? ' / Anak ke-' . $death->deceased_child_order : '')],
            ['10', 'Tempat, Tanggal Lahir / Umur', $formatBirthInfo($death->deceased_birth_place ?: 'Sleman', $death->birth_date, $deceasedAge)],
            ['11', 'Agama', strtoupper($death->religion ?: '-')],
            ['12', 'Pekerjaan', strtoupper($death->deceased_job ?: '-')],
            ['13', 'Alamat Jenazah', $deceasedFullAddress],

            ['section' => 'III. RINCIAN PERISTIWA KEMATIAN'],
            ['14', 'Hari, Tanggal Kematian', $deathDateFormatted],
            ['15', 'Pukul / Jam Kematian', $death->death_time ? $death->death_time . ' WIB' : '-'],
            ['16', 'Tempat Kematian', strtoupper($death->death_place ?: '-') . ($death->death_location_detail ? " ({$death->death_location_detail})" : '')],
            ['17', 'Sebab Kematian', strtoupper($death->cause_of_death ?: '-')],
            ['18', 'Yang Menerangkan', strtoupper($death->reported_by_title ?: '-')],

            ['section' => 'IV. DATA ORANG TUA JENAZAH'],
            ['19', 'NIK Ibu Kandung', $death->mother_nik ?: '-'],
            ['20', 'Nama Lengkap Ibu Kandung', strtoupper($death->mother_name ?: '-')],
            ['21', 'TTL / Umur Ibu Kandung', $formatBirthInfo($death->mother_birth_place, $death->mother_birth_date, $motherAge)],
            ['22', 'Pekerjaan & Alamat Ibu', ($death->mother_job ?: '-') . ' — ' . ($death->mother_address ?: '-')],
            ['23', 'NIK Ayah', $death->father_nik ?: '-'],
            ['24', 'Nama Lengkap Ayah', strtoupper($death->father_name ?: '-')],
            ['25', 'TTL / Umur Ayah', $formatBirthInfo($death->father_birth_place, $death->father_birth_date, $fatherAge)],
            ['26', 'Pekerjaan & Alamat Ayah', ($death->father_job ?: '-') . ' — ' . ($death->father_address ?: '-')],

            ['section' => 'V. DATA PELAPOR & SAKSI'],
            ['27', 'NIK Pelapor', $death->applicant_nik ?: '-'],
            ['28', 'Nama Pelapor / Ahli Waris', strtoupper($death->applicant_name ?: '-')],
            ['29', 'TTL / Umur Pelapor', $formatBirthInfo($death->applicant_birth_place, $death->applicant_birth_date, $death->applicant_age)],
            ['30', 'Pekerjaan Pelapor', $death->applicant_job ?: '-'],
            ['31', 'Hubungan Pelapor', $death->applicant_relation ?: 'Ahli Waris / Keluarga'],
            ['32', 'Telepon / WhatsApp Pelapor', $death->applicant_phone ?: '-'],
            ['33', 'Alamat Pelapor', $death->applicant_address ?: '-'],
            ['34', 'Saksi I (NIK / Nama)', $witness1Nik . ' / ' . strtoupper($witness1Name)],
            ['35', 'TTL / Umur & Alamat Saksi I', $formatBirthInfo($death->witness1_birth_place, $death->witness1_birth_date, $death->witness1_age) . ' — ' . ($death->witness1_address ?: '-')],
            ['36', 'Saksi II (NIK / Nama)', ($death->witness2_nik ?: '-') . ' / ' . strtoupper($death->witness2_name ?: '-')],
            ['37', 'TTL / Umur & Alamat Saksi II', $formatBirthInfo($death->witness2_birth_place, $death->witness2_birth_date, $death->witness2_age) . ' — ' . ($death->witness2_address ?: '-')],
            ['38', 'Petugas Penandatangan / Pemroses', ($death->officer_name ?: '-') . ($death->processed_by ? ' / Verifikator: ' . $death->processed_by : '')],
            ['39', 'Catatan Verifikator', $death->rejection_note ?: '-'],
        ];

        $currentRow = 6;
        foreach ($dataRows as $r) {
            if (isset($r['section'])) {
                $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
                $sheet->setCellValueExplicit("A{$currentRow}", $r['section'], DataType::TYPE_STRING);
                $sheet->getStyle("A{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'BE123C']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FBE8E8']],
                ]);
            } else {
                $sheet->setCellValueExplicit("A{$currentRow}", (string) $r[0], DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("B{$currentRow}", (string) $r[1], DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C{$currentRow}", (string) ($r[2] ?? '-'), DataType::TYPE_STRING);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getStyle("C{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getStyle("C{$currentRow}")->getFont()->setBold(true);
            }
            $currentRow++;
        }

        $sheet->getStyle("A5:C" . ($currentRow - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(34);
        $sheet->getColumnDimension('C')->setWidth(56);

        $filename = 'Surat_Kematian_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $death->registration_no) . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /* =========================================================================
     * HELPER METHODS
     * ========================================================================= */

    private function getLogoBase64(): string
    {
        $logoPath = public_path('images/logo-sleman.png');
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return '';
    }

    private function getAksaraBase64(): string
    {
        $aksaraPath = public_path('images/aksara-purwobinangun.jpg');
        if (file_exists($aksaraPath)) {
            $type = pathinfo($aksaraPath, PATHINFO_EXTENSION);
            $data = file_get_contents($aksaraPath);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return '';
    }

    private function addDocxKopSurat($section): void
    {
        $logoPath = public_path('images/logo-sleman.png');
        $kopTable = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 0, 'cellMargin' => 40]);
        $kopTable->addRow();

        if (file_exists($logoPath)) {
            $c1 = $kopTable->addCell(1500);
            $c1->addImage($logoPath, ['width' => 55, 'height' => 70, 'alignment' => Jc::CENTER]);
            $c2 = $kopTable->addCell(7500);
        } else {
            $c2 = $kopTable->addCell(9000);
        }

        $c2->addText('PEMERINTAH KABUPATEN SLEMAN', ['size' => 11, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
        $c2->addText('KAPANEWON PAKEM', ['size' => 11, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
        $c2->addText('PEMERINTAH KALURAHAN PURWOBINANGUN', ['size' => 13, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);
        $c2->addText('Alamat: Jl. Boyong No. 1 Purwobinangun, Pakem, Sleman, D.I. Yogyakarta 55582', ['size' => 8.5, 'italic' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 50]);

        $section->addLine(['weight' => 2, 'width' => 480, 'height' => 0, 'color' => '000000']);
        $section->addTextBreak(1);
    }

    private function addDocxSignature($section, string $dateFormatted): void
    {
        $sigTable = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 0]);
        $sigTable->addRow();

        $c1 = $sigTable->addCell(4500);
        $c1->addText('Mengetahui / Memeriksa:', ['size' => 9.5]);
        $c1->addText('Petugas Registrasi Kalurahan,', ['size' => 9.5, 'bold' => true]);
        $c1->addTextBreak(3);
        $c1->addText('( ................................................... )', ['size' => 9.5, 'underline' => 'single']);

        $c2 = $sigTable->addCell(4500);
        $c2->addText('Purwobinangun, ' . $dateFormatted, ['size' => 9.5], ['alignment' => Jc::RIGHT]);
        $c2->addText('Lurah Purwobinangun,', ['size' => 9.5, 'bold' => true], ['alignment' => Jc::RIGHT]);
        $c2->addTextBreak(3);
        $c2->addText('( R. HADI PURWANTO )', ['size' => 9.5, 'bold' => true, 'underline' => 'single'], ['alignment' => Jc::RIGHT]);
    }

    private function addDocxDeathKopSurat($section): void
    {
        $logoPath = public_path('images/logo-sleman.png');
        $aksaraPath = public_path('images/aksara-purwobinangun.jpg');
        $kopTable = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 0, 'cellMargin' => 20]);
        $kopTable->addRow();

        if (file_exists($logoPath)) {
            $c1 = $kopTable->addCell(1300);
            $c1->addImage($logoPath, ['width' => 52, 'height' => 65, 'alignment' => Jc::CENTER]);
            $c2 = $kopTable->addCell(7700);
        } else {
            $c2 = $kopTable->addCell(9000);
        }

        $c2->addText('PEMERINTAH KABUPATEN SLEMAN', ['size' => 10.5, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $c2->addText('KAPANEWON PAKEM', ['size' => 11.5, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $c2->addText('PEMERINTAH KALURAHAN PURWOBINANGUN', ['size' => 13.5, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);

        if (file_exists($aksaraPath)) {
            $c2->addImage($aksaraPath, ['height' => 12, 'width' => 170, 'alignment' => Jc::CENTER]);
        }

        $c2->addText('Alamat : Jl. Pakem - Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582', ['size' => 8, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
        $c2->addText('Laman : www.purwobinangun.slemankab.go.id    Surel : desapurwobinangun@slemankab.go.id', ['size' => 8, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);

        $section->addLine(['weight' => 2, 'width' => 480, 'height' => 0, 'color' => '000000']);
        $section->addTextBreak(1);
    }

    private function addDocxDeathSignature($section, string $dateFormatted, string $officerName): void
    {
        $sigTable = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 0]);
        $sigTable->addRow();

        $c1 = $sigTable->addCell(5200);
        $c2 = $sigTable->addCell(3800);
        $c2->addText('Sleman, ' . $dateFormatted, ['size' => 10, 'name' => 'Times New Roman'], ['spaceAfter' => 10]);
        $c2->addText('Mengetahui', ['size' => 10, 'name' => 'Times New Roman'], ['spaceAfter' => 10]);
        $c2->addText('a.n Lurah', ['size' => 10, 'name' => 'Times New Roman'], ['spaceAfter' => 10]);
        $c2->addText('Kaur Tata Laksana', ['size' => 10, 'name' => 'Times New Roman'], ['spaceAfter' => 10]);
        $c2->addTextBreak(3);
        $c2->addText($officerName, ['size' => 10, 'bold' => true, 'name' => 'Times New Roman']);
    }
}
