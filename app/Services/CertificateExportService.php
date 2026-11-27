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
use PhpOffice\PhpWord\Style\Table as WordTableStyle;
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
        $aksaraBase64 = $this->getAksaraBase64();
        $signatureBase64 = $this->getApplicantSignatureBase64($birth->applicant_signature);
        $filename = 'Surat_Kelahiran_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $birth->registration_no) . '.pdf';

        $pdf = Pdf::loadView('exports.pdf.birth', [
            'birth' => $birth,
            'logoBase64' => $logoBase64,
            'aksaraBase64' => $aksaraBase64,
            'signatureBase64' => $signatureBase64,
        ])->setPaper('a4', 'portrait')->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'times-roman',
        ]);

        return $pdf->download($filename);
    }

    public function exportBirthDocx(BirthCertificate $birth): StreamedResponse
    {
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);
        $phpWord->setDefaultParagraphStyle([
            'spaceBefore' => 0,
            'spaceAfter'  => 0,
            'lineHeight'  => 1.15,
        ]);

        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'marginTop'    => 720,
            'marginBottom' => 720,
            'marginLeft'   => 950,
            'marginRight'  => 950,
        ]);

        $romans = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $refDate = $birth->created_at ?: now();
        $monthRoman = $romans[(int) $refDate->format('n')] ?? 'I';
        $letterYear = $refDate->format('Y');
        $letterSeq = (int) substr($birth->registration_no, -4) ?: 1;
        $reportDateFormatted = $refDate->translatedFormat('d F Y');

        $childDob = $birth->birth_date ? Carbon::parse($birth->birth_date) : null;
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
        $applicantDobFormatted = $applicantDobObj ? Carbon::parse($applicantDobObj)->translatedFormat('d F Y') : '-';

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

        $weightStr = $birth->weight_gram
            ? $birth->weight_gram . ' gram' . ($birth->weight_kg ? ' (' . $birth->weight_kg . ' kg)' : '')
            : ($birth->weight_kg ? $birth->weight_kg . ' kg' : '-');

        /* ---------------------------------------------------------------------
         * HALAMAN 1: KOP SURAT & DATA KELUARGA / BIODATA BAYI (BAGIAN I & II)
         * --------------------------------------------------------------------- */
        $this->addDocxPurwobinangunKopSurat($section, 'puwobinangun@slemankab.go.id');

        $section->addText('FORMULIR PELAPORAN & PERMOHONAN AKTA KELAHIRAN', [
            'name' => 'Times New Roman', 'size' => 12.5, 'bold' => true, 'underline' => 'single',
        ], ['alignment' => Jc::CENTER, 'spaceBefore' => 80, 'spaceAfter' => 40]);

        $section->addText('Nomor Registrasi: ' . $birth->registration_no . '  |  Tanggal Lapor: ' . $reportDateFormatted, [
            'name' => 'Times New Roman', 'size' => 9.5,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 160]);

        $formTableStyle = [
            'borderColor'       => '334155',
            'borderSize'        => 4,
            'cellMarginTop'     => 85,
            'cellMarginBottom'  => 85,
            'cellMarginLeft'    => 100,
            'cellMarginRight'   => 100,
            'layout'            => WordTableStyle::LAYOUT_FIXED,
        ];
        $phpWord->addTableStyle('BirthFormTable', $formTableStyle);

        $renderBirthTableRows = function ($tableObj, array $items): void {
            foreach ($items as $item) {
                $tableObj->addRow(null, ['cantSplit' => true]);
                if (!empty($item[2])) {
                    $cell = $tableObj->addCell(9800, ['gridSpan' => 2, 'bgColor' => 'E2E8F0', 'noWrap' => false]);
                    $cell->addText($item[0], ['bold' => true, 'color' => '0F172A', 'size' => 9.5, 'name' => 'Times New Roman'], ['lineHeight' => 1.25]);
                } else {
                    $c1 = $tableObj->addCell(3400, ['noWrap' => false]);
                    $c1->addText($item[0], ['size' => 9.5, 'name' => 'Times New Roman'], ['lineHeight' => 1.25]);
                    $c2 = $tableObj->addCell(6400, ['noWrap' => false]);
                    if (is_array($item[1])) {
                        $c2->addText($item[1][0], ['size' => 9.5, 'bold' => true, 'name' => 'Times New Roman'], ['lineHeight' => 1.25, 'spaceAfter' => 30]);
                        $c2->addText($item[1][1], ['size' => 9, 'name' => 'Times New Roman'], ['lineHeight' => 1.25]);
                    } else {
                        $c2->addText((string) $item[1], ['size' => 9.5, 'bold' => true, 'name' => 'Times New Roman'], ['lineHeight' => 1.25]);
                    }
                }
            }
        };

        $tablePage1 = $section->addTable('BirthFormTable');
        $rowsPage1 = [
            ['I. DATA KELUARGA & PENDAFTARAN', '', true],
            ['Nomor Registrasi', $birth->registration_no],
            ['Nomor Kartu Keluarga (KK)', $birth->family_card_no ?: '-'],
            ['Nama Kepala Keluarga', strtoupper($birth->father_name ?: ($birth->mother_name ?: $applicantName))],

            ['II. BIODATA LENGKAP BAYI / ANAK', '', true],
            ['Nama Lengkap Bayi / Anak', strtoupper($birth->child_name)],
            ['NIK Bayi / Anak', $birth->child_nik ?: '-'],
            ['Jenis Kelamin', $genderText],
            ['Tempat Dilahirkan / Kabupaten-Kota', ($birth->birth_place_type ?: '-') . ' — ' . strtoupper($birth->birth_place ?: 'SLEMAN')],
            ['Hari, Tanggal & Pukul Kelahiran', ($birth->birth_day ? strtoupper($birth->birth_day) . ', ' : '') . ($childDob ? $childDob->translatedFormat('d F Y') : '-') . ($birth->birth_time ? ' — Pukul ' . $birth->birth_time . ' WIB' : '')],
            ['Jenis Kelahiran / Kelahiran Anak Ke', ($birth->birth_type ?: 'Tunggal') . ' / Anak ke-' . ($birth->birth_order ?: '1')],
            ['Penolong Kelahiran', $birth->birth_helper ?: '-'],
            ['Berat & Panjang Bayi', $weightStr . ' / ' . ($birth->length_cm ? $birth->length_cm . ' cm' : '-')],
        ];
        $renderBirthTableRows($tablePage1, $rowsPage1);

        /* ---------------------------------------------------------------------
         * HALAMAN 2: DATA ORANG TUA, PELAPOR & SAKSI (BAGIAN III & IV)
         * --------------------------------------------------------------------- */
        $section->addPageBreak();

        $tablePage2 = $section->addTable('BirthFormTable');
        $rowsPage2 = [
            ['III. DATA KEDUA ORANG TUA KANDUNG', '', true],
            ['Nama Lengkap & NIK Ayah', strtoupper($birth->father_name ?: '-') . ' (' . ($birth->father_nik ?: '-') . ')'],
            ['Tgl Lahir / Umur & Pekerjaan Ayah', ($birth->father_birth_date ? $birth->father_birth_date->translatedFormat('d F Y') : '-') . ($fatherAgeVal !== null ? " ({$fatherAgeVal} th)" : '') . ' — ' . ($birth->father_job ?: '-') . ' (' . ($birth->father_citizenship ?: 'WNI') . ')'],
            ['Alamat Ayah', $fatherFullAddr],
            ['Nama Lengkap & NIK Ibu Kandung', strtoupper($birth->mother_name ?: '-') . ' (' . ($birth->mother_nik ?: '-') . ')'],
            ['Tgl Lahir / Umur & Pekerjaan Ibu', ($birth->mother_birth_date ? $birth->mother_birth_date->translatedFormat('d F Y') : '-') . ($motherAgeVal !== null ? " ({$motherAgeVal} th)" : '') . ' — ' . ($birth->mother_job ?: '-') . ' (' . ($birth->mother_citizenship ?: 'WNI') . ')'],
            ['Alamat Ibu Kandung', $motherFullAddr],
            ['Tanggal Pencatatan Perkawinan', $birth->marriage_date ? Carbon::parse($birth->marriage_date)->translatedFormat('d F Y') : '-'],

            ['IV. DATA PELAPOR & SAKSI KELAHIRAN', '', true],
            ['Nama Lengkap & NIK Pelapor', strtoupper($applicantName) . ' (' . $applicantNik . ')'],
            ['Umur, Pekerjaan & Hubungan Pelapor', ($applicantAgeVal !== null ? $applicantAgeVal . ' th' : '-') . ' — ' . $applicantJob . ' (' . ($birth->applicant_relation ?: 'Keluarga') . ')' . ($birth->applicant_phone ? ' | Telp: ' . $birth->applicant_phone : '')],
            ['Alamat Pelapor', $applicantFullAddr],
            ['Saksi I (Nama / NIK / Umur / Pekerjaan)', [strtoupper($birth->witness_1_name ?: '-') . ' / ' . ($birth->witness_1_nik ?: '-') . ($w1AgeVal !== null ? " / {$w1AgeVal} th" : '') . ($birth->witness_1_job ? ' / ' . $birth->witness_1_job : ''), 'Alamat: ' . $witness1FullAddr]],
            ['Saksi II (Nama / NIK / Umur / Pekerjaan)', [strtoupper($birth->witness_2_name ?: '-') . ' / ' . ($birth->witness_2_nik ?: '-') . ($w2AgeVal !== null ? " / {$w2AgeVal} th" : '') . ($birth->witness_2_job ? ' / ' . $birth->witness_2_job : ''), 'Alamat: ' . $witness2FullAddr]],
        ];
        $renderBirthTableRows($tablePage2, $rowsPage2);

        $section->addTextBreak(1);
        $this->addDocxFormSignature($section, $reportDateFormatted, strtoupper($applicantName), $birth->processed_by);

        /* ---------------------------------------------------------------------
         * HALAMAN TERAKHIR: SURAT KETERANGAN RESMI KALURAHAN PURWOBINANGUN
         * --------------------------------------------------------------------- */
        $section->addPageBreak();

        $this->addDocxPurwobinangunKopSurat($section, 'puwobinangun@slemankab.go.id');

        $section->addText('SURAT KETERANGAN', [
            'name' => 'Times New Roman', 'size' => 13, 'bold' => true, 'underline' => 'single',
        ], ['alignment' => Jc::CENTER, 'spaceBefore' => 100, 'spaceAfter' => 30]);

        $section->addText("Nomor : 474.1/ {$letterSeq} / {$monthRoman} / {$letterYear}", [
            'name' => 'Times New Roman', 'size' => 10.5,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 150]);

        $noBorder = [
            'borderColor'      => 'FFFFFF',
            'borderSize'       => 0,
            'cellMarginTop'    => 15,
            'cellMarginBottom' => 15,
            'cellMarginLeft'   => 20,
            'cellMarginRight'  => 20,
            'layout'           => WordTableStyle::LAYOUT_FIXED,
        ];

        // Yang bertandatangan di bawah ini
        $head1 = $section->addTable($noBorder);
        $head1->addRow(null, ['cantSplit' => true]);
        $head1->addCell(300, ['noWrap' => false])->addText('', ['size' => 10.5]);
        $head1->addCell(3600, ['noWrap' => false])->addText('Yang bertandatangan di bawah ini', ['size' => 10.5, 'name' => 'Times New Roman']);
        $head1->addCell(200, ['noWrap' => false])->addText(':', ['size' => 10.5, 'name' => 'Times New Roman']);
        $head1->addCell(5700, ['noWrap' => false])->addText('', ['size' => 10.5]);

        $sub1 = $section->addTable($noBorder);
        foreach ([
            ['Nama', '...................................................'],
            ['Jabatan', '...................................................'],
        ] as $row) {
            $sub1->addRow(null, ['cantSplit' => true]);
            $sub1->addCell(1000, ['noWrap' => false])->addText('', ['size' => 10.5]);
            $sub1->addCell(2200, ['noWrap' => false])->addText($row[0], ['size' => 10.5, 'name' => 'Times New Roman']);
            $sub1->addCell(200, ['noWrap' => false])->addText(':', ['size' => 10.5, 'name' => 'Times New Roman']);
            $sub1->addCell(6400, ['noWrap' => false])->addText($row[1], ['size' => 10.5, 'name' => 'Times New Roman']);
        }

        // Dengan ini menerangkan bahwa
        $head2 = $section->addTable($noBorder);
        $head2->addRow(null, ['cantSplit' => true]);
        $head2->addCell(300, ['noWrap' => false])->addText('', ['size' => 10.5]);
        $head2->addCell(3600, ['noWrap' => false])->addText('Dengan ini menerangkan bahwa', ['size' => 10.5, 'name' => 'Times New Roman'], ['spaceBefore' => 90]);
        $head2->addCell(200, ['noWrap' => false])->addText(':', ['size' => 10.5, 'name' => 'Times New Roman'], ['spaceBefore' => 90]);
        $head2->addCell(5700, ['noWrap' => false])->addText('', ['size' => 10.5]);

        $sub2 = $section->addTable($noBorder);
        $addSkRow = function ($label, $lines, bool $boldFirst = false) use ($sub2) {
            $sub2->addRow(null, ['cantSplit' => true]);
            $sub2->addCell(1000, ['noWrap' => false])->addText('', ['size' => 10.5]);
            $sub2->addCell(2200, ['noWrap' => false])->addText($label, ['size' => 10.5, 'name' => 'Times New Roman']);
            $sub2->addCell(200, ['noWrap' => false])->addText(':', ['size' => 10.5, 'name' => 'Times New Roman']);
            $valCell = $sub2->addCell(6400, ['noWrap' => false]);
            $linesArr = is_array($lines) ? $lines : [$lines];
            foreach ($linesArr as $idx => $line) {
                $valCell->addText((string) $line, [
                    'size' => 10.5,
                    'name' => 'Times New Roman',
                    'bold' => $boldFirst && $idx === 0,
                ], ['spaceAfter' => 15]);
            }
        };

        $addSkRow('Nama Lengkap', $applicantName, true);
        $addSkRow('NIK', $applicantNik);
        $addSkRow('Tanggal Lahir', $applicantDobFormatted);
        $addSkRow('Pekerjaan', $applicantJob);
        $addSkRow('Alamat', [
            $applicantStreetLine,
            'Kalurahan ' . ($birth->applicant_village ?: 'Purwobinangun'),
            'Kapanewon ' . ($birth->applicant_district ?: 'Pakem'),
            'Kabupaten ' . ($birth->applicant_city ?: 'Sleman'),
            'Provinsi ' . ($birth->applicant_province ?: 'DI Yogyakarta'),
        ]);
        $addSkRow('Pergi ke', [
            'Dinas Kependudukan dan Catatan Sipil',
            'Sleman',
        ]);
        $addSkRow('Keperluan', 'Mohon Akta Kelahiran untuk ' . strtoupper($birth->child_name));

        $section->addText(
            'Sehubungan dengan maksud yang bersangkutan, mohon yang berwenang memberikan bantuan serta fasilitas seperlunya.',
            ['size' => 10.5, 'name' => 'Times New Roman'],
            ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => 680, 'left' => 300], 'spaceBefore' => 130, 'spaceAfter' => 80, 'lineHeight' => 1.25]
        );

        $section->addText(
            'Demikian surat keterangan ini dibuat dengan sebenarnya, untuk dipergunakan sebagaimana mestinya.',
            ['size' => 10.5, 'name' => 'Times New Roman'],
            ['alignment' => Jc::BOTH, 'indentation' => ['firstLine' => 680, 'left' => 300], 'spaceAfter' => 180, 'lineHeight' => 1.25]
        );

        $skSig = $section->addTable($noBorder);
        $skSig->addRow(null, ['cantSplit' => true]);
        $skSig->addCell(5300, ['noWrap' => false]);
        $sigRight = $skSig->addCell(4500, ['noWrap' => false]);
        $sigRight->addText('Purwobinangun, ' . $reportDateFormatted, ['size' => 10.5, 'name' => 'Times New Roman']);
        $sigRight->addText('', ['size' => 10], ['spaceAfter' => 520]);
        $sigRight->addText('( .................................................... )', ['size' => 10.5, 'name' => 'Times New Roman']);

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

        $fatherAgeVal = $birth->father_age ?: ($birth->father_birth_date ? (int) $birth->father_birth_date->diffInYears($birth->birth_date ?? now()) : null);
        $motherAgeVal = $birth->mother_age ?: ($birth->mother_birth_date ? (int) $birth->mother_birth_date->diffInYears($birth->birth_date ?? now()) : null);
        $applicantAgeVal = $birth->applicant_age ?: ($birth->applicant_birth_date ? (int) $birth->applicant_birth_date->diffInYears(now()) : null);

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
        $phpWord->setDefaultFontSize(10);
        $phpWord->setDefaultParagraphStyle([
            'spaceBefore' => 0,
            'spaceAfter'  => 0,
            'lineHeight'  => 1.15,
        ]);

        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'marginTop'    => 720,
            'marginBottom' => 720,
            'marginLeft'   => 950,
            'marginRight'  => 950,
        ]);

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
            ? Carbon::parse($death->report_date)->translatedFormat('d F Y')
            : ($death->created_at ? $death->created_at->translatedFormat('d F Y') : Carbon::now()->translatedFormat('d F Y'));
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

        /* ---------------------------------------------------------------------
         * BAGIAN 1: FORMULIR DATA ISIAN PENGAJUAN AKTA KEMATIAN
         * --------------------------------------------------------------------- */
        $this->addDocxPurwobinangunKopSurat($section, 'desapurwobinangun@slemankab.go.id');

        $section->addText('FORMULIR PELAPORAN & PERMOHONAN AKTA KEMATIAN', [
            'name' => 'Times New Roman', 'size' => 11.5, 'bold' => true, 'underline' => 'single',
        ], ['alignment' => Jc::CENTER, 'spaceBefore' => 40, 'spaceAfter' => 20]);

        $section->addText('Nomor Registrasi: ' . $death->registration_no . '  |  Tanggal Lapor: ' . $reportDateFormatted, [
            'name' => 'Times New Roman', 'size' => 8.5,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 90]);

        $formTableStyle = [
            'borderColor'       => '334155',
            'borderSize'        => 4,
            'cellMarginTop'     => 35,
            'cellMarginBottom'  => 35,
            'cellMarginLeft'    => 70,
            'cellMarginRight'   => 70,
            'layout'            => WordTableStyle::LAYOUT_FIXED,
        ];
        $phpWord->addTableStyle('DeathFormTable', $formTableStyle);
        $formTable = $section->addTable('DeathFormTable');

        $formRows = [
            ['I. DATA KELUARGA & PENDAFTARAN', '', true],
            ['Nomor Registrasi', $death->registration_no],
            ['Nomor Kartu Keluarga (KK)', $death->family_card_no ?: '-'],
            ['Nama Kepala Keluarga', strtoupper($death->head_of_family_name ?: '-')],

            ['II. BIODATA LENGKAP JENAZAH', '', true],
            ['NIK Jenazah', $death->deceased_nik ?: '-'],
            ['Nama Lengkap Jenazah', strtoupper($death->deceased_name ?: '-')],
            ['Jenis Kelamin / Anak Ke', $genderText . ($death->deceased_child_order ? ' / Anak ke-' . $death->deceased_child_order : '')],
            ['Tempat, Tanggal Lahir / Umur', strtoupper($death->deceased_birth_place ?: 'SLEMAN') . ', ' . ($death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-') . ($deceasedAge !== null ? " ({$deceasedAge} Tahun)" : '')],
            ['Agama & Pekerjaan', strtoupper($death->religion ?: '-') . ' — ' . strtoupper($death->deceased_job ?: '-')],
            ['Alamat Lengkap Jenazah', strtoupper($deceasedFullAddress)],

            ['III. RINCIAN PERISTIWA KEMATIAN', '', true],
            ['Hari, Tanggal & Pukul Kematian', ($death->death_day ? strtoupper($death->death_day) . ', ' : '') . ($death->death_date ? $death->death_date->translatedFormat('d F Y') : '-') . ($death->death_time ? ' — Pukul ' . $death->death_time . ' WIB' : '')],
            ['Tempat Kematian', strtoupper($death->death_place ?: '-') . ($death->death_location_detail ? ' (' . strtoupper($death->death_location_detail) . ')' : '')],
            ['Sebab Kematian & Yang Menerangkan', strtoupper($death->cause_of_death ?: '-') . ' — ' . strtoupper($death->reported_by_title ?: 'LAINNYA')],

            ['IV. DATA ORANG TUA KANDUNG JENAZAH', '', true],
            ['Ibu Kandung (Nama / NIK)', strtoupper($death->mother_name ?: '-') . ' (' . ($death->mother_nik ?: '-') . ')'],
            ['TTL / Pekerjaan / Alamat Ibu', ($motherBirth ?: '-') . ' — ' . ($death->mother_job ?: '-') . ' — ' . ($death->mother_address ?: '-')],
            ['Ayah Kandung (Nama / NIK)', strtoupper($death->father_name ?: '-') . ' (' . ($death->father_nik ?: '-') . ')'],
            ['TTL / Pekerjaan / Alamat Ayah', ($fatherBirth ?: '-') . ' — ' . ($death->father_job ?: '-') . ' — ' . ($death->father_address ?: '-')],

            ['V. DATA PELAPOR (AHLI WARIS) & SAKSI', '', true],
            ['Pelapor (Nama / NIK / Hubungan)', strtoupper($death->applicant_name ?: '-') . ' / ' . ($death->applicant_nik ?: '-') . ' (' . ($death->applicant_relation ?: 'Ahli Waris / Keluarga') . ')' . ($death->applicant_phone ? ' | Telp: ' . $death->applicant_phone : '')],
            ['TTL / Pekerjaan / Alamat Pelapor', ($applicantBirth ?: '-') . ' — ' . ($death->applicant_job ?: '-') . ' — ' . ($death->applicant_address ?: '-')],
            ['Saksi I (Nama / NIK / TTL / Alamat)', [strtoupper($witness1Name ?: '-') . ' / ' . ($witness1Nik ?: '-') . ($witness1Birth ? ' / ' . $witness1Birth : ''), 'Alamat: ' . ($death->witness1_address ?: '-')]],
            ['Saksi II (Nama / NIK / TTL / Alamat)', [strtoupper($death->witness2_name ?: '-') . ' / ' . ($death->witness2_nik ?: '-') . ($witness2Birth ? ' / ' . $witness2Birth : ''), 'Alamat: ' . ($death->witness2_address ?: '-')]],
        ];

        foreach ($formRows as $item) {
            $formTable->addRow(null, ['cantSplit' => true]);
            if (!empty($item[2])) {
                $cell = $formTable->addCell(9800, ['gridSpan' => 2, 'bgColor' => 'E2E8F0', 'noWrap' => false]);
                $cell->addText($item[0], ['bold' => true, 'color' => '0F172A', 'size' => 8.5, 'name' => 'Times New Roman']);
            } else {
                $c1 = $formTable->addCell(3300, ['noWrap' => false]);
                $c1->addText($item[0], ['size' => 8.5, 'name' => 'Times New Roman']);
                $c2 = $formTable->addCell(6500, ['noWrap' => false]);
                if (is_array($item[1])) {
                    $c2->addText($item[1][0], ['size' => 8.5, 'bold' => true, 'name' => 'Times New Roman']);
                    $c2->addText($item[1][1], ['size' => 8.2, 'name' => 'Times New Roman']);
                } else {
                    $c2->addText((string) $item[1], ['size' => 8.5, 'bold' => true, 'name' => 'Times New Roman']);
                }
            }
        }

        $this->addDocxFormSignature($section, $reportDateFormatted, strtoupper($death->applicant_name ?: '-'), $death->processed_by);

        /* ---------------------------------------------------------------------
         * BAGIAN TERAKHIR: SURAT KETERANGAN KEMATIAN RESMI PURWOBINANGUN
         * --------------------------------------------------------------------- */
        $section->addPageBreak();

        $this->addDocxPurwobinangunKopSurat($section, 'desapurwobinangun@slemankab.go.id');

        $section->addText('SURAT KETERANGAN KEMATIAN', [
            'name' => 'Times New Roman', 'size' => 13.5, 'bold' => true,
        ], ['alignment' => Jc::CENTER, 'spaceBefore' => 60, 'spaceAfter' => 20]);

        $section->addText("No. 474.3/{$letterNumberSeq}/{$monthRoman}/{$letterYear}", [
            'name' => 'Times New Roman', 'size' => 10, 'bold' => true,
        ], ['alignment' => Jc::CENTER, 'spaceAfter' => 120]);

        $borderlessTableStyle = [
            'borderColor'      => 'FFFFFF',
            'borderSize'       => 0,
            'cellMarginTop'    => 15,
            'cellMarginBottom' => 15,
            'cellMarginLeft'   => 15,
            'cellMarginRight'  => 15,
            'layout'           => WordTableStyle::LAYOUT_FIXED,
        ];
        $phpWord->addTableStyle('BorderlessKK', $borderlessTableStyle);
        $kkTable = $section->addTable('BorderlessKK');

        $kkTable->addRow(null, ['cantSplit' => true]);
        $kkTable->addCell(3400, ['noWrap' => false])->addText('Nama Kepala Keluarga', ['size' => 9.5, 'name' => 'Times New Roman']);
        $kkTable->addCell(200, ['noWrap' => false])->addText(':', ['size' => 9.5, 'name' => 'Times New Roman']);
        $kkTable->addCell(6200, ['noWrap' => false])->addText(strtoupper($death->head_of_family_name ?: '-'), ['size' => 9.5, 'name' => 'Times New Roman']);

        $kkTable->addRow(null, ['cantSplit' => true]);
        $kkTable->addCell(3400, ['noWrap' => false])->addText('Nomor Kartu Keluarga', ['size' => 9.5, 'name' => 'Times New Roman']);
        $kkTable->addCell(200, ['noWrap' => false])->addText(':', ['size' => 9.5, 'name' => 'Times New Roman']);
        $kkTable->addCell(6200, ['noWrap' => false])->addText($death->family_card_no ?: '-', ['size' => 9.5, 'name' => 'Times New Roman']);

        // Section JENAZAH
        $section->addText('JENAZAH', ['size' => 10, 'bold' => true, 'name' => 'Times New Roman'], ['spaceBefore' => 80, 'spaceAfter' => 30]);

        $phpWord->addTableStyle('BorderlessJenazah', $borderlessTableStyle);
        $jTable = $section->addTable('BorderlessJenazah');

        $addDocxRow = function ($table, string $num, $label, string $val, ?string $valRight = null) {
            $table->addRow(null, ['cantSplit' => true]);
            $table->addCell(400, ['noWrap' => false])->addText($num, ['size' => 9.5, 'name' => 'Times New Roman']);
            $labelCell = $table->addCell(3000, ['noWrap' => false]);
            foreach ((is_array($label) ? $label : [$label]) as $line) {
                $labelCell->addText($line, ['size' => 9.5, 'name' => 'Times New Roman']);
            }
            $table->addCell(200, ['noWrap' => false])->addText(':', ['size' => 9.5, 'name' => 'Times New Roman']);
            if ($valRight !== null) {
                $table->addCell(4100, ['noWrap' => false])->addText($val, ['size' => 9.5, 'name' => 'Times New Roman']);
                $table->addCell(2100, ['noWrap' => false])->addText($valRight, ['size' => 9.5, 'name' => 'Times New Roman'], ['alignment' => Jc::RIGHT]);
            } else {
                $table->addCell(6200, ['gridSpan' => 2, 'noWrap' => false])->addText($val, ['size' => 9.5, 'name' => 'Times New Roman']);
            }
        };

        $addDocxRow($jTable, '1', 'NIK', $death->deceased_nik ?: '-');
        $addDocxRow($jTable, '2', 'Nama Lengkap', strtoupper($death->deceased_name ?: '-'));
        $addDocxRow($jTable, '3', 'Jenis Kelamin / Anak ke', $genderText, 'Anak ke ' . ($death->deceased_child_order ?: ''));
        $deceasedBirthStr = strtoupper($death->deceased_birth_place ?: 'SLEMAN') . ', ' . ($death->birth_date ? $death->birth_date->format('d-m-Y') : '-');
        $addDocxRow($jTable, '4', ['Tempat dan Tanggal Lahir /', 'Umur'], $deceasedBirthStr, 'Umur ' . ($deceasedAge !== null ? $deceasedAge . ' th' : '-'));
        $addDocxRow($jTable, '5', 'Agama', strtoupper($death->religion ?: '-'));
        $addDocxRow($jTable, '6', 'Pekerjaan', strtoupper($death->deceased_job ?: '-'));
        $addDocxRow($jTable, '7', 'Alamat', strtoupper($deceasedFullAddress));
        $deathDateStr = $death->death_date ? $death->death_date->format('d-m-Y') : '-';
        $deathTimeStr = 'Jam: ' . ($death->death_time ? str_replace(':', '.', $death->death_time) . ' WIB' : '-');
        $addDocxRow($jTable, '8', 'Tanggal dan Jam Kematian', $deathDateStr, $deathTimeStr);
        $addDocxRow($jTable, '9', 'Sebab Kematian', strtoupper($death->cause_of_death ?: '-'));
        $deathPlaceStr = strtoupper($death->death_place ?: '-') . ($death->death_location_detail ? ' (' . strtoupper($death->death_location_detail) . ')' : '');
        $addDocxRow($jTable, '10', 'Tempat Kematian', $deathPlaceStr);
        $addDocxRow($jTable, '11', 'Yang Menerangkan', strtoupper($death->reported_by_title ?: 'LAINNYA'));

        // Lanjutan Surat Keterangan Kematian: IBU, AYAH, PELAPOR, SAKSI 1, SAKSI 2 & TANDA TANGAN
        $section->addPageBreak();

        $addSectionBlock = function ($title, $nik, $name, $birth, $address, $spaceBefore = 65) use ($section, $addDocxRow, $dotsPlaceholder, $borderlessTableStyle) {
            $section->addText($title, ['size' => 10, 'bold' => true, 'name' => 'Times New Roman'], ['spaceBefore' => $spaceBefore, 'spaceAfter' => 20]);
            $table = $section->addTable($borderlessTableStyle);
            $addDocxRow($table, '1', 'NIK', $nik ?: $dotsPlaceholder);
            $addDocxRow($table, '2', 'Nama Lengkap', $name ? strtoupper($name) : $dotsPlaceholder);
            $addDocxRow($table, '3', ['Tempat dan Tanggal Lahir /', 'Umur'], $birth ? strtoupper($birth) : $dotsPlaceholder);
            $addDocxRow($table, '4', 'Alamat', $address ? strtoupper($address) : $dotsPlaceholder);
        };

        $addSectionBlock('IBU', $death->mother_nik, $death->mother_name, $motherBirth, $death->mother_address, 0);
        $addSectionBlock('AYAH', $death->father_nik, $death->father_name, $fatherBirth, $death->father_address);
        $addSectionBlock('PELAPOR', $death->applicant_nik, $death->applicant_name, $applicantBirth, $death->applicant_address);
        $addSectionBlock('SAKSI 1', $witness1Nik, $witness1Name, $witness1Birth, $death->witness1_address);
        $addSectionBlock('SAKSI 2', $death->witness2_nik, $death->witness2_name, $witness2Birth, $death->witness2_address);

        $this->addDocxDeathSignature($section, $reportDateFormatted);

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
            $deceasedAge = (int) $death->birth_date->diffInYears($death->death_date);
        } elseif ($death->birth_date) {
            $deceasedAge = (int) $death->birth_date->diffInYears(now());
        }

        $motherAge = $death->mother_birth_date ? (int) $death->mother_birth_date->diffInYears($death->death_date ?? now()) : null;
        $fatherAge = $death->father_birth_date ? (int) $death->father_birth_date->diffInYears($death->death_date ?? now()) : null;

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

    private function getApplicantSignatureBase64(?string $signature): string
    {
        if (!$signature) {
            return '';
        }
        if (str_starts_with($signature, 'data:image')) {
            return $signature;
        }
        $storagePath = storage_path('app/public/' . ltrim($signature, '/'));
        if (file_exists($storagePath)) {
            $type = pathinfo($storagePath, PATHINFO_EXTENSION) ?: 'png';
            return 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($storagePath));
        }
        return '';
    }

    private function addDocxPurwobinangunKopSurat($section, string $email): void
    {
        $logoPath = public_path('images/logo-sleman.png');
        $aksaraPath = public_path('images/aksara-purwobinangun.jpg');

        $kopTable = $section->addTable([
            'borderColor'       => 'FFFFFF',
            'borderSize'        => 0,
            'borderBottomColor' => '000000',
            'borderBottomSize'  => 18,
            'cellMarginTop'     => 10,
            'cellMarginBottom'  => 45,
            'cellMarginLeft'    => 10,
            'cellMarginRight'   => 10,
            'layout'            => WordTableStyle::LAYOUT_FIXED,
        ]);
        $kopTable->addRow(null, ['cantSplit' => true]);

        if (file_exists($logoPath)) {
            $c1 = $kopTable->addCell(1300, ['noWrap' => false]);
            $c1->addImage($logoPath, ['width' => 46, 'height' => 56, 'alignment' => Jc::CENTER]);
            $c2 = $kopTable->addCell(8500, ['noWrap' => false]);
        } else {
            $c2 = $kopTable->addCell(9800, ['noWrap' => false]);
        }

        $c2->addText('PEMERINTAH KABUPATEN SLEMAN', ['size' => 10, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'lineHeight' => 1.1]);
        $c2->addText('KAPANEWON PAKEM', ['size' => 11, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'lineHeight' => 1.1]);
        $c2->addText('PEMERINTAH KALURAHAN PURWOBINANGUN', ['size' => 13, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 15, 'lineHeight' => 1.1]);

        if (file_exists($aksaraPath)) {
            $c2->addImage($aksaraPath, ['height' => 11, 'width' => 155, 'alignment' => Jc::CENTER]);
        }

        $c2->addText('Alamat : Jl. Pakem-Turi Km.04 Watuadeg Purwobinangun Pakem Sleman Yogyakarta. 55582', ['size' => 7.8, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'lineHeight' => 1.1]);
        $c2->addText('Laman : www.purwobinangun.slemankab.go.id   Surel : ' . $email, ['size' => 7.8, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'lineHeight' => 1.1]);
    }

    private function addDocxFormSignature($section, string $dateFormatted, string $applicantName, ?string $processedBy = null): void
    {
        $sigTable = $section->addTable([
            'borderColor' => 'FFFFFF',
            'borderSize'  => 0,
            'layout'      => WordTableStyle::LAYOUT_FIXED,
        ]);
        $sigTable->addRow(null, ['cantSplit' => true]);

        $c1 = $sigTable->addCell(4900, ['noWrap' => false]);
        $c1->addText('Sleman, ' . $dateFormatted, ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceBefore' => 80]);
        $c1->addText('Petugas Penerima Berkas,', ['size' => 9, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER]);
        $c1->addText('', ['size' => 9], ['spaceAfter' => 420]);
        $c1->addText('( ' . ($processedBy ?: '...................................................') . ' )', ['size' => 9, 'underline' => 'single', 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER]);

        $c2 = $sigTable->addCell(4900, ['noWrap' => false]);
        $c2->addText('Sleman, ' . $dateFormatted, ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceBefore' => 80]);
        $c2->addText('Pemohon / Pelapor,', ['size' => 9, 'bold' => true, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER]);
        $c2->addText('', ['size' => 9], ['spaceAfter' => 420]);
        $c2->addText('( ' . $applicantName . ' )', ['size' => 9, 'bold' => true, 'underline' => 'single', 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER]);
    }

    private function addDocxDeathSignature($section, string $dateFormatted): void
    {
        $sigTable = $section->addTable([
            'borderColor' => 'FFFFFF',
            'borderSize'  => 0,
            'layout'      => WordTableStyle::LAYOUT_FIXED,
        ]);
        $sigTable->addRow(null, ['cantSplit' => true]);

        $sigTable->addCell(5500, ['noWrap' => false]);
        $c2 = $sigTable->addCell(4300, ['noWrap' => false]);
        $c2->addText('Purwobinangun, ' . $dateFormatted, ['size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 140]);
        $c2->addText('', ['size' => 9.5], ['spaceAfter' => 500]);
        $c2->addText('( .................................................... )', ['size' => 9.5, 'name' => 'Times New Roman']);
    }
}
