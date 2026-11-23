<?php

namespace Tests\Feature;

use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAttachmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->warga = User::factory()->create([
            'role' => 'warga',
            'status' => 'active',
            'family_card_no' => '3404050101900001',
        ]);
    }

    public function test_admin_can_view_and_download_birth_certificate_attachments(): void
    {
        $file = UploadedFile::fake()->create('surat_lahir.jpg', 150, 'image/jpeg');
        $path = $file->store('uploads/birth_certs', 'public');

        $birth = BirthCertificate::create([
            'registration_no' => 'AKL-20261123-0001',
            'family_card_no' => '3404050101900001',
            'child_name' => 'Bayi Pratama',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-11-20',
            'birth_time' => '08:00',
            'birth_type' => 'Tunggal',
            'birth_order' => 1,
            'helper_type' => 'Bidan',
            'weight' => 3.2,
            'length' => 49,
            'mother_nik' => '3404055205900002',
            'mother_name' => 'Siti Aminah',
            'mother_birth_place' => 'Sleman',
            'mother_birth_date' => '1992-05-12',
            'mother_citizenship' => 'WNI',
            'father_nik' => '3404051205900001',
            'father_name' => 'Bambang Nugroho',
            'father_birth_place' => 'Sleman',
            'father_birth_date' => '1990-05-12',
            'father_citizenship' => 'WNI',
            'applicant_nik' => '3404051205900001',
            'applicant_name' => 'Bambang Nugroho',
            'applicant_phone' => '081234567890',
            'padukuhan' => 'Kadilobo',
            'rt' => '02',
            'rw' => '05',
            'status' => 'pending',
            'doc_birth_cert' => $path,
        ]);

        // 1. Admin view attachment (inline)
        $response = $this->actingAs($this->admin)->get(route('admin.birth.attachment.view', [
            'birth' => $birth,
            'field' => 'doc_birth_cert',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition') ?? '');

        // 2. Admin download attachment
        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.birth.attachment.download', [
            'birth' => $birth,
            'field' => 'doc_birth_cert',
        ]));

        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment', $downloadResponse->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('Surat_Keterangan_Lahir_AKL-20261123-0001', $downloadResponse->headers->get('Content-Disposition') ?? '');
    }

    public function test_admin_can_view_and_download_death_certificate_attachments(): void
    {
        $file = UploadedFile::fake()->create('surat_kematian.pdf', 200, 'application/pdf');
        $path = $file->store('uploads/death_certs', 'public');

        $death = DeathCertificate::create([
            'registration_no' => 'AKM-20261123-0001',
            'family_card_no' => '3404050101900001',
            'deceased_nik' => '3404050101450001',
            'deceased_name' => 'Mbah Marto Suwito',
            'gender' => 'L',
            'birth_date' => '1945-01-01',
            'religion' => 'Islam',
            'padukuhan' => 'Donolayan',
            'rt' => '02',
            'rw' => '03',
            'death_date' => '2026-08-20',
            'death_time' => '10:00',
            'death_place' => 'Rumah',
            'death_cause' => 'Sakit Tua',
            'death_determinant' => 'Dokter',
            'applicant_nik' => '3404051205900001',
            'applicant_name' => 'Bambang Nugroho',
            'applicant_relation' => 'Anak',
            'applicant_phone' => '081234567890',
            'heir_nik' => '3404051205900001',
            'heir_name' => 'Bambang Nugroho',
            'status' => 'pending',
            'doc_death_statement' => $path,
        ]);

        // 1. Admin view attachment (inline)
        $response = $this->actingAs($this->admin)->get(route('admin.death.attachment.view', [
            'death' => $death,
            'field' => 'doc_death_statement',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition') ?? '');

        // 2. Admin download attachment
        $downloadResponse = $this->actingAs($this->admin)->get(route('admin.death.attachment.download', [
            'death' => $death,
            'field' => 'doc_death_statement',
        ]));

        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment', $downloadResponse->headers->get('Content-Disposition') ?? '');
        $this->assertStringContainsString('Surat_Kematian_AKM-20261123-0001.pdf', $downloadResponse->headers->get('Content-Disposition') ?? '');
    }

    public function test_guest_and_warga_cannot_access_admin_attachment_routes(): void
    {
        $birth = BirthCertificate::create([
            'registration_no' => 'AKL-20261123-0002',
            'family_card_no' => '3404050101900001',
            'child_name' => 'Bayi Dua',
            'gender' => 'P',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-11-20',
            'birth_time' => '08:00',
            'birth_type' => 'Tunggal',
            'birth_order' => 1,
            'helper_type' => 'Bidan',
            'weight' => 3.0,
            'length' => 48,
            'mother_nik' => '3404055205900002',
            'mother_name' => 'Siti Aminah',
            'father_nik' => '3404051205900001',
            'father_name' => 'Bambang Nugroho',
            'applicant_nik' => '3404051205900001',
            'applicant_name' => 'Bambang Nugroho',
            'applicant_phone' => '081234567890',
            'padukuhan' => 'Kadilobo',
            'rt' => '02',
            'rw' => '05',
            'status' => 'pending',
            'doc_birth_cert' => 'uploads/birth_certs/dummy.jpg',
        ]);

        // Guest
        $this->get(route('admin.birth.attachment.view', ['birth' => $birth, 'field' => 'doc_birth_cert']))
            ->assertRedirect(route('admin.login'));

        // Warga (non-admin)
        $this->actingAs($this->warga)
            ->get(route('admin.birth.attachment.view', ['birth' => $birth, 'field' => 'doc_birth_cert']))
            ->assertRedirect(route('admin.login'));
    }

    public function test_invalid_field_returns_404(): void
    {
        $birth = BirthCertificate::create([
            'registration_no' => 'AKL-20261123-0003',
            'family_card_no' => '3404050101900001',
            'child_name' => 'Bayi Tiga',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-11-20',
            'birth_time' => '08:00',
            'birth_type' => 'Tunggal',
            'birth_order' => 1,
            'helper_type' => 'Bidan',
            'weight' => 3.0,
            'length' => 48,
            'mother_nik' => '3404055205900002',
            'mother_name' => 'Siti Aminah',
            'father_nik' => '3404051205900001',
            'father_name' => 'Bambang Nugroho',
            'applicant_nik' => '3404051205900001',
            'applicant_name' => 'Bambang Nugroho',
            'applicant_phone' => '081234567890',
            'padukuhan' => 'Kadilobo',
            'rt' => '02',
            'rw' => '05',
            'status' => 'pending',
            'doc_birth_cert' => 'uploads/birth_certs/dummy.jpg',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.birth.attachment.view', ['birth' => $birth, 'field' => 'malicious_field']))
            ->assertNotFound();
    }
}
