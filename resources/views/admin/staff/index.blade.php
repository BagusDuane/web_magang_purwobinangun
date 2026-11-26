@extends('layouts.admin')

@section('title', 'Kelola Petugas Kalurahan')
@section('page_title', 'Kelola & Tambah Akun Petugas')

@section('content')
<div class="space-y-6">

    <!-- Statistik Singkat Petugas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Petugas</span>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-teal-50 text-[#0b7c89] text-xl">
                <i class="fa-solid fa-user-shield"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-2xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Petugas Aktif</span>
                <p class="text-2xl font-black text-emerald-700 mt-1">{{ $stats['active'] }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600 text-xl">
                <i class="fa-solid fa-shield-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs flex items-center justify-between sm:col-span-2 md:col-span-1">
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Hak Akses</span>
                <p class="text-sm font-bold text-slate-800 mt-1">Administrator Pelayanan</p>
            </div>
            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-sky-50 text-sky-600 text-xl">
                <i class="fa-solid fa-key"></i>
            </div>
        </div>
    </div>

    <!-- Layout Grid: Form Tambah Petugas (Kiri) & Daftar Petugas (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Kolom Kiri: Form Tambah Petugas/Admin -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-2xs p-6">
            <div class="pb-4 mb-5 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white shrink-0" style="background-color: #0b7c89;">
                        <i class="fa-solid fa-user-plus text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base leading-tight">Tambah Petugas Baru</h3>
                        <p class="text-xs text-slate-500">Buat akun admin untuk staf pelayanan desa</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Lengkap Petugas <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative" style="position: relative;">
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="Contoh: Budi Santoso, S.Kom"
                            class="w-full text-xs rounded-xl border {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-[#0b7c89]/20 focus:border-[#0b7c89] transition"
                            style="padding-left: 1rem; padding-right: 2.5rem; padding-top: 0.625rem; padding-bottom: 0.625rem;">
                        <span class="text-slate-400 pointer-events-none flex items-center justify-center" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%);">
                            <i class="fa-solid fa-user text-xs"></i>
                        </span>
                    </div>
                    @error('name')
                        <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Petugas -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1">
                        Alamat Email (Login) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative" style="position: relative;">
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            placeholder="petugas@purwobinangun.desa.id"
                            class="w-full text-xs rounded-xl border {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-[#0b7c89]/20 focus:border-[#0b7c89] transition"
                            style="padding-left: 1rem; padding-right: 2.5rem; padding-top: 0.625rem; padding-bottom: 0.625rem;">
                        <span class="text-slate-400 pointer-events-none flex items-center justify-center" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%);">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </span>
                    </div>
                    @error('email')
                        <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor Telepon/WA -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor HP / WhatsApp <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <div class="relative" style="position: relative;">
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            placeholder="081234567890"
                            class="w-full text-xs rounded-xl border {{ $errors->has('phone') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-[#0b7c89]/20 focus:border-[#0b7c89] transition"
                            style="padding-left: 1rem; padding-right: 2.5rem; padding-top: 0.625rem; padding-bottom: 0.625rem;">
                        <span class="text-slate-400 pointer-events-none flex items-center justify-center" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%);">
                            <i class="fa-solid fa-phone text-xs"></i>
                        </span>
                    </div>
                    @error('phone')
                        <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kata Sandi -->
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative" style="position: relative;">
                        <input type="password" name="password" id="password" required minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="w-full text-xs rounded-xl border {{ $errors->has('password') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-300' }} focus:outline-none focus:ring-2 focus:ring-[#0b7c89]/20 focus:border-[#0b7c89] transition"
                            style="padding-left: 1rem; padding-right: 2.5rem; padding-top: 0.625rem; padding-bottom: 0.625rem;">
                        <span class="text-slate-400 pointer-events-none flex items-center justify-center" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%);">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                    </div>
                    @error('password')
                        <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Kata Sandi -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">
                        Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative" style="position: relative;">
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6"
                            placeholder="Ulangi kata sandi"
                            class="w-full text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0b7c89]/20 focus:border-[#0b7c89] transition"
                            style="padding-left: 1rem; padding-right: 2.5rem; padding-top: 0.625rem; padding-bottom: 0.625rem;">
                        <span class="text-slate-400 pointer-events-none flex items-center justify-center" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%);">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                        </span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full text-white font-bold text-xs py-2.5 px-5 rounded-xl shadow-sm hover:shadow transition flex items-center justify-center gap-2 cursor-pointer"
                        style="background-color: #0b7c89;">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Simpan Akun Petugas</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan: Tabel Daftar Petugas / Admin -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
            
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-base leading-tight">Daftar Akun Petugas/Admin</h3>
                    <p class="text-xs text-slate-500">Semua staf kalurahan yang memiliki akses ke sistem panel admin</p>
                </div>

                <!-- Pencarian Petugas -->
                <form action="{{ route('admin.staff.index') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative" style="position: relative;">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama / email..."
                            class="text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#0b7c89]/20 focus:border-[#0b7c89] transition"
                            style="padding-left: 2.25rem; padding-right: 0.85rem; padding-top: 0.45rem; padding-bottom: 0.45rem;">
                        <span class="text-slate-400 pointer-events-none flex items-center justify-center" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.staff.index') }}" class="text-xs text-slate-500 hover:text-slate-700 px-2 py-1.5 rounded-lg border border-slate-200">Reset</a>
                    @endif
                </form>
            </div>

            <!-- Tabel Petugas -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold">
                            <th class="py-3 px-4">Nama Petugas</th>
                            <th class="py-3 px-4">Kontak Login</th>
                            <th class="py-3 px-4">Status Akun</th>
                            <th class="py-3 px-4">Terdaftar</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($admins as $adminItem)
                            <tr class="hover:bg-slate-50/60 transition {{ Auth::id() === $adminItem->id ? 'bg-teal-50/30' : '' }}">
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0" style="background-color: #0b7c89;">
                                            {{ strtoupper(substr($adminItem->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span>{{ $adminItem->name }}</span>
                                            @if(Auth::id() === $adminItem->id)
                                                <span class="text-[10px] font-bold text-[#0b7c89] bg-teal-50 px-1.5 py-0.5 rounded border border-teal-200 ml-1">Akun Anda</span>
                                            @endif
                                            <div class="text-[11px] text-slate-400 font-normal">Peran: Petugas Admin</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-envelope text-slate-400 text-[10px]"></i>
                                        <span>{{ $adminItem->email }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                        <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                                        <span>{{ $adminItem->phone ?: 'Tidak diatur' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($adminItem->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i> Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-300">
                                            <i class="fa-solid fa-user-slash text-[10px]"></i> Nonaktif
                                        </span>
                                        @if($adminItem->rejection_reason)
                                            <div class="text-[10px] text-rose-600 mt-1 italic">{{ $adminItem->rejection_reason }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    <span>{{ $adminItem->created_at ? $adminItem->created_at->format('d/m/Y') : '-' }}</span>
                                    <div class="text-[10px] text-slate-400">{{ $adminItem->created_at ? $adminItem->created_at->diffForHumans() : '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if(Auth::id() === $adminItem->id)
                                        <span class="text-[10px] font-semibold text-slate-400 italic">Sedang Aktif</span>
                                    @else
                                        <div class="inline-flex items-center justify-center gap-1.5">
                                            <form action="{{ route('admin.staff.toggle_status', $adminItem) }}" method="POST" class="inline" onsubmit="return handleStaffToggle(this, @js($adminItem->name), @js($adminItem->status === 'active'));">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="reason" value="">
                                                @if($adminItem->status === 'active')
                                                    <button type="submit" class="bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold text-[11px] px-2.5 py-1.5 rounded-lg transition cursor-pointer" title="Nonaktifkan petugas (Pindah Tugas / Pensiun)">
                                                        <i class="fa-solid fa-user-clock"></i> Nonaktifkan
                                                    </button>
                                                @else
                                                    <button type="submit" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-[11px] px-2.5 py-1.5 rounded-lg transition cursor-pointer" title="Aktifkan kembali akun petugas">
                                                        <i class="fa-solid fa-rotate-left"></i> Aktifkan
                                                    </button>
                                                @endif
                                            </form>

                                            <form action="{{ route('admin.staff.destroy', $adminItem) }}" method="POST" class="inline" onsubmit="return handleStaffDelete(this, @js($adminItem->name));">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="reason" value="">
                                                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] px-2.5 py-1.5 rounded-lg transition cursor-pointer" title="Hapus permanen akun petugas (Tidak Aktif / Pindah Tugas / Pensiun)">
                                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-user-slash text-2xl mb-2 block"></i>
                                    <span>Tidak ada akun petugas yang ditemukan.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admins->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $admins->links() }}
                </div>
            @endif

        </div>

    </div>

</div>

<script>
function handleStaffToggle(form, staffName, isDeactivating) {
    if (isDeactivating) {
        const reason = prompt('Tuliskan keterangan penonaktifan akun petugas "' + staffName + '" (misal: Pindah Tugas / Pensiun / Tidak Aktif):', 'Pindah Tugas / Pensiun');
        if (reason === null) return false;
        form.querySelector('input[name="reason"]').value = reason.trim() || 'Pindah Tugas / Pensiun';
        return true;
    }
    return confirm('Aktifkan kembali akun petugas "' + staffName + '"?');
}

function handleStaffDelete(form, staffName) {
    const reason = prompt('HAPUS PERMANEN akun petugas "' + staffName + '".\nMasukkan alasan penghapusan (misal: Pensiun / Pindah Tugas / Tidak Aktif):', 'Pindah Tugas / Pensiun');
    if (reason === null) return false;
    form.querySelector('input[name="reason"]').value = reason.trim() || 'Tidak Aktif / Pindah Tugas / Pensiun';
    return confirm('Apakah Anda yakin ingin MENGHAPUS PERMANEN akun petugas "' + staffName + '"? Tindakan ini tidak dapat dibatalkan.');
}
</script>
@endsection
