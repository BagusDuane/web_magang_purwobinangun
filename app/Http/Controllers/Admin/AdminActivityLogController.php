<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Pastikan data historis terisi otomatis jika log masih baru dibuat
        ActivityLog::backfillHistoricalData();

        $search = $request->input('search');
        $module = $request->input('module');
        $actionType = $request->input('action_type');
        $actorRole = $request->input('actor_role') ?: $request->input('user_role');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = ActivityLog::latest('id');

        // Filter Pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        // Filter Modul dengan pemetaan alias
        if ($module && $module !== 'all') {
            if ($module === 'akte_kelahiran' || $module === 'birth_certificate') {
                $query->where('module', 'birth_certificate');
            } elseif ($module === 'akte_kematian' || $module === 'death_certificate') {
                $query->where('module', 'death_certificate');
            } elseif ($module === 'akun_warga' || $module === 'citizen' || $module === 'auth') {
                $query->whereIn('module', ['citizen', 'auth']);
            } elseif ($module === 'perubahan_profil' || $module === 'profile_update') {
                $query->where('module', 'profile_update');
            } elseif ($module === 'petugas' || $module === 'staff') {
                $query->where('module', 'staff');
            } else {
                $query->where('module', $module);
            }
        }

        // Filter Jenis Aksi dengan pemetaan alias
        if ($actionType && $actionType !== 'all') {
            $actionMap = [
                'pengajuan' => 'create',
                'setujui' => 'approve',
                'tolak' => 'reject',
                'ubah' => 'update',
                'arsip' => 'archive',
                'pulihkan' => 'restore',
                'hapus' => 'delete',
            ];
            $mappedAction = $actionMap[$actionType] ?? $actionType;
            $query->where('action_type', $mappedAction);
        }

        // Filter Role Pelaku
        if ($actorRole && $actorRole !== 'all') {
            $query->where('user_role', $actorRole);
        }

        // Filter Tanggal
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Pagination default menampilkan 10 data per halaman sesuai instruksi user
        $logs = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'admin' => ActivityLog::where('user_role', 'admin')->count(),
            'warga' => ActivityLog::where('user_role', 'warga')->count(),
        ];

        $filters = [
            'search' => $search,
            'module' => $module,
            'action_type' => $actionType,
            'actor_role' => $actorRole,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return view('admin.activity_logs.index', compact('logs', 'stats', 'filters'));
    }

    public function destroy(ActivityLog $activityLog)
    {
        $ref = $activityLog->reference_no ?: ('#' . $activityLog->id);
        $activityLog->delete();

        return back()->with('success', "Catatan log aktivitas ({$ref}) berhasil dihapus.");
    }

    public function destroyFiltered(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $module = $request->input('module');
        $actionType = $request->input('action_type');
        $actorRole = $request->input('actor_role') ?: $request->input('user_role');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        if (!$search && !$module && !$actionType && !$actorRole && !$dateFrom && !$dateTo) {
            return back()->with('error', 'Silakan gunakan pencarian (misal NIK/Nama warga pindah domisili) atau filter terlebih dahulu sebelum menghapus log massal.');
        }

        $query = ActivityLog::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($module && $module !== 'all') {
            if ($module === 'akte_kelahiran' || $module === 'birth_certificate') {
                $query->where('module', 'birth_certificate');
            } elseif ($module === 'akte_kematian' || $module === 'death_certificate') {
                $query->where('module', 'death_certificate');
            } elseif ($module === 'akun_warga' || $module === 'citizen' || $module === 'auth') {
                $query->whereIn('module', ['citizen', 'auth']);
            } elseif ($module === 'perubahan_profil' || $module === 'profile_update') {
                $query->where('module', 'profile_update');
            } elseif ($module === 'petugas' || $module === 'staff') {
                $query->where('module', 'staff');
            } else {
                $query->where('module', $module);
            }
        }

        if ($actionType && $actionType !== 'all') {
            $actionMap = [
                'pengajuan' => 'create',
                'setujui' => 'approve',
                'tolak' => 'reject',
                'ubah' => 'update',
                'arsip' => 'archive',
                'pulihkan' => 'restore',
                'hapus' => 'delete',
            ];
            $query->where('action_type', $actionMap[$actionType] ?? $actionType);
        }

        if ($actorRole && $actorRole !== 'all') {
            $query->where('user_role', $actorRole);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $deletedCount = $query->count();
        $query->delete();

        return redirect()->route('admin.activity_logs.index')
            ->with('success', "Berhasil menghapus {$deletedCount} catatan log aktivitas sesuai kriteria filter.");
    }
}
