<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use App\Models\FamilyMember;
use App\Models\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminProfileRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $query = ProfileUpdateRequest::with('user')->latest();

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('family_card_no', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => ProfileUpdateRequest::count(),
            'pending' => ProfileUpdateRequest::where('status', 'pending')->count(),
            'approved' => ProfileUpdateRequest::where('status', 'approved')->count(),
            'rejected' => ProfileUpdateRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.profile_requests.index', compact('requests', 'status', 'search', 'stats'));
    }

    public function show(ProfileUpdateRequest $profileRequest)
    {
        $profileRequest->load('user.familyMembers');
        $user = $profileRequest->user;

        return view('admin.profile_requests.show', compact('profileRequest', 'user'));
    }

    public function verify(Request $request, ProfileUpdateRequest $profileRequest)
    {
        if (!$profileRequest->isPending()) {
            return redirect()->route('admin.profile_requests.show', $profileRequest)
                ->with('error', 'Permohonan perubahan data ini telah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|nullable|string|max:1000',
        ], [
            'action.required' => 'Pilihan tindakan verifikasi wajib ditentukan.',
            'rejection_reason.required_if' => 'Alasan atau catatan penolakan permohonan wajib diisi jika menolak.',
        ]);

        $verifierName = Auth::user()->name;

        if ($validated['action'] === 'approve') {
            /** @var User $targetUser */
            $targetUser = $profileRequest->user;
            if (!$targetUser) {
                return redirect()->route('admin.profile_requests.show', $profileRequest)
                    ->with('error', 'Akun warga pemohon tidak ditemukan.');
            }

            $targetNik = $profileRequest->nik ?: $targetUser->nik;
            $targetEmail = $profileRequest->email ?: $targetUser->email;

            if ($targetNik && User::where('id', '!=', $targetUser->id)->where('nik', $targetNik)->exists()) {
                return redirect()->route('admin.profile_requests.show', $profileRequest)
                    ->with('error', 'Tidak dapat menyetujui permohonan: NIK ' . $targetNik . ' sudah digunakan oleh akun lain.');
            }
            if ($targetEmail && User::where('id', '!=', $targetUser->id)->where('email', $targetEmail)->exists()) {
                return redirect()->route('admin.profile_requests.show', $profileRequest)
                    ->with('error', 'Tidak dapat menyetujui permohonan: Email ' . $targetEmail . ' sudah digunakan oleh akun lain.');
            }

            DB::transaction(function () use ($profileRequest, $verifierName, $targetUser) {
                $user = $targetUser;
                $oldNik = $user->nik;

                // Update data akun warga
                $updateData = [
                    'nik' => $profileRequest->nik ?: $user->nik,
                    'family_card_no' => $profileRequest->family_card_no ?: $user->family_card_no,
                    'name' => $profileRequest->name,
                    'birth_place' => $profileRequest->birth_place,
                    'birth_date' => $profileRequest->birth_date,
                    'gender' => $profileRequest->gender,
                    'family_relationship' => $profileRequest->family_relationship,
                    'phone' => $profileRequest->phone,
                    'email' => $profileRequest->email,
                    'address' => $profileRequest->address,
                    'rt' => $profileRequest->rt,
                    'rw' => $profileRequest->rw,
                ];

                if ($profileRequest->doc_family_card) {
                    $updateData['doc_family_card'] = $profileRequest->doc_family_card;
                }

                $user->update($updateData);

                // Sinkronisasi anggota keluarga
                $user->familyMembers()->delete();

                if (!empty($profileRequest->family_members_data) && is_array($profileRequest->family_members_data)) {
                    foreach ($profileRequest->family_members_data as $m) {
                        if (empty(trim($m['name'] ?? '')) && empty(trim($m['nik'] ?? ''))) {
                            continue;
                        }
                        FamilyMember::create([
                            'user_id' => $user->id,
                            'family_card_no' => !empty($m['family_card_no']) ? $m['family_card_no'] : $user->family_card_no,
                            'nik' => !empty($m['nik']) ? $m['nik'] : null,
                            'name' => $m['name'],
                            'birth_place' => $m['birth_place'] ?? null,
                            'birth_date' => !empty($m['birth_date']) ? $m['birth_date'] : null,
                            'gender' => !empty($m['gender']) ? $m['gender'] : null,
                            'family_relationship' => !empty($m['family_relationship']) ? $m['family_relationship'] : 'Anggota Keluarga',
                        ]);
                    }
                }

                // Sinkronisasi data permohonan akta terkait (termasuk No. KK & NIK pelapor)
                BirthCertificate::where(function ($q) use ($user, $oldNik) {
                    $q->where('user_id', $user->id);
                    if ($oldNik) {
                        $q->orWhere('applicant_nik', $oldNik);
                    }
                })->update([
                    'family_card_no' => $user->family_card_no,
                    'applicant_nik' => $user->nik,
                    'applicant_phone' => $user->phone,
                    'applicant_name' => $user->name,
                ]);

                DeathCertificate::where(function ($q) use ($user, $oldNik) {
                    $q->where('user_id', $user->id);
                    if ($oldNik) {
                        $q->orWhere('applicant_nik', $oldNik);
                    }
                })->update([
                    'family_card_no' => $user->family_card_no,
                    'applicant_nik' => $user->nik,
                    'applicant_phone' => $user->phone,
                    'applicant_name' => $user->name,
                ]);

                // Update status permohonan
                $profileRequest->update([
                    'status' => 'approved',
                    'admin_notes' => null,
                    'processed_by' => $verifierName,
                    'processed_at' => now(),
                ]);

                ActivityLog::record([
                    'user_id' => Auth::id(),
                    'user_name' => $verifierName,
                    'user_role' => 'admin',
                    'action_type' => 'approve',
                    'module' => 'profile_update',
                    'title' => 'Perubahan Data KK Disetujui',
                    'description' => 'Persetujuan permohonan perubahan data Kartu Keluarga dan profil warga.',
                    'reference_no' => $profileRequest->family_card_no,
                    'subject_type' => ProfileUpdateRequest::class,
                    'subject_id' => $profileRequest->id,
                ]);
            });

            return redirect()->route('admin.profile_requests.show', $profileRequest)
                ->with('success', 'Permohonan perubahan data warga (' . $profileRequest->name . ') berhasil disetujui. Data profil dan anggota keluarga telah berhasil diperbarui di sistem.');
        } else {
            $profileRequest->update([
                'status' => 'rejected',
                'admin_notes' => $validated['rejection_reason'],
                'processed_by' => $verifierName,
                'processed_at' => now(),
            ]);

            ActivityLog::record([
                'user_id' => Auth::id(),
                'user_name' => $verifierName,
                'user_role' => 'admin',
                'action_type' => 'reject',
                'module' => 'profile_update',
                'title' => 'Perubahan Data KK Ditolak',
                'description' => 'Penolakan permohonan perubahan data Kartu Keluarga. Alasan: ' . $validated['rejection_reason'],
                'reference_no' => $profileRequest->family_card_no,
                'subject_type' => ProfileUpdateRequest::class,
                'subject_id' => $profileRequest->id,
            ]);

            return redirect()->route('admin.profile_requests.show', $profileRequest)
                ->with('success', 'Permohonan perubahan data warga (' . $profileRequest->name . ') telah ditolak dengan catatan yang diberikan.');
        }
    }

    public function destroy(ProfileUpdateRequest $profileRequest)
    {
        $applicantName = $profileRequest->name;
        $requestNik = $profileRequest->nik;
        $familyCardNo = $profileRequest->family_card_no;
        $adminUser = Auth::user();

        DB::transaction(function () use ($profileRequest, $applicantName, $requestNik, $familyCardNo, $adminUser) {
            // Hapus berkas kartu keluarga fisik pengajuan jika ada dan tidak dipakai oleh data aktif user saat ini
            if ($profileRequest->doc_family_card) {
                $userCurrentDoc = $profileRequest->user?->doc_family_card;
                if ($profileRequest->doc_family_card !== $userCurrentDoc) {
                    $cleanPath = ltrim(preg_replace('#^(public/|storage/)#', '', $profileRequest->doc_family_card), '/\\');
                    if (Storage::disk('public')->exists($cleanPath)) {
                        Storage::disk('public')->delete($cleanPath);
                    }
                }
            }

            ActivityLog::record([
                'user_id' => $adminUser->id,
                'user_name' => $adminUser->name,
                'user_role' => 'admin',
                'action_type' => 'delete',
                'module' => 'profile_update',
                'title' => 'Permohonan Perubahan Data Dihapus',
                'description' => 'Penghapusan data permohonan perubahan data warga (' . $applicantName . ' - NIK: ' . $requestNik . ').',
                'reference_no' => $familyCardNo,
                'subject_type' => ProfileUpdateRequest::class,
                'subject_id' => $profileRequest->id,
            ]);

            $profileRequest->delete();
        });

        return redirect()->route('admin.profile_requests.index')
            ->with('success', 'Permohonan perubahan data warga (' . $applicantName . ') berhasil dihapus dari sistem.');
    }
}
