@extends('layouts.admin')

@section('title', 'Verifikasi Akte Kematian - ' . $death->registration_no)
@section('page_title', 'Detail & Verifikasi Permohonan Akte Kematian')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.death.index') }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. Registrasi:</span>
                <h3 class="text-base font-extrabold text-rose-700">{{ $death->registration_no }}</h3>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold px-3 py-1.5 rounded-full border {{ $death->status_badge_class }}">
                Status: {{ $death->status_label }}
            </span>
            <a href="{{ route('admin.death.print_letter', $death) }}" target="_blank" class="bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Cetak Surat Keterangan Kematian
            </a>
        </div>
    </div>

    <!-- Grid Content: Data & Verifikasi -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Kolom Data Permohonan (8 Kolom) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- 1. Data Almarhum/ah & KK -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-700 pb-2 border-b border-slate-100 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-user-xmark text-base"></i> 1. Data Kartu Keluarga & Almarhum / Almarhumah
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div><span class="text-slate-500 block text-[11px]">No. Kartu Keluarga:</span> <span class="font-mono font-bold text-slate-800">{{ $death->family_card_no ?? '-' }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Nama Kepala Keluarga:</span> <span class="font-semibold text-slate-800">{{ $death->head_of_family_name ?? '-' }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Nama Lengkap Almarhum/ah:</span> <strong class="text-slate-900 text-sm">{{ $death->deceased_name }}</strong></div>
                    <div><span class="text-slate-500 block text-[11px]">NIK Almarhum/ah:</span> <span class="font-mono font-bold text-slate-800">{{ $death->deceased_nik }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Jenis Kelamin:</span> <span class="font-medium text-slate-800">{{ $death->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Tempat, Tgl Lahir:</span> <span class="font-medium text-slate-800">{{ $death->deceased_birth_place ?? '-' }}, {{ $death->birth_date ? $death->birth_date->translatedFormat('d F Y') : '-' }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Agama:</span> <span class="font-medium text-slate-800">{{ $death->religion }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Anak Ke-:</span> <span class="font-medium text-slate-800">{{ $death->deceased_child_order ?? '-' }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Padukuhan Domisili:</span> <span class="font-medium text-slate-800">Padukuhan {{ $death->padukuhan }}, RT {{ $death->rt }} / RW {{ $death->rw }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Alamat KTP Lengkap:</span> <span class="font-medium text-slate-800">{{ $death->deceased_address ?? '-' }}</span></div>
                </div>
            </div>

            <!-- 2. Orang Tua Almarhum/ah (Ibu & Ayah) -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-700 pb-2 border-b border-slate-100 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-people-roof text-base"></i> 2. Data Orang Tua Almarhum / Almarhumah
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Ibu -->
                    <div class="p-3 bg-rose-50/40 rounded-lg border border-rose-100 space-y-1.5">
                        <span class="font-bold text-rose-800 block border-b border-rose-100 pb-1">Data Ibu:</span>
                        <div><span class="text-slate-500 text-[11px]">Nama:</span> <strong>{{ $death->mother_name ?: '-' }}</strong></div>
                        <div><span class="text-slate-500 text-[11px]">NIK:</span> <span class="font-mono">{{ $death->mother_nik ?: '-' }}</span></div>
                        <div><span class="text-slate-500 text-[11px]">Tempat, Tgl Lahir:</span> {{ $death->mother_birth_place ?: '-' }}, {{ $death->mother_birth_date ? $death->mother_birth_date->translatedFormat('d F Y') : '-' }}</div>
                        <div><span class="text-slate-500 text-[11px]">Pekerjaan:</span> {{ $death->mother_job ?: '-' }}</div>
                        <div><span class="text-slate-500 text-[11px]">Alamat:</span> {{ $death->mother_address ?: '-' }}</div>
                        <div><span class="text-slate-500 text-[11px]">Kewarganegaraan:</span> {{ $death->mother_citizenship ?: 'WNI' }}</div>
                    </div>
                    <!-- Ayah -->
                    <div class="p-3 bg-rose-50/40 rounded-lg border border-rose-100 space-y-1.5">
                        <span class="font-bold text-rose-800 block border-b border-rose-100 pb-1">Data Ayah:</span>
                        <div><span class="text-slate-500 text-[11px]">Nama:</span> <strong>{{ $death->father_name ?: '-' }}</strong></div>
                        <div><span class="text-slate-500 text-[11px]">NIK:</span> <span class="font-mono">{{ $death->father_nik ?: '-' }}</span></div>
                        <div><span class="text-slate-500 text-[11px]">Tempat, Tgl Lahir:</span> {{ $death->father_birth_place ?: '-' }}, {{ $death->father_birth_date ? $death->father_birth_date->translatedFormat('d F Y') : '-' }}</div>
                        <div><span class="text-slate-500 text-[11px]">Pekerjaan:</span> {{ $death->father_job ?: '-' }}</div>
                        <div><span class="text-slate-500 text-[11px]">Alamat:</span> {{ $death->father_address ?: '-' }}</div>
                        <div><span class="text-slate-500 text-[11px]">Kewarganegaraan:</span> {{ $death->father_citizenship ?: 'WNI' }}</div>
                    </div>
                </div>
            </div>

            <!-- 3. Peristiwa Kematian -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-700 pb-2 border-b border-slate-100 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-clock text-base"></i> 3. Rincian Peristiwa Kematian
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div><span class="text-slate-500 block text-[11px]">Hari / Tanggal Meninggal:</span> <span class="font-bold text-slate-800">{{ $death->death_day ? $death->death_day . ', ' : '' }}{{ $death->death_date->translatedFormat('d F Y') }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Waktu / Pukul:</span> <span class="font-medium text-slate-800">{{ $death->death_time ?? '-' }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Tempat Kejadian:</span> <span class="font-medium text-slate-800">{{ $death->death_place }} {{ $death->death_location_detail ? '(' . $death->death_location_detail . ')' : '' }}</span></div>
                    <div><span class="text-slate-500 block text-[11px]">Penyebab Kematian:</span> <span class="font-medium text-slate-800">{{ $death->cause_of_death }}</span></div>
                    <div class="sm:col-span-2"><span class="text-slate-500 block text-[11px]">Yang Menerangkan:</span> <span class="font-medium text-slate-800">{{ $death->reported_by_title }}</span></div>
                </div>
            </div>

            <!-- 4. Berkas Dokumen Terunggah -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-700 pb-2 border-b border-slate-100 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-folder-open text-base"></i> 4. Berkas Lampiran Pendukung (6 Dokumen)
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    
                    @php
                        $docs = [
                            '1. Surat Kematian (RS/Dokter/RT)' => [
                                'field' => 'doc_death_statement',
                                'path' => $death->doc_death_statement,
                            ],
                            '2. Surat Pengantar RT/RW' => [
                                'field' => 'doc_rt_rw_statement',
                                'path' => $death->doc_rt_rw_statement,
                            ],
                            '3. Kartu Keluarga (KK)' => [
                                'field' => 'doc_family_card',
                                'path' => $death->doc_family_card,
                            ],
                            '4. KTP Asli Almarhum/ah' => [
                                'field' => 'doc_deceased_ktp',
                                'path' => $death->doc_deceased_ktp,
                            ],
                            '5. KK Ahli Waris' => [
                                'field' => 'doc_heir_family_card',
                                'path' => $death->doc_heir_family_card,
                            ],
                            '6. KTP Ahli Waris' => [
                                'field' => 'doc_heir_ktp',
                                'path' => $death->doc_heir_ktp ?: $death->doc_applicant_ktp,
                            ],
                        ];
                    @endphp

                    @foreach($docs as $title => $doc)
                        @php
                            $path = $doc['path'];
                            $field = $doc['field'];
                            $isPdf = $path ? \Illuminate\Support\Str::endsWith(strtolower($path), '.pdf') : false;
                            $viewUrl = $path ? route('admin.death.attachment.view', ['death' => $death, 'field' => $field]) : '';
                            $downloadUrl = $path ? route('admin.death.attachment.download', ['death' => $death, 'field' => $field]) : '';
                        @endphp
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 flex items-center justify-between">
                            <div class="mr-2 overflow-hidden">
                                <p class="font-semibold text-slate-800 text-[11px] truncate">{{ $title }}</p>
                                <p class="text-[10px] text-slate-400">{{ $path ? ($isPdf ? 'Dokumen PDF Terlampir' : 'Berkas Gambar Terlampir') : 'Tidak Ada Dokumen' }}</p>
                            </div>
                            @if($path)
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" onclick="previewAttachment('{{ $viewUrl }}', '{{ $downloadUrl }}', '{{ addslashes($title) }}', {{ $isPdf ? 'true' : 'false' }});" class="bg-[#059cb8] hover:bg-[#095b8c] text-white font-bold text-[10px] px-2.5 py-1.5 rounded transition flex items-center gap-1 shadow-2xs cursor-pointer" title="Pratinjau Dokumen">
                                        <i class="fa-solid fa-eye"></i> Lihat
                                    </button>
                                    <a href="{{ $downloadUrl }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] px-2.5 py-1.5 rounded transition flex items-center gap-1 shadow-2xs" title="Unduh Berkas ke Komputer">
                                        <i class="fa-solid fa-download"></i> Unduh
                                    </a>
                                    <a href="{{ $viewUrl }}" target="_blank" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-[10px] px-2 py-1.5 rounded transition flex items-center gap-1 shadow-2xs" title="Buka di Tab Baru">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            @else
                                <span class="text-[10px] text-slate-400 italic">Kosong</span>
                            @endif
                        </div>
                    @endforeach

                </div>
            </div>

        </div>

        <!-- Kolom Verifikasi & Tindakan Petugas (4 Kolom) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Panel Aksi Verifikasi Status -->
            <div class="bg-white rounded-xl shadow-xs border-2 border-rose-200 p-5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-stamp text-rose-700"></i> Validasi Petugas
                </h4>

                <form id="statusDeathForm" action="{{ route('admin.death.update_status', $death) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ubah Status Permohonan</label>
                        <select name="status" id="statusDeathSelect" class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-rose-600 font-medium">
                            <option value="pending" {{ $death->status === 'pending' ? 'selected' : '' }}>1. Menunggu Verifikasi</option>
                            <option value="in_process" {{ ($death->status === 'in_process' || $death->status === 'verified') ? 'selected' : '' }}>2. Sedang Diproses</option>
                            <option value="revision" {{ $death->status === 'revision' ? 'selected' : '' }}>3. Revisi Berkas</option>
                            <option value="rejected" {{ $death->status === 'rejected' ? 'selected' : '' }}>4. Dibatalkan</option>
                            <option value="ready_for_pickup" {{ ($death->status === 'ready_for_pickup' || $death->status === 'completed') ? 'selected' : '' }}>5. Siap Diambil</option>
                            <option value="picked_up" {{ ($death->status === 'picked_up' || $death->status === 'archived') ? 'selected' : '' }}>6. Sudah Diambil (Masuk Arsip)</option>
                        </select>
                    </div>

                    <!-- Warning jika status diubah namun catatan belum diganti -->
                    <div id="statusDeathChangeWarning" class="hidden p-3 bg-amber-50 border border-amber-300 rounded-lg text-[11px] text-amber-900 flex items-start gap-2 animate-fadeIn">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <p class="font-bold">Perhatian: Status Pengajuan Diubah</p>
                            <p class="text-amber-800">Silakan perbarui catatan / pesan verifikator di bawah sesuai dengan status baru sebelum menyimpan.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Catatan Verifikator / Alasan <span class="text-rose-600 font-bold">* (Wajib diisi)</span>
                        </label>
                        <textarea name="rejection_note" id="rejectionDeathNote" rows="3" required placeholder="Tuliskan catatan verifikasi, instruksi pengambilan berkas, atau alasan perubahan status..." class="w-full text-xs px-3 py-2 rounded-lg border @error('rejection_note') border-rose-500 ring-2 ring-rose-200 @else border-slate-300 @enderror focus:outline-none focus:ring-2 focus:ring-rose-600 transition">{{ old('rejection_note', $death->rejection_note) }}</textarea>
                        @error('rejection_note')
                            <p class="text-rose-600 text-[11px] mt-1 font-medium">{{ $message }}</p>
                        @enderror
                        <p id="noteDeathJsError" class="hidden text-rose-600 text-[11px] mt-1 font-medium"></p>
                    </div>

                    <div class="text-[11px] text-slate-500">
                        <p>Petugas Verifikator: <strong>{{ Auth::user()->name }}</strong></p>
                    </div>

                    <button type="button" id="openModalSubmitBtn" class="w-full bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Status
                    </button>
                </form>

                @if($death->status === 'rejected' || $death->status === 'picked_up' || $death->status === 'archived')
                    <form action="{{ route('admin.archive.death.archive', $death) }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin MENGARSIPKAN pengajuan akte kematian ini secara manual?');" class="btn-archive w-full bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs py-2.5 rounded-lg shadow-xs transition flex items-center justify-center gap-1.5" style="background-color: #334155; color: #ffffff;">
                            <i class="fa-solid fa-box-archive text-amber-300"></i> Arsipkan Pengajuan
                        </button>
                    </form>
                @endif
            </div>

            <!-- Ringkasan Pelapor & Saksi -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5 text-xs space-y-3">
                <h4 class="font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-1.5">
                    <i class="fa-solid fa-address-card text-rose-700"></i> Data Pelapor (Ahli Waris)
                </h4>
                <div><span class="text-slate-400 block text-[11px]">Nama Pelapor:</span> <strong class="text-slate-800">{{ $death->applicant_name }}</strong></div>
                <div><span class="text-slate-400 block text-[11px]">NIK Pelapor:</span> <span class="font-mono font-semibold">{{ $death->applicant_nik }}</span></div>
                <div><span class="text-slate-400 block text-[11px]">Tempat, Tgl Lahir / Umur:</span> <span>{{ $death->applicant_birth_place ?: '-' }}, {{ $death->applicant_birth_date ? $death->applicant_birth_date->translatedFormat('d F Y') : '-' }} ({{ $death->applicant_age ? $death->applicant_age . ' Thn' : '-' }})</span></div>
                <div><span class="text-slate-400 block text-[11px]">Pekerjaan:</span> <span>{{ $death->applicant_job ?: '-' }}</span></div>
                <div><span class="text-slate-400 block text-[11px]">Alamat KTP:</span> <span>{{ $death->applicant_address ?: '-' }}</span></div>
                <div><span class="text-slate-400 block text-[11px]">Hubungan:</span> <span class="font-semibold text-rose-700">{{ $death->applicant_relation }}</span></div>
                <div><span class="text-slate-400 block text-[11px]">Tgl Lapor:</span> <span>{{ $death->report_date ? $death->report_date->translatedFormat('d F Y') : '-' }}</span></div>
                <div><span class="text-slate-400 block text-[11px]">No. HP / Telepon:</span> <span class="font-bold text-slate-800">{{ $death->applicant_phone }}</span></div>

                <!-- Saksi I & Saksi II -->
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <h5 class="font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1">
                        <i class="fa-solid fa-users text-rose-600"></i> Saksi-Saksi Kematian:
                    </h5>
                    
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200 space-y-1">
                        <span class="text-[10px] font-bold text-rose-800 block">SAKSI I:</span>
                        <div class="text-[11px]"><strong class="text-slate-800">{{ $death->witness1_name ?: ($death->witness_name ?: '-') }}</strong></div>
                        <div class="text-[10px] text-slate-500 font-mono">NIK: {{ $death->witness1_nik ?: ($death->witness_nik ?: '-') }} &bull; Umur: {{ $death->witness1_age ? $death->witness1_age . ' Thn' : '-' }}</div>
                        @if($death->witness1_address)
                            <div class="text-[10px] text-slate-500">Alamat: {{ $death->witness1_address }}</div>
                        @endif
                    </div>

                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200 space-y-1">
                        <span class="text-[10px] font-bold text-rose-800 block">SAKSI II:</span>
                        <div class="text-[11px]"><strong class="text-slate-800">{{ $death->witness2_name ?: '-' }}</strong></div>
                        <div class="text-[10px] text-slate-500 font-mono">NIK: {{ $death->witness2_nik ?: '-' }} &bull; Umur: {{ $death->witness2_age ? $death->witness2_age . ' Thn' : '-' }}</div>
                        @if($death->witness2_address)
                            <div class="text-[10px] text-slate-500">Alamat: {{ $death->witness2_address }}</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusForm = document.getElementById('statusDeathForm');
    const statusSelect = document.getElementById('statusDeathSelect');
    const rejectionNote = document.getElementById('rejectionDeathNote');
    const statusChangeWarning = document.getElementById('statusDeathChangeWarning');
    const noteJsError = document.getElementById('noteDeathJsError');
    const openModalSubmitBtn = document.getElementById('openModalSubmitBtn');

    if (statusForm && statusSelect && rejectionNote) {
        const initialStatus = statusSelect.value;
        const initialNote = @json((string) $death->rejection_note).trim();

        function validateNoteChange() {
            const currentStatus = statusSelect.value;
            const currentNote = rejectionNote.value.trim();
            const isStatusChanged = (currentStatus !== initialStatus);
            const isNoteUnchanged = (initialNote !== '' && currentNote === initialNote);

            if (isStatusChanged && isNoteUnchanged) {
                if (statusChangeWarning) statusChangeWarning.classList.remove('hidden');
                return false;
            } else {
                if (statusChangeWarning) statusChangeWarning.classList.add('hidden');
                if (noteJsError) noteJsError.classList.add('hidden');
                rejectionNote.classList.remove('border-rose-500', 'ring-2', 'ring-rose-400');
                return true;
            }
        }

        statusSelect.addEventListener('change', validateNoteChange);
        rejectionNote.addEventListener('input', validateNoteChange);

        if (openModalSubmitBtn) {
            openModalSubmitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                const currentStatus = statusSelect.value;
                const currentNote = rejectionNote.value.trim();
                const isStatusChanged = (currentStatus !== initialStatus);
                const isNoteUnchanged = (initialNote !== '' && currentNote === initialNote);

                if (isStatusChanged && isNoteUnchanged) {
                    if (statusChangeWarning) statusChangeWarning.classList.remove('hidden');
                    if (noteJsError) {
                        noteJsError.textContent = 'Peringatan: Status pengajuan telah diubah, silakan perbarui Catatan Verifikator / Pesan terlebih dahulu.';
                        noteJsError.classList.remove('hidden');
                    }
                    rejectionNote.classList.add('border-rose-500', 'ring-2', 'ring-rose-400');
                    rejectionNote.focus();
                    alert('Peringatan: Status pengajuan telah diubah tetapi pesan/catatan verifikator masih belum diganti. Silakan ubah catatan terlebih dahulu sebelum menyimpan.');
                    return;
                }

                if (currentNote.length < 3) {
                    rejectionNote.focus();
                    rejectionNote.reportValidity();
                    return;
                }

                openModalSubmitBtn.disabled = true;
                openModalSubmitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';
                statusForm.submit();
            });
        }
    }

    // Modal Preview Lampiran Interaktif
    const modalPreviewDoc = document.getElementById('modal-preview-doc');
    const modalPreviewImg = document.getElementById('modal-preview-img');
    const modalImgContainer = document.getElementById('modal-img-container');
    const modalZoomControls = document.getElementById('modal-zoom-controls');
    const zoomLevelLabel = document.getElementById('zoom-level-label');
    const btnZoomIn = document.getElementById('btn-zoom-in');
    const btnZoomOut = document.getElementById('btn-zoom-out');
    const btnZoomRotate = document.getElementById('btn-zoom-rotate');
    const btnZoomReset = document.getElementById('btn-zoom-reset');
    const modalPdfContainer = document.getElementById('modal-pdf-container');
    const modalPdfFrame = document.getElementById('modal-pdf-frame');
    const modalPreviewTitle = document.getElementById('modal-preview-title');
    const modalPreviewSubtitle = document.getElementById('modal-preview-subtitle');
    const modalFileInfo = document.getElementById('modal-file-info');
    const modalBtnOpenTab = document.getElementById('modal-btn-open-tab');
    const modalBtnDownload = document.getElementById('modal-btn-download');
    const modalFooterDownload = document.getElementById('modal-footer-download');
    const modalPdfDownload = document.getElementById('modal-pdf-download');
    const modalPdfFallbackDownload = document.getElementById('modal-pdf-fallback-download');
    const modalPdfFallbackTab = document.getElementById('modal-pdf-fallback-tab');
    const btnCloseModalDoc = document.getElementById('btn-close-modal-doc');
    const btnCloseModalDocFooter = document.getElementById('btn-close-modal-doc-footer');

    let zoomScale = 1.0;
    let panX = 0;
    let panY = 0;
    let currentRotation = 0;
    let isDraggingImg = false;
    let dragStartX = 0;
    let dragStartY = 0;

    function applyPanZoomTransform() {
        if (!modalPreviewImg) return;
        modalPreviewImg.style.transform = `translate(${panX}px, ${panY}px) scale(${zoomScale}) rotate(${currentRotation}deg)`;
        if (zoomLevelLabel) {
            zoomLevelLabel.textContent = Math.round(zoomScale * 100) + '%';
        }
    }

    function resetPanZoom() {
        zoomScale = 1.0;
        panX = 0;
        panY = 0;
        currentRotation = 0;
        applyPanZoomTransform();
    }

    function zoomIn() {
        zoomScale = Math.min(5.0, Number((zoomScale + 0.25).toFixed(2)));
        applyPanZoomTransform();
    }

    function zoomOut() {
        zoomScale = Math.max(0.4, Number((zoomScale - 0.25).toFixed(2)));
        applyPanZoomTransform();
    }

    function rotateImg() {
        currentRotation = (currentRotation + 90) % 360;
        applyPanZoomTransform();
    }

    if (btnZoomIn) btnZoomIn.addEventListener('click', zoomIn);
    if (btnZoomOut) btnZoomOut.addEventListener('click', zoomOut);
    if (btnZoomRotate) btnZoomRotate.addEventListener('click', rotateImg);
    if (btnZoomReset) btnZoomReset.addEventListener('click', resetPanZoom);

    if (modalImgContainer) {
        modalImgContainer.addEventListener('mousedown', function(e) {
            if (e.target.closest('#modal-zoom-controls')) return;
            isDraggingImg = true;
            dragStartX = e.clientX - panX;
            dragStartY = e.clientY - panY;
            modalImgContainer.classList.add('cursor-grabbing');
            modalImgContainer.classList.remove('cursor-grab');
            e.preventDefault();
        });

        window.addEventListener('mousemove', function(e) {
            if (!isDraggingImg) return;
            panX = e.clientX - dragStartX;
            panY = e.clientY - dragStartY;
            applyPanZoomTransform();
        });

        window.addEventListener('mouseup', function() {
            if (isDraggingImg) {
                isDraggingImg = false;
                if (modalImgContainer) {
                    modalImgContainer.classList.remove('cursor-grabbing');
                    modalImgContainer.classList.add('cursor-grab');
                }
            }
        });

        modalImgContainer.addEventListener('wheel', function(e) {
            e.preventDefault();
            if (e.deltaY < 0) {
                zoomIn();
            } else {
                zoomOut();
            }
        }, { passive: false });

        modalImgContainer.addEventListener('dblclick', function(e) {
            if (e.target.closest('#modal-zoom-controls')) return;
            if (zoomScale > 1.0) {
                resetPanZoom();
            } else {
                zoomScale = 2.0;
                applyPanZoomTransform();
            }
        });
    }

    window.previewAttachment = function(viewUrl, downloadUrl, title, isPdf) {
        if (!modalPreviewDoc || !viewUrl) return;
        resetPanZoom();

        if (modalPreviewTitle) modalPreviewTitle.textContent = title || 'Pratinjau Berkas Lampiran';
        if (modalFileInfo) modalFileInfo.textContent = title + (isPdf ? ' (Format PDF)' : ' (Format Gambar)');
        if (modalBtnOpenTab) modalBtnOpenTab.href = viewUrl;
        if (modalBtnDownload) modalBtnDownload.href = downloadUrl || viewUrl;
        if (modalPdfDownload) modalPdfDownload.href = viewUrl;
        if (modalFooterDownload) modalFooterDownload.href = downloadUrl || viewUrl;
        if (modalPdfFallbackDownload) modalPdfFallbackDownload.href = downloadUrl || viewUrl;
        if (modalPdfFallbackTab) modalPdfFallbackTab.href = viewUrl;

        if (isPdf) {
            if (modalImgContainer) modalImgContainer.classList.add('hidden');
            if (modalZoomControls) modalZoomControls.classList.add('hidden');
            if (modalPdfContainer) modalPdfContainer.classList.remove('hidden');
            if (modalPdfFrame) modalPdfFrame.src = viewUrl;
            if (modalPreviewSubtitle) modalPreviewSubtitle.textContent = 'Dokumen berformat PDF • Dapat dilihat dan diunduh langsung';
        } else {
            if (modalPdfContainer) modalPdfContainer.classList.add('hidden');
            if (modalPdfFrame) modalPdfFrame.src = 'about:blank';
            if (modalImgContainer) modalImgContainer.classList.remove('hidden');
            if (modalZoomControls) modalZoomControls.classList.remove('hidden');
            if (modalPreviewImg) {
                modalPreviewImg.src = viewUrl;
            }
            if (modalPreviewSubtitle) modalPreviewSubtitle.textContent = 'Scroll untuk Zoom • Drag untuk Menggeser Posisi';
        }

        modalPreviewDoc.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    function closeDocModal() {
        if (!modalPreviewDoc) return;
        modalPreviewDoc.classList.add('hidden');
        if (modalPdfFrame) modalPdfFrame.src = 'about:blank';
        document.body.style.overflow = '';
    }

    if (btnCloseModalDoc) btnCloseModalDoc.addEventListener('click', closeDocModal);
    if (btnCloseModalDocFooter) btnCloseModalDocFooter.addEventListener('click', closeDocModal);
    if (modalPreviewDoc) {
        modalPreviewDoc.addEventListener('click', function(e) {
            if (e.target === modalPreviewDoc) closeDocModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalPreviewDoc && !modalPreviewDoc.classList.contains('hidden')) {
            closeDocModal();
        }
    });
});
</script>

<!-- MODAL PREVIEW BERKAS LAMPIRAN -->
<div id="modal-preview-doc" class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4 hidden transition-opacity">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-700/50 max-w-5xl w-full overflow-hidden my-auto flex flex-col h-[90vh] max-h-[92vh]">
        <!-- Header Modal -->
        <div class="px-5 py-3.5 bg-[#095b8c] text-white flex items-center justify-between shrink-0 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                    <i class="fa-solid fa-file-lines text-teal-200 text-base"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white tracking-wide" id="modal-preview-title">Pratinjau Berkas Lampiran</h4>
                    <p class="text-[10px] sm:text-[11px] text-teal-100 truncate" id="modal-preview-subtitle">Scroll untuk Zoom • Drag untuk Menggeser Posisi</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <!-- Toolbar Zoom Gambar -->
                <div id="modal-zoom-controls" class="flex items-center bg-black/25 backdrop-blur-md rounded-xl p-1 border border-white/20">
                    <button type="button" id="btn-zoom-out" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" title="Perkecil (Zoom Out)">
                        <i class="fa-solid fa-minus text-xs"></i>
                    </button>
                    <span id="zoom-level-label" class="text-xs font-mono font-bold px-2 text-teal-200 min-w-[46px] text-center select-none">100%</span>
                    <button type="button" id="btn-zoom-in" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" title="Perbesar (Zoom In)">
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                    <div class="h-4 w-px bg-white/20 mx-1"></div>
                    <button type="button" id="btn-zoom-rotate" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" title="Putar 90°">
                        <i class="fa-solid fa-rotate-right text-xs"></i>
                    </button>
                    <button type="button" id="btn-zoom-reset" class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer" title="Reset Posisi & Zoom">
                        <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    </button>
                </div>

                <!-- Tombol Unduh Header -->
                <a id="modal-btn-download" href="#" class="w-8 h-8 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center transition cursor-pointer shadow-xs" title="Unduh Berkas ke Komputer">
                    <i class="fa-solid fa-download text-xs"></i>
                </a>

                <a id="modal-btn-open-tab" href="#" target="_blank" class="w-8 h-8 rounded-lg bg-white/15 hover:bg-white/30 text-teal-100 hover:text-white flex items-center justify-center transition cursor-pointer" title="Buka di Tab Baru">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                </a>

                <button type="button" id="btn-close-modal-doc" class="w-8 h-8 rounded-lg bg-white/15 hover:bg-white/30 text-teal-100 hover:text-white flex items-center justify-center transition cursor-pointer" title="Tutup">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Body Modal Viewport Interaktif -->
        <div class="relative flex-1 bg-slate-950 overflow-hidden flex items-center justify-center select-none">
            <!-- Image Pan & Zoom Stage -->
            <div id="modal-img-container" class="w-full h-full relative overflow-hidden flex items-center justify-center cursor-grab active:cursor-grabbing bg-slate-900/95" style="touch-action: none; min-height: 250px;">
                <div class="w-full h-full flex items-center justify-center pointer-events-none p-4">
                    <img id="modal-preview-img" 
                         src="" 
                         alt="Pratinjau Dokumen" 
                         class="max-h-[72vh] max-w-[88%] w-auto h-auto rounded-md shadow-2xl object-contain block border border-slate-700 bg-white pointer-events-none select-none transition-transform duration-75 ease-out"
                         style="transform-origin: center center;">
                </div>
            </div>

            <!-- PDF Container -->
            <div id="modal-pdf-container" class="hidden w-full h-full flex flex-col bg-slate-900">
                <div class="px-4 py-2 bg-slate-800/90 border-b border-slate-700 flex items-center justify-between text-xs text-slate-300 shrink-0">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-file-pdf text-rose-400"></i> Dokumen PDF Terlampir</span>
                    <div class="flex items-center gap-2">
                        <a id="modal-pdf-fallback-download" href="#" class="text-[11px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-3 py-1 rounded-lg transition flex items-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-download"></i> Unduh PDF
                        </a>
                        <a id="modal-pdf-fallback-tab" href="#" target="_blank" class="text-[11px] font-bold text-white bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded-lg transition flex items-center gap-1">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Layar Penuh
                        </a>
                    </div>
                </div>
                <iframe id="modal-pdf-frame" src="" class="w-full h-full flex-1 border-0 bg-slate-800"></iframe>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2 overflow-hidden mr-3">
                <i class="fa-solid fa-file-circle-check text-emerald-600 shrink-0"></i>
                <span class="text-xs text-slate-700 truncate font-medium" id="modal-file-info">-</span>
            </div>
            <div class="flex items-center gap-2">
                <a id="modal-footer-download" href="#" class="text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 px-3.5 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-download text-xs"></i> Unduh Berkas
                </a>
                <a id="modal-pdf-download" href="#" target="_blank" class="text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-300 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> Tab Baru
                </a>
                <button type="button" id="btn-close-modal-doc-footer" class="text-xs font-bold bg-[#059cb8] hover:bg-[#095b8c] text-white px-4 py-2 rounded-xl shadow-xs transition cursor-pointer">
                    Tutup Pratinjau
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
