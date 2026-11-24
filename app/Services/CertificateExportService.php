<?php

namespace App\Services;

use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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

        $dataRows = [
            ['I. DATA KELUARGA & PENDAFTARAN', '', true],
            ['1', 'Nomor Registrasi', $birth->registration_no],
            ['2', 'Nomor Kartu Keluarga (KK)', $birth->family_card_no ?: '-'],
            ['3', 'Status Permohonan', $birth->status],
            ['4', 'Tanggal Pendaftaran', $birth->created_at ? $birth->created_at->translatedFormat('d F Y H:i') : '-'],

            ['II. DATA ANAK / BAYI', '', true],
            ['5', 'Nama Lengkap Anak', strtoupper($birth->child_name)],
            ['6', 'NIK Anak', $birth->child_nik ?: '-'],
            ['7', 'Jenis Kelamin', $birth->gender === 'L' ? 'LAKI-LAKI' : ($birth->gender === 'P' ? 'PEREMPUAN' : '-')],
            ['8', 'Tempat Lahir', strtoupper($birth->birth_place ?: 'Sleman')],
            ['9', 'Hari, Tanggal Lahir', ($birth->birth_day ? strtoupper($birth->birth_day) . ', ' : '') . ($birth->birth_date ? Carbon::parse($birth->birth_date)->translatedFormat('d F Y') : '-')],
            ['10', 'Pukul / Jam Lahir', $birth->birth_time ? $birth->birth_time . ' WIB' : '-'],
            ['11', 'Anak Ke', $birth->birth_order ?: '1'],
            ['12', 'Jenis Kelahiran', $birth->birth_type ?: 'Tunggal'],
            ['13', 'Penolong Kelahiran', $birth->birth_helper ?: '-'],
            ['14', 'Tempat Dilahirkan', $birth->birth_place_type ?: '-'],
            ['15', 'Berat Lahir', ($birth->weight_kg ?: '-') . ' kg (' . ($birth->weight_gram ?: '-') . ' gram)'],
            ['16', 'Panjang Lahir', ($birth->length_cm ?: '-') . ' cm'],

            ['III. DATA ORANG TUA', '', true],
            ['17', 'NIK Ayah', $birth->father_nik ?: '-'],
            ['18', 'Nama Ayah', strtoupper($birth->father_name ?: '-')],
            ['19', 'Pekerjaan Ayah', $birth->father_job ?: '-'],
            ['20', 'Alamat Ayah', $birth->father_address ?: '-'],
            ['21', 'NIK Ibu Kandung', $birth->mother_nik ?: '-'],
            ['22', 'Nama Ibu Kandung', strtoupper($birth->mother_name ?: '-')],
            ['23', 'Pekerjaan Ibu', $birth->mother_job ?: '-'],
            ['24', 'Alamat Ibu', $birth->mother_address ?: '-'],
            ['25', 'Tanggal Perkawinan Orang Tua', $birth->marriage_date ? Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-'],

            ['IV. DATA PELAPOR & SAKSI', '', true],
            ['26', 'NIK Pelapor', $birth->applicant_nik ?: '-'],
            ['27', 'Nama Pelapor', strtoupper($birth->applicant_name ?: '-')],
            ['28', 'Hubungan Pelapor', $birth->applicant_relation ?: 'Keluarga'],
            ['29', 'Telepon Pelapor', $birth->applicant_phone ?: '-'],
            ['30', 'Alamat Pelapor', $birth->applicant_address ?: '-'],
            ['31', 'Saksi I (NIK / Nama)', ($birth->witness_1_nik ?: '-') . ' / ' . ($birth->witness_1_name ?: '-')],
            ['32', 'Saksi II (NIK / Nama)', ($birth->witness_2_nik ?: '-') . ' / ' . ($birth->witness_2_name ?: '-')],
        ];

        $currentRow = 6;
        foreach ($dataRows as $r) {
            if (!empty($r[2])) {
                $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", $r[0]);
                $sheet->getStyle("A{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '095B8C']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F2F7']],
                ]);
            } else {
                $sheet->setCellValue("A{$currentRow}", $r[0]);
                $sheet->setCellValue("B{$currentRow}", $r[1]);
                $sheet->setCellValue("C{$currentRow}", $r[2]);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
        $filename = 'Surat_Kematian_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $death->registration_no) . '.pdf';

        $pdf = Pdf::loadView('exports.pdf.death', [
            'death' => $death,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'portrait')->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        return $pdf->download($filename);
    }

    public function exportDeathDocx(DeathCertificate $death): StreamedResponse
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
        $section->addText('SURAT KETERANGAN KEMATIAN', [
            'name' => 'Calibri', 'size' => 14, 'bold' => true, 'underline' => 'single',
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);

        $section->addText('Nomor Registrasi: ' . $death->registration_no, [
            'name' => 'Calibri', 'size' => 10, 'italic' => true,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 200]);

        $section->addText('Pemerintah Kalurahan Purwobinangun dengan ini menerangkan bahwa telah tercatat pelaporan peristiwa kematian warga dengan rincian data sebagai berikut:', [
            'size' => 10.5,
        ], ['spaceAfter' => 140]);

        $tableStyle = [
            'borderColor' => 'D0D5DD',
            'borderSize'  => 6,
            'cellMargin'  => 80,
        ];
        $phpWord->addTableStyle('DeathTable', $tableStyle);
        $table = $section->addTable('DeathTable');

        $deceasedAge = null;
        if ($death->birth_date && $death->death_date) {
            $deceasedAge = $death->birth_date->diffInYears($death->death_date);
        }

        $deathDateFormatted = $death->death_date ? ($death->death_day ? strtoupper($death->death_day) . ', ' : '') . $death->death_date->translatedFormat('d F Y') : '-';
        $deceasedFullAddress = $death->deceased_address ?: ('Padukuhan ' . $death->padukuhan . ', RT ' . $death->rt . ' / RW ' . $death->rw . ', Purwobinangun, Pakem, Sleman');

        $rows = [
            ['I. DATA KELUARGA & PENDAFTARAN', '', true],
            ['Nomor Registrasi', $death->registration_no],
            ['Nomor Kartu Keluarga (KK)', $death->family_card_no ?: '-'],
            ['Nama Kepala Keluarga', strtoupper($death->head_of_family_name ?: '-')],

            ['II. DATA JENAZAH (YANG MENINGGAL DUNIA)', '', true],
            ['Nama Lengkap', strtoupper($death->deceased_name)],
            ['NIK', $death->deceased_nik],
            ['Jenis Kelamin', $death->gender === 'L' ? 'LAKI-LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-')],
            ['Tempat, Tanggal Lahir / Umur', strtoupper($death->deceased_birth_place ?: 'Sleman') . ', ' . ($death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-') . ($deceasedAge !== null ? " ({$deceasedAge} Tahun)" : '')],
            ['Agama / Pekerjaan', strtoupper($death->religion ?: '-') . ' / ' . strtoupper($death->deceased_job ?: '-')],
            ['Alamat Terakhir', $deceasedFullAddress],

            ['III. RINCIAN PERISTIWA KEMATIAN', '', true],
            ['Hari, Tanggal Kematian', $deathDateFormatted],
            ['Waktu / Pukul', $death->death_time ? $death->death_time . ' WIB' : '-'],
            ['Tempat Kematian', strtoupper($death->death_place ?: 'RUMAH') . ($death->death_location_detail ? " ({$death->death_location_detail})" : '')],
            ['Sebab Kematian', strtoupper($death->cause_of_death ?: 'SAKIT BIASA / TUA')],
            ['Yang Menerangkan Kematian', $death->reported_by_title ?: 'Dokter / Tenaga Medis / RT'],

            ['IV. DATA ORANG TUA JENAZAH', '', true],
            ['Nama Ayah / NIK', strtoupper($death->father_name ?: '-') . ' / ' . ($death->father_nik ?: '-')],
            ['Nama Ibu Kandung / NIK', strtoupper($death->mother_name ?: '-') . ' / ' . ($death->mother_nik ?: '-')],

            ['V. DATA PELAPOR & SAKSI', '', true],
            ['Nama Pelapor / Ahli Waris', strtoupper($death->applicant_name ?: '-')],
            ['NIK Pelapor', $death->applicant_nik ?: '-'],
            ['Hubungan dengan Jenazah', $death->applicant_relation ?: 'Ahli Waris / Keluarga'],
            ['Nomor Telepon Pelapor', $death->applicant_phone ?: '-'],
            ['Saksi I (Nama / NIK)', ($death->witness1_name ?: ($death->witness_name ?: '-')) . ' / ' . ($death->witness1_nik ?: ($death->witness_nik ?: '-'))],
            ['Saksi II (Nama / NIK)', ($death->witness2_name ?: '-') . ' / ' . ($death->witness2_nik ?: '-')],
        ];

        foreach ($rows as $item) {
            $table->addRow();
            if (!empty($item[2])) {
                $cell = $table->addCell(9000, ['gridSpan' => 2, 'bgColor' => 'FBE8E8']);
                $cell->addText($item[0], ['bold' => true, 'color' => 'B91C1C', 'size' => 10]);
            } else {
                $c1 = $table->addCell(3400);
                $c1->addText($item[0], ['size' => 9.5, 'color' => '333333']);
                $c2 = $table->addCell(5600);
                $c2->addText($item[1], ['size' => 9.5, 'bold' => true]);
            }
        }

        $section->addTextBreak(1);
        $this->addDocxSignature($section, $death->created_at ? $death->created_at->translatedFormat('d F Y') : Carbon::now()->translatedFormat('d F Y'));

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

        $deceasedAge = null;
        if ($death->birth_date && $death->death_date) {
            $deceasedAge = $death->birth_date->diffInYears($death->death_date);
        }

        $deathDateFormatted = $death->death_date ? ($death->death_day ? strtoupper($death->death_day) . ', ' : '') . $death->death_date->translatedFormat('d F Y') : '-';
        $deceasedFullAddress = $death->deceased_address ?: ('Padukuhan ' . $death->padukuhan . ', RT ' . $death->rt . ' / RW ' . $death->rw . ', Purwobinangun, Pakem, Sleman');

        $dataRows = [
            ['I. DATA KELUARGA & PENDAFTARAN', '', true],
            ['1', 'Nomor Registrasi', $death->registration_no],
            ['2', 'Nomor Kartu Keluarga (KK)', $death->family_card_no ?: '-'],
            ['3', 'Nama Kepala Keluarga', strtoupper($death->head_of_family_name ?: '-')],
            ['4', 'Status Permohonan', $death->status],
            ['5', 'Tanggal Pendaftaran', $death->created_at ? $death->created_at->translatedFormat('d F Y H:i') : '-'],

            ['II. DATA JENAZAH (YANG MENINGGAL)', '', true],
            ['6', 'NIK Jenazah', $death->deceased_nik],
            ['7', 'Nama Lengkap Jenazah', strtoupper($death->deceased_name)],
            ['8', 'Jenis Kelamin', $death->gender === 'L' ? 'LAKI-LAKI' : ($death->gender === 'P' ? 'PEREMPUAN' : '-')],
            ['9', 'Tempat Lahir', strtoupper($death->deceased_birth_place ?: 'Sleman')],
            ['10', 'Tanggal Lahir / Umur', ($death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-') . ($deceasedAge !== null ? " ({$deceasedAge} Tahun)" : '')],
            ['11', 'Agama', strtoupper($death->religion ?: '-')],
            ['12', 'Pekerjaan', strtoupper($death->deceased_job ?: '-')],
            ['13', 'Alamat Jenazah', $deceasedFullAddress],

            ['III. RINCIAN PERISTIWA KEMATIAN', '', true],
            ['14', 'Hari, Tanggal Kematian', $deathDateFormatted],
            ['15', 'Pukul / Jam Kematian', $death->death_time ? $death->death_time . ' WIB' : '-'],
            ['16', 'Tempat Kematian', strtoupper($death->death_place ?: 'RUMAH') . ($death->death_location_detail ? " ({$death->death_location_detail})" : '')],
            ['17', 'Sebab Kematian', strtoupper($death->cause_of_death ?: 'SAKIT BIASA / TUA')],
            ['18', 'Penerang Kematian', $death->reported_by_title ?: '-'],

            ['IV. DATA ORANG TUA JENAZAH', '', true],
            ['19', 'NIK Ayah', $death->father_nik ?: '-'],
            ['20', 'Nama Ayah', strtoupper($death->father_name ?: '-')],
            ['21', 'NIK Ibu Kandung', $death->mother_nik ?: '-'],
            ['22', 'Nama Ibu Kandung', strtoupper($death->mother_name ?: '-')],

            ['V. DATA PELAPOR & SAKSI', '', true],
            ['23', 'NIK Pelapor', $death->applicant_nik ?: '-'],
            ['24', 'Nama Pelapor / Ahli Waris', strtoupper($death->applicant_name ?: '-')],
            ['25', 'Hubungan Pelapor', $death->applicant_relation ?: 'Ahli Waris / Keluarga'],
            ['26', 'Telepon Pelapor', $death->applicant_phone ?: '-'],
            ['27', 'Alamat Pelapor', $death->applicant_address ?: '-'],
            ['28', 'Saksi I (NIK / Nama)', ($death->witness1_nik ?: ($death->witness_nik ?: '-')) . ' / ' . ($death->witness1_name ?: ($death->witness_name ?: '-'))],
            ['29', 'Saksi II (NIK / Nama)', ($death->witness2_nik ?: '-') . ' / ' . ($death->witness2_name ?: '-')],
        ];

        $currentRow = 6;
        foreach ($dataRows as $r) {
            if (!empty($r[2])) {
                $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", $r[0]);
                $sheet->getStyle("A{$currentRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'BE123C']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FBE8E8']],
                ]);
            } else {
                $sheet->setCellValue("A{$currentRow}", $r[0]);
                $sheet->setCellValue("B{$currentRow}", $r[1]);
                $sheet->setCellValue("C{$currentRow}", $r[2]);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
}
