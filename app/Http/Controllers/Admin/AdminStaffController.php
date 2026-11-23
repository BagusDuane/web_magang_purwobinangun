<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    /**
     * Tampilkan daftar akun petugas/admin dan formulir tambah akun.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'admin');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $admins = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total' => User::where('role', 'admin')->count(),
            'active' => User::where('role', 'admin')->where('status', 'active')->count(),
        ];

        return view('admin.staff.index', compact('admins', 'stats'));
    }

    /**
     * Simpan akun petugas/admin baru.
     */
    public function store(Request $request)
    {
        $currentAdmin = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap petugas wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah terdaftar pada akun lain.',
            'phone.max' => 'Nomor telepon/WhatsApp maksimal 20 digit.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $newAdmin = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'active',
            'phone' => $validated['phone'] ?? null,
            'verified_at' => now(),
            'verified_by' => $currentAdmin->name,
        ]);

        ActivityLog::record([
            'actor_id' => $currentAdmin->id,
            'actor_name' => $currentAdmin->name,
            'actor_role' => 'admin',
            'module' => 'citizen',
            'action' => 'create',
            'title' => 'Penambahan Akun Petugas/Admin',
            'description' => "Menambahkan akun petugas/admin baru: {$newAdmin->name} ({$newAdmin->email}).",
            'reference_no' => $newAdmin->email,
            'subject_type' => User::class,
            'subject_id' => $newAdmin->id,
            'properties' => [
                'admin_name' => $newAdmin->name,
                'admin_email' => $newAdmin->email,
                'created_by' => $currentAdmin->name,
            ],
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', "Akun petugas '{$newAdmin->name}' berhasil ditambahkan dan siap digunakan untuk masuk.");
    }
}
