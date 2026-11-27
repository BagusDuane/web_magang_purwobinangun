<?php

namespace Tests\Feature;

use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use App\Models\User;
use App\Services\CitizenNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogicFixesRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_track_by_nik_but_can_track_by_registration_no(): void
    {
        $warga = User::where('role', 'warga')->where('status', 'active')->first();

        $birth = BirthCertificate::create([
            'user_id' => $warga->id,
            'registration_no' => 'AKL-20261127-0001',
            'family_card_no' => $warga->family_card_no,
            'child_name' => 'Bayi Rahasia',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-11-01',
            'applicant_nik' => $warga->nik,
            'applicant_name' => $warga->name,
            'applicant_phone' => '081234567890',
            'padukuhan' => 'Babadan',
            'rt' => '01',
            'rw' => '02',
            'status' => 'pending',
        ]);

        // Guest searching by NIK must NOT find the record
        $guestByNik = $this->get(route('tracking.index', ['keyword' => $warga->nik]));
        $guestByNik->assertStatus(200);
        $guestByNik->assertDontSee('Bayi Rahasia');
        $guestByNik->assertSee('Data Permohonan Tidak Ditemukan');

        // Guest searching by exact registration_no still works
        $guestByReg = $this->get(route('tracking.index', ['keyword' => 'AKL-20261127-0001']));
        $guestByReg->assertStatus(200);
        $guestByReg->assertSee('AKL-20261127-0001');
        $guestByReg->assertSee('Bayi Rahasia');
    }

    public function test_rejected_application_cannot_print_receipt(): void
    {
        $warga = User::where('role', 'warga')->where('status', 'active')->first();

        $birth = BirthCertificate::create([
            'user_id' => $warga->id,
            'registration_no' => 'AKL-20261127-0002',
            'family_card_no' => $warga->family_card_no,
            'child_name' => 'Bayi Ditolak',
            'gender' => 'P',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-11-01',
            'applicant_nik' => $warga->nik,
            'applicant_name' => $warga->name,
            'applicant_phone' => '081234567890',
            'status' => 'rejected',
            'rejection_note' => 'Berkas tidak valid',
        ]);

        $response = $this->actingAs($warga)->get(route('tracking.print_receipt', [
            'type' => 'birth',
            'registrationNo' => $birth->registration_no,
        ]));

        $response->assertRedirect(route('tracking.show', ['type' => 'birth', 'registrationNo' => $birth->registration_no]));
        $response->assertSessionHas('error');
    }

    public function test_archived_warga_cannot_be_taken_over_via_reapply_nik(): void
    {
        $archivedWarga = User::create([
            'name' => 'Warga Pindah',
            'role' => 'warga',
            'nik' => '3404059988776655',
            'family_card_no' => '3404051122334455',
            'birth_place' => 'Sleman',
            'birth_date' => '1990-05-05',
            'gender' => 'L',
            'address' => 'Pakem',
            'rt' => '01',
            'rw' => '01',
            'phone' => '081234567890',
            'email' => 'pindah@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'archived',
        ]);

        // Registering again with archived NIK must fail unique check
        $response = $this->post(route('warga.register.submit'), [
            'nik' => '3404059988776655',
            'family_card_no' => '3404051122334455',
            'name' => 'Penyerang',
            'birth_place' => 'Sleman',
            'birth_date' => '1990-05-05',
            'gender' => 'L',
            'address' => 'Pakem',
            'rt' => '01',
            'rw' => '01',
            'phone' => '081999999999',
            'email' => 'attacker@example.com',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $response->assertSessionHasErrors(['nik']);
        $this->assertEquals('archived', $archivedWarga->fresh()->status);
    }

    public function test_rejected_warga_reapply_requires_matching_kk_and_birth_date(): void
    {
        $rejectedWarga = User::create([
            'name' => 'Warga Ditolak',
            'role' => 'warga',
            'nik' => '3404059988776644',
            'family_card_no' => '3404051122334444',
            'birth_place' => 'Sleman',
            'birth_date' => '1992-03-15',
            'gender' => 'L',
            'address' => 'Pakem',
            'rt' => '01',
            'rw' => '01',
            'phone' => '081234567890',
            'email' => 'ditolak@example.com',
            'password' => Hash::make('secret123'),
            'status' => 'rejected',
        ]);

        $mismatch = $this->post(route('warga.register.submit'), [
            'nik' => '3404059988776644',
            'family_card_no' => '3404050000000000',
            'name' => 'Penyerang',
            'birth_place' => 'Sleman',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'address' => 'Pakem',
            'rt' => '01',
            'rw' => '01',
            'phone' => '081999999999',
            'email' => 'ditolak@example.com',
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ]);

        $mismatch->assertSessionHasErrors(['nik']);
        $this->assertEquals('rejected', $rejectedWarga->fresh()->status);
    }

    public function test_birth_and_death_revision_cannot_overwrite_non_revision_records(): void
    {
        Storage::fake('public');
        $warga = User::where('role', 'warga')->where('status', 'active')->first();

        $birth = BirthCertificate::create([
            'user_id' => $warga->id,
            'registration_no' => 'AKL-20261127-0010',
            'family_card_no' => $warga->family_card_no,
            'child_name' => 'Anak Selesai',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-10-01',
            'applicant_nik' => $warga->nik,
            'applicant_name' => $warga->name,
            'applicant_phone' => '081234567890',
            'status' => 'ready_for_pickup',
            'doc_birth_cert' => 'uploads/birth_certs/a.pdf',
            'doc_family_card' => 'uploads/birth_certs/b.pdf',
            'doc_marriage_cert' => 'uploads/birth_certs/c.pdf',
            'doc_parents_ktp' => 'uploads/birth_certs/d.pdf',
            'doc_witness_ktp' => 'uploads/birth_certs/e.pdf',
        ]);

        $birthAttempt = $this->actingAs($warga)->post(route('birth.store'), [
            'revision_registration_no' => $birth->registration_no,
            'applicant_name' => $warga->name,
            'applicant_nik' => $warga->nik,
            'address' => 'Pakem',
            'father_name' => 'Ayah',
            'father_nik' => '3404051111111111',
            'father_birth_date' => '1990-01-01',
            'father_job' => 'Wiraswasta',
            'mother_name' => 'Ibu',
            'mother_nik' => '3404052222222222',
            'mother_birth_date' => '1992-01-01',
            'mother_job' => 'Guru',
            'child_name' => 'Anak Diubah',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-10-01',
            'birth_order' => 1,
            'birth_type' => 'Tunggal',
            'birth_place_type' => 'RS/SB',
        ]);

        $birthAttempt->assertRedirect(route('tracking.show', ['type' => 'birth', 'registrationNo' => $birth->registration_no]));
        $this->assertEquals('ready_for_pickup', $birth->fresh()->status);
        $this->assertEquals('Anak Selesai', $birth->fresh()->child_name);

        $death = DeathCertificate::create([
            'user_id' => $warga->id,
            'registration_no' => 'AKM-20261127-0010',
            'family_card_no' => $warga->family_card_no,
            'deceased_nik' => '3404050101450001',
            'deceased_name' => 'Almarhum Tetap',
            'gender' => 'L',
            'padukuhan' => 'Babadan',
            'rt' => '01',
            'rw' => '02',
            'death_date' => '2026-10-05',
            'death_place' => 'Rumah',
            'cause_of_death' => 'Sakit',
            'applicant_nik' => $warga->nik,
            'applicant_name' => $warga->name,
            'applicant_phone' => '081234567890',
            'applicant_relation' => 'Anak',
            'status' => 'picked_up',
            'is_archived' => true,
        ]);

        $deathAttempt = $this->actingAs($warga)->post(route('death.store'), [
            'registration_no' => $death->registration_no,
            'head_of_family_name' => 'Kepala',
            'deceased_nik' => '3404050101450001',
            'deceased_name' => 'Diubah',
            'gender' => 'L',
            'deceased_birth_place' => 'Sleman',
            'birth_date' => '1945-01-01',
            'religion' => 'Islam',
            'padukuhan' => 'Babadan',
            'rt' => '01',
            'rw' => '02',
            'death_day' => 'Senin',
            'death_date' => '2026-10-05',
            'death_place' => 'Rumah',
            'cause_of_death' => 'Sakit',
            'reported_by_title' => 'Dokter',
            'applicant_nik' => $warga->nik,
            'applicant_name' => $warga->name,
            'applicant_phone' => '081234567890',
            'applicant_relation' => 'Anak',
            'doc_death_statement' => UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'),
            'doc_rt_rw_statement' => UploadedFile::fake()->create('b.pdf', 100, 'application/pdf'),
            'doc_family_card' => UploadedFile::fake()->create('c.pdf', 100, 'application/pdf'),
            'doc_deceased_ktp' => UploadedFile::fake()->create('d.pdf', 100, 'application/pdf'),
            'doc_heir_family_card' => UploadedFile::fake()->create('e.pdf', 100, 'application/pdf'),
            'doc_heir_ktp' => UploadedFile::fake()->create('f.pdf', 100, 'application/pdf'),
        ]);

        $deathAttempt->assertRedirect(route('tracking.show', ['type' => 'death', 'registrationNo' => $death->registration_no]));
        $this->assertEquals(1, DeathCertificate::count());
        $this->assertEquals('picked_up', $death->fresh()->status);
    }

    public function test_manually_archived_certificate_does_not_block_citizen_deletion_and_admin_update_notifies_warga(): void
    {
        $admin = User::where('role', 'admin')->first();
        $warga = User::where('role', 'warga')->where('status', 'active')->first();

        $birth = BirthCertificate::create([
            'user_id' => $warga->id,
            'registration_no' => 'AKL-20261127-0020',
            'family_card_no' => $warga->family_card_no,
            'child_name' => 'Bayi Arsip Manual',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-10-01',
            'applicant_nik' => $warga->nik,
            'applicant_name' => $warga->name,
            'applicant_phone' => '081234567890',
            'status' => 'pending',
            'is_archived' => true,
        ]);

        $this->assertFalse($warga->fresh()->hasActiveApplications());

        // Admin direct profile update triggers 'Perubahan Data oleh Admin' notification
        $this->actingAs($admin)->put(route('admin.citizens.update', $warga), [
            'nik' => $warga->nik,
            'family_card_no' => '3404059999000011',
            'name' => $warga->name,
            'birth_place' => $warga->birth_place ?: 'Sleman',
            'birth_date' => $warga->birth_date ? $warga->birth_date->format('Y-m-d') : '1990-01-01',
            'gender' => $warga->gender ?: 'L',
            'family_relationship' => $warga->family_relationship ?: 'Kepala Keluarga',
            'phone' => '081200001111',
            'email' => $warga->email,
            'address' => $warga->address ?: 'Purwobinangun',
            'rt' => $warga->rt ?: '01',
            'rw' => $warga->rw ?: '01',
            'status' => 'active',
            'admin_note' => 'Pembaruan nomor KK resmi',
        ])->assertRedirect(route('admin.citizens.show', $warga));

        $notifs = CitizenNotificationService::getNotificationsForUser($warga->fresh());
        $this->assertTrue($notifs->contains('title', 'Perubahan Data oleh Admin'));
        $this->assertEquals('3404059999000011', $birth->fresh()->family_card_no);
    }

    public function test_inactive_admin_is_blocked_and_invalid_tracking_type_returns_404(): void
    {
        $inactiveAdmin = User::create([
            'name' => 'Eks Petugas',
            'email' => 'eks@purwobinangun.desa.id',
            'role' => 'admin',
            'status' => 'archived',
            'password' => Hash::make('admin123'),
        ]);

        $birth = BirthCertificate::create([
            'registration_no' => 'AKL-20261127-0030',
            'family_card_no' => '3404051122334499',
            'child_name' => 'Bayi <script>alert(1)</script>',
            'gender' => 'L',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-10-01',
            'applicant_nik' => '3404051122334400',
            'applicant_name' => 'Pelapor Test',
            'applicant_phone' => '081234567890',
            'status' => 'pending',
        ]);

        $this->actingAs($inactiveAdmin)
            ->get(route('tracking.print_letter', ['type' => 'birth', 'registrationNo' => $birth->registration_no]))
            ->assertStatus(403);

        $this->actingAs($inactiveAdmin)
            ->get(route('birth.print_letter', $birth))
            ->assertRedirect(route('warga.login'));

        $this->actingAs($inactiveAdmin)
            ->get(route('tracking.show', ['type' => 'invalid', 'registrationNo' => $birth->registration_no]))
            ->assertStatus(404);
    }

    public function test_admin_citizen_update_syncs_old_nik_and_family_members_and_restore_clears_note(): void
    {
        $admin = User::where('role', 'admin')->first();
        $warga = User::where('role', 'warga')->where('status', 'active')->first();
        $oldNik = $warga->nik;

        $member = \App\Models\FamilyMember::create([
            'user_id' => $warga->id,
            'family_card_no' => $warga->family_card_no,
            'nik' => '3404059999888877',
            'name' => 'Anggota KK',
            'family_relationship' => 'Anak',
        ]);

        $orphanBirth = BirthCertificate::create([
            'user_id' => null,
            'registration_no' => 'AKL-20261127-0040',
            'family_card_no' => $warga->family_card_no,
            'child_name' => 'Anak Sinkron',
            'gender' => 'P',
            'birth_place' => 'Sleman',
            'birth_date' => '2026-10-01',
            'applicant_nik' => $oldNik,
            'applicant_name' => $warga->name,
            'applicant_phone' => '081234567890',
            'status' => 'rejected',
            'is_archived' => true,
            'rejection_note' => 'Catatan lama',
        ]);

        $this->actingAs($admin)->put(route('admin.citizens.update', $warga), [
            'nik' => '3404051111222299',
            'family_card_no' => '3404057777888899',
            'name' => 'Nama Baru Warga',
            'birth_place' => 'Sleman',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'family_relationship' => 'Kepala Keluarga',
            'phone' => '081299998888',
            'email' => $warga->email,
            'address' => 'Purwobinangun',
            'rt' => '01',
            'rw' => '01',
            'status' => 'active',
            'admin_note' => 'Koreksi NIK dan KK',
        ])->assertRedirect(route('admin.citizens.show', $warga));

        $this->assertEquals('3404057777888899', $member->fresh()->family_card_no);
        $this->assertEquals('3404051111222299', $orphanBirth->fresh()->applicant_nik);
        $this->assertEquals('3404057777888899', $orphanBirth->fresh()->family_card_no);

        $this->actingAs($admin)->post(route('admin.archive.birth.restore', $orphanBirth));
        $this->assertEquals('pending', $orphanBirth->fresh()->status);
        $this->assertNull($orphanBirth->fresh()->rejection_note);

        // Verify XSS escaping in CitizenNotificationService
        $notifs = CitizenNotificationService::getNotificationsForUser($warga->fresh());
        $orphanBirth->update(['child_name' => '<script>xss</script>', 'user_id' => $warga->id]);
        $notifsEscaped = CitizenNotificationService::getNotificationsForUser($warga->fresh());
        $birthNotif = $notifsEscaped->firstWhere('reference_no', 'AKL-20261127-0040');
        $this->assertStringNotContainsString('<script>', $birthNotif['message']);
        $this->assertStringContainsString('&lt;script&gt;', $birthNotif['message']);
    }
}
