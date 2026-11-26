<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Petugas Utama',
            'email' => 'admin.utama@purwobinangun.desa.id',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_guest_or_warga_cannot_access_staff_management(): void
    {
        // Guest
        $response = $this->get(route('admin.staff.index'));
        $response->assertRedirect(route('admin.login'));

        // Warga
        $warga = User::factory()->create([
            'role' => 'warga',
            'status' => 'active',
        ]);
        $response = $this->actingAs($warga)->get(route('admin.staff.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_staff_management_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.staff.index'));
        $response->assertOk();
        $response->assertSee('Kelola &amp; Tambah Akun Petugas', false);
        $response->assertSee($this->admin->email);
    }

    public function test_admin_can_create_new_staff_account(): void
    {
        $payload = [
            'name' => 'Petugas Baru Purwobinangun',
            'email' => 'petugas.baru@purwobinangun.desa.id',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.staff.store'), $payload);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Petugas Baru Purwobinangun',
            'email' => 'petugas.baru@purwobinangun.desa.id',
            'role' => 'admin',
            'status' => 'active',
            'phone' => '081234567890',
            'verified_by' => 'Petugas Utama',
        ]);

        $newStaff = User::where('email', 'petugas.baru@purwobinangun.desa.id')->first();
        $this->assertTrue(Hash::check('password123', $newStaff->password));

        // Verifikasi log aktivitas tercatat
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'title' => 'Penambahan Akun Petugas/Admin',
            'subject_id' => $newStaff->id,
        ]);
    }

    public function test_create_staff_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.staff.store'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_admin_can_deactivate_and_delete_inactive_or_retired_staff_but_not_self(): void
    {
        $retiredStaff = User::factory()->create([
            'name' => 'Petugas Pensiun',
            'email' => 'pensiun@purwobinangun.desa.id',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        // Self-delete guard
        $selfDel = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $this->admin));
        $selfDel->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // Toggle deactivate
        $toggleResp = $this->actingAs($this->admin)->patch(route('admin.staff.toggle_status', $retiredStaff), [
            'reason' => 'Pensiun',
        ]);
        $toggleResp->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseHas('users', [
            'id' => $retiredStaff->id,
            'status' => 'archived',
            'rejection_reason' => 'Pensiun',
        ]);

        // Delete retired staff permanently
        $delResp = $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $retiredStaff), [
            'reason' => 'Pensiun',
        ]);
        $delResp->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseMissing('users', ['id' => $retiredStaff->id]);
    }

    public function test_citizen_verification_pagination_previous_link_is_clickable_on_page_two(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            User::factory()->create([
                'name' => "Warga Uji {$i}",
                'nik' => str_pad((string) $i, 16, '3', STR_PAD_LEFT),
                'family_card_no' => str_pad((string) $i, 16, '4', STR_PAD_LEFT),
                'role' => 'warga',
                'status' => 'pending',
                'phone' => '0812345678' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'address' => 'Padukuhan Babadan',
                'rt' => '01',
                'rw' => '02',
            ]);
        }

        $page2 = $this->actingAs($this->admin)->get(route('admin.citizens.index', ['page' => 2]));
        $page2->assertOk();
        $page2->assertSee('rel="prev"', false);
        $page2->assertSee('Sebelumnya');
    }
}
