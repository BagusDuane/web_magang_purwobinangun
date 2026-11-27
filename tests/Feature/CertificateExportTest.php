<?php

namespace Tests\Feature;

use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $warga1;
    private User $warga2;
    private BirthCertificate $birth;
    private DeathCertificate $death;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Kalurahan',
            'nik' => '3404051111900001',
            'family_card_no' => '3404051111900001',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $this->warga1 = User::create([
            'name' => 'Warga Purwo Satu',
            'nik' => '3404052222900001',
            'family_card_no' => '3404052222900001',
            'role' => 'warga',
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $this->warga2 = User::create([
            'name' => 'Warga Purwo Dua',
            'nik' => '3404053333900001',
            'family_card_no' => '3404053333900001',
            'role' => 'warga',
            'status' => 'active',
            'password' => bcrypt('password123'),
        ]);

        $this->birth = BirthCertificate::create([
            'user_id' => $this->warga1->id,
            'registration_no' => 'KLH-202611-0001',
            'family_card_no' => $this->warga1->family_card_no,
            'child_name' => 'Ananda Rizky Purwobinangun',
            'child_nik' => '3404050101260001',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-11-10',
            'birth_day' => 'Selasa',
            'birth_time' => '08:30',
            'birth_order' => 1,
            'birth_type' => 'Tunggal',
            'birth_helper' => 'Bidan',
            'birth_place_type' => 'Puskesmas Pakem',
            'weight_kg' => 3.2,
            'weight_gram' => 3200,
            'length_cm' => 49,
            'father_name' => 'Ayah Rizky',
            'father_nik' => '3404051010880001',
            'mother_name' => 'Ibu Rizky',
            'mother_nik' => '3404051010900001',
            'applicant_name' => 'Warga Purwo Satu',
            'applicant_nik' => '3404052222900001',
            'applicant_phone' => '081234567890',
            'applicant_relation' => 'Ayah Kandung',
            'status' => 'ready_for_pickup',
        ]);

        $this->death = DeathCertificate::create([
            'user_id' => $this->warga1->id,
            'registration_no' => 'KMT-202611-0001',
            'family_card_no' => $this->warga1->family_card_no,
            'head_of_family_name' => 'Kepala Keluarga Mulyo',
            'deceased_name' => 'Mbah Mulyo Santoso',
            'deceased_nik' => '3404050101450001',
            'gender' => 'L',
            'deceased_birth_place' => 'Sleman',
            'birth_date' => '1945-05-15',
            'padukuhan' => 'Babadan',
            'rt' => '01',
            'rw' => '02',
            'death_date' => '2026-11-01',
            'death_day' => 'Minggu',
            'death_time' => '14:15',
            'death_place' => 'Rumah',
            'cause_of_death' => 'Sakit Biasa / Usia Lanjut',
            'applicant_name' => 'Warga Purwo Satu',
            'applicant_nik' => '3404052222900001',
            'applicant_phone' => '081234567890',
            'applicant_relation' => 'Ayah Kandung',
            'status' => 'ready_for_pickup',
        ]);
    }

    public function test_admin_can_export_birth_certificate_in_all_three_formats(): void
    {
        foreach (['pdf', 'docx', 'xlsx'] as $format) {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.birth.export', ['birth' => $this->birth, 'format' => $format]));

            $response->assertStatus(200);
            $this->assertNotEmpty($response->headers->get('content-disposition'));
            $this->assertStringContainsString($format, $response->headers->get('content-disposition'));
        }
    }

    public function test_admin_can_export_death_certificate_in_all_three_formats(): void
    {
        foreach (['pdf', 'docx', 'xlsx'] as $format) {
            $response = $this->actingAs($this->admin)
                ->get(route('admin.death.export', ['death' => $this->death, 'format' => $format]));

            $response->assertStatus(200);
            $this->assertNotEmpty($response->headers->get('content-disposition'));
            $this->assertStringContainsString($format, $response->headers->get('content-disposition'));
        }
    }

    public function test_warga_can_export_own_kk_certificates(): void
    {
        $responseBirth = $this->actingAs($this->warga1)
            ->get(route('birth.export', ['birth' => $this->birth, 'format' => 'pdf']));
        $responseBirth->assertStatus(200);

        $responseDeath = $this->actingAs($this->warga1)
            ->get(route('death.export', ['death' => $this->death, 'format' => 'docx']));
        $responseDeath->assertStatus(200);
    }

    public function test_warga_cannot_export_other_kk_certificates(): void
    {
        $responseBirth = $this->actingAs($this->warga2)
            ->get(route('birth.export', ['birth' => $this->birth, 'format' => 'pdf']));
        $responseBirth->assertStatus(403);

        $responseDeath = $this->actingAs($this->warga2)
            ->get(route('death.export', ['death' => $this->death, 'format' => 'xlsx']));
        $responseDeath->assertStatus(403);
    }

    public function test_export_with_invalid_format_returns_bad_request(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.birth.export', ['birth' => $this->birth, 'format' => 'exe']));

        $response->assertStatus(400);
    }

    public function test_tracking_export_works_for_admin_and_authorized_warga(): void
    {
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('tracking.export', ['type' => 'birth', 'registrationNo' => $this->birth->registration_no, 'format' => 'xlsx']));
        $responseAdmin->assertStatus(200);

        $responseWarga = $this->actingAs($this->warga1)
            ->get(route('tracking.export', ['type' => 'death', 'registrationNo' => $this->death->registration_no, 'format' => 'pdf']));
        $responseWarga->assertStatus(200);

        $responseForbidden = $this->actingAs($this->warga2)
            ->get(route('tracking.export', ['type' => 'birth', 'registrationNo' => $this->birth->registration_no, 'format' => 'pdf']));
        $responseForbidden->assertStatus(403);
    }

    public function test_excel_export_displays_accurate_cell_values_and_preserves_nik_strings(): void
    {
        $birthResp = $this->actingAs($this->admin)
            ->get(route('admin.birth.export', ['birth' => $this->birth, 'format' => 'xlsx']));
        $birthResp->assertStatus(200);

        $tmpBirth = tempnam(sys_get_temp_dir(), 'birth_xlsx_');
        file_put_contents($tmpBirth, $birthResp->streamedContent());
        $birthSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpBirth)->getActiveSheet();
        @unlink($tmpBirth);

        $birthValues = [];
        foreach ($birthSheet->toArray(null, false, false, true) as $row) {
            if (!empty($row['B'])) {
                $birthValues[$row['B']] = $row['C'];
            }
        }

        $this->assertSame('KLH-202611-0001', $birthValues['Nomor Registrasi'] ?? null);
        $this->assertSame('ANANDA RIZKY PURWOBINANGUN', $birthValues['Nama Lengkap Anak'] ?? null);
        $this->assertSame('3404050101260001', $birthValues['NIK Anak'] ?? null);
        $this->assertSame('3404051010880001', $birthValues['NIK Ayah'] ?? null);
        $this->assertSame('AYAH RIZKY', $birthValues['Nama Lengkap Ayah'] ?? null);
        $this->assertSame('081234567890', $birthValues['Telepon / WhatsApp Pelapor'] ?? null);

        $deathResp = $this->actingAs($this->admin)
            ->get(route('admin.death.export', ['death' => $this->death, 'format' => 'xlsx']));
        $deathResp->assertStatus(200);

        $tmpDeath = tempnam(sys_get_temp_dir(), 'death_xlsx_');
        file_put_contents($tmpDeath, $deathResp->streamedContent());
        $deathSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpDeath)->getActiveSheet();
        @unlink($tmpDeath);

        $deathValues = [];
        foreach ($deathSheet->toArray(null, false, false, true) as $row) {
            if (!empty($row['B'])) {
                $deathValues[$row['B']] = $row['C'];
            }
        }

        $this->assertSame('KMT-202611-0001', $deathValues['Nomor Registrasi'] ?? null);
        $this->assertSame('3404050101450001', $deathValues['NIK Jenazah'] ?? null);
        $this->assertSame('MBAH MULYO SANTOSO', $deathValues['Nama Lengkap Jenazah'] ?? null);
        $this->assertSame('SAKIT BIASA / USIA LANJUT', $deathValues['Sebab Kematian'] ?? null);
        $this->assertSame('081234567890', $deathValues['Telepon / WhatsApp Pelapor'] ?? null);
    }

    public function test_admin_can_delete_birth_and_death_certificates_for_moved_citizens(): void
    {
        $delBirth = $this->actingAs($this->admin)
            ->delete(route('admin.birth.destroy', $this->birth), ['reason' => 'Warga pindah domisili']);
        $delBirth->assertRedirect(route('admin.birth.index'));
        $this->assertDatabaseMissing('birth_certificates', ['id' => $this->birth->id]);

        $delDeath = $this->actingAs($this->admin)
            ->delete(route('admin.death.destroy', $this->death), ['from' => 'archive', 'reason' => 'Warga pindah domisili']);
        $delDeath->assertRedirect(route('admin.archive.index', ['tab' => 'death']));
        $this->assertDatabaseMissing('death_certificates', ['id' => $this->death->id]);
    }

    public function test_pdf_and_docx_exports_include_form_data_and_last_page_surat_keterangan(): void
    {
        // Render Birth & Death PDF Blade templates directly to verify structure & content
        $birthHtml = view('exports.pdf.birth', [
            'birth' => $this->birth,
            'logoBase64' => '',
            'aksaraBase64' => '',
            'signatureBase64' => '',
        ])->render();

        $this->assertStringContainsString('PERMOHONAN AKTA KELAHIRAN', $birthHtml);
        $this->assertStringContainsString('FORMULIR PELAPORAN KELAHIRAN', $birthHtml);
        $this->assertStringContainsString('Kode F-2.02', $birthHtml);
        $this->assertStringContainsString('DOKUMEN PERSYARATAN', $birthHtml);
        $this->assertStringContainsString('Ananda Rizky Purwobinangun', $birthHtml);
        $this->assertStringContainsString('SURAT KETERANGAN', $birthHtml);
        $this->assertStringContainsString('Nomor : 474.1/', $birthHtml);
        $this->assertStringContainsString('Dinas Kependudukan dan Catatan Sipil', $birthHtml);
        $this->assertStringContainsString('Mohon Akta Kelahiran untuk', $birthHtml);
        $this->assertStringContainsString('ANANDA RIZKY PURWOBINANGUN', $birthHtml);
        $this->assertStringContainsString('Purwobinangun,', $birthHtml);
        $this->assertStringContainsString('a.n Lurah Purwobinangun', $birthHtml);
        $this->assertStringContainsString('( .................................................... )', $birthHtml);
        $this->assertStringNotContainsString('R. Heri Suasana', $birthHtml);
        $this->assertStringNotContainsString('Kaur Tata Laksana', $birthHtml);
        $this->assertStringNotContainsString('TEGUH KURNIAWAN', $birthHtml);

        $deathHtml = view('exports.pdf.death', [
            'death' => $this->death,
            'logoBase64' => '',
            'aksaraBase64' => '',
        ])->render();

        $this->assertStringContainsString('FORMULIR PELAPORAN &amp; PERMOHONAN AKTA KEMATIAN', $deathHtml);
        $this->assertStringContainsString('Mbah Mulyo Santoso', $deathHtml);
        $this->assertStringContainsString('SURAT KETERANGAN KEMATIAN', $deathHtml);
        $this->assertStringContainsString('No. 474.3/', $deathHtml);
        $this->assertStringContainsString('Purwobinangun,', $deathHtml);
        $this->assertStringContainsString('( .................................................... )', $deathHtml);
        $this->assertStringNotContainsString('Kaur Tata Laksana', $deathHtml);

        // Verify Birth DOCX XML contains both Form Data and Surat Keterangan
        $birthDocxResp = $this->actingAs($this->admin)
            ->get(route('admin.birth.export', ['birth' => $this->birth, 'format' => 'docx']));
        $birthDocxResp->assertStatus(200);
        $tmpBirthDocx = tempnam(sys_get_temp_dir(), 'birth_docx_');
        file_put_contents($tmpBirthDocx, $birthDocxResp->streamedContent());
        $zipBirth = new \ZipArchive();
        $this->assertTrue($zipBirth->open($tmpBirthDocx) === true);
        $birthDocXml = $zipBirth->getFromName('word/document.xml');
        $zipBirth->close();
        @unlink($tmpBirthDocx);

        $this->assertStringContainsString('PERMOHONAN AKTA KELAHIRAN', $birthDocXml);
        $this->assertStringContainsString('FORMULIR PELAPORAN KELAHIRAN', $birthDocXml);
        $this->assertStringContainsString('Kode F-2.02', $birthDocXml);
        $this->assertStringContainsString('DOKUMEN PERSYARATAN', $birthDocXml);
        $this->assertStringContainsString('SURAT KETERANGAN', $birthDocXml);
        $this->assertStringContainsString('474.1/', $birthDocXml);
        $this->assertStringContainsString('Mohon Akta Kelahiran untuk', $birthDocXml);
        $this->assertStringContainsString('ANANDA RIZKY PURWOBINANGUN', $birthDocXml);
        $this->assertStringContainsString('a.n Lurah Purwobinangun', $birthDocXml);
        $this->assertStringContainsString('( .................................................... )', $birthDocXml);
        $this->assertStringNotContainsString('R. Heri Suasana', $birthDocXml);
        $this->assertStringNotContainsString('Kaur Tata Laksana', $birthDocXml);
        $this->assertStringNotContainsString('TEGUH KURNIAWAN', $birthDocXml);
        $this->assertStringNotContainsString('<w:noWrap/>', $birthDocXml);
        $this->assertStringContainsString('<w:tblLayout w:type="fixed"/>', $birthDocXml);
        $this->assertStringContainsString('<w:cantSplit', $birthDocXml);

        // Verify Death DOCX XML contains both Form Data and Surat Keterangan Kematian
        $deathDocxResp = $this->actingAs($this->admin)
            ->get(route('admin.death.export', ['death' => $this->death, 'format' => 'docx']));
        $deathDocxResp->assertStatus(200);
        $tmpDeathDocx = tempnam(sys_get_temp_dir(), 'death_docx_');
        file_put_contents($tmpDeathDocx, $deathDocxResp->streamedContent());
        $zipDeath = new \ZipArchive();
        $this->assertTrue($zipDeath->open($tmpDeathDocx) === true);
        $deathDocXml = $zipDeath->getFromName('word/document.xml');
        $zipDeath->close();
        @unlink($tmpDeathDocx);

        $this->assertStringContainsString('FORMULIR PELAPORAN &amp; PERMOHONAN AKTA KEMATIAN', $deathDocXml);
        $this->assertStringContainsString('SURAT KETERANGAN KEMATIAN', $deathDocXml);
        $this->assertStringContainsString('474.3/', $deathDocXml);
        $this->assertStringContainsString('MBAH MULYO SANTOSO', $deathDocXml);
        $this->assertStringContainsString('Umur 81 th', $deathDocXml);
        $this->assertStringContainsString('Purwobinangun,', $deathDocXml);
        $this->assertStringContainsString('( .................................................... )', $deathDocXml);
        $this->assertStringNotContainsString('Kaur Tata Laksana', $deathDocXml);
        $this->assertStringNotContainsString('<w:noWrap/>', $deathDocXml);
        $this->assertStringContainsString('<w:tblLayout w:type="fixed"/>', $deathDocXml);
        $this->assertStringContainsString('<w:cantSplit', $deathDocXml);

        // Verify exact PDF page counts without awkward overflow even with long addresses
        $this->birth->fill([
            'father_address' => 'Padukuhan Watuadeg Jl. Pakem-Turi Km 04 Blok Selatan',
            'father_rt' => '002', 'father_rw' => '005',
            'father_village' => 'Purwobinangun', 'father_district' => 'Pakem', 'father_city' => 'Sleman', 'father_province' => 'DI Yogyakarta',
            'mother_address' => 'Padukuhan Watuadeg Jl. Pakem-Turi Km 04 Blok Selatan',
            'mother_rt' => '002', 'mother_rw' => '005',
            'mother_village' => 'Purwobinangun', 'mother_district' => 'Pakem', 'mother_city' => 'Sleman', 'mother_province' => 'DI Yogyakarta',
            'witness_1_name' => 'PURNOMO RAHAYU SANTOSO', 'witness_1_nik' => '3404162303870002', 'witness_1_job' => 'Wiraswasta',
            'witness_1_address' => 'Padukuhan Kemiri RT 001 / RW 003, Kalurahan Purwobinangun, Kapanewon Pakem, Kabupaten Sleman',
            'witness_2_name' => 'HARI PUJIANTORO WIBOWO', 'witness_2_nik' => '3404160704760001', 'witness_2_job' => 'Perangkat Desa',
            'witness_2_address' => 'Padukuhan Candi RT 004 / RW 008, Kalurahan Purwobinangun, Kapanewon Pakem, Kabupaten Sleman',
        ]);
        $pdfBirth = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.pdf.birth', [
            'birth' => $this->birth,
            'logoBase64' => '',
            'aksaraBase64' => '',
            'signatureBase64' => '',
        ])->setPaper('a4', 'portrait');
        $pdfBirth->render();
        $this->assertSame(3, $pdfBirth->getCanvas()->get_page_count());

        $pdfDeath = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.pdf.death', [
            'death' => $this->death,
            'logoBase64' => '',
            'aksaraBase64' => '',
        ])->setPaper('a4', 'portrait');
        $pdfDeath->render();
        $this->assertSame(3, $pdfDeath->getCanvas()->get_page_count());
    }
}
