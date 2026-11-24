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
}
