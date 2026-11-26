<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BirthCertificate;
use App\Services\CertificateExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBirthController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        // Pengajuan yang sudah diambil / diarsipkan hanya muncul di menu Arsip
        $query = BirthCertificate::where('is_archived', false)->whereNotIn('status', ['picked_up', 'archived']);

        if ($status && !in_array($status, ['picked_up', 'archived'])) {
            if ($status === 'in_process') {
                $query->whereIn('status', ['in_process', 'verified']);
            } elseif ($status === 'ready_for_pickup') {
                $query->whereIn('status', ['ready_for_pickup', 'completed']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_no', 'like', "%{$search}%")
                  ->orWhere('child_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mother_name', 'like', "%{$search}%")
                  ->orWhere('applicant_nik', 'like', "%{$search}%");
            });
        }

        $births = $query->latest()->paginate(10)->withQueryString();

        return view('admin.birth.index', compact('births', 'status', 'search'));
    }

    public function show(BirthCertificate $birth)
    {
        return view('admin.birth.show', compact('birth'));
    }

    public function updateStatus(Request $request, BirthCertificate $birth)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_process,revision,rejected,ready_for_pickup,picked_up,verified,completed,archived',
            'rejection_note' => 'required|string|min:3',
        ], [
            'rejection_note.required' => 'Catatan verifikator / alasan perubahan status wajib diisi.',
            'rejection_note.min' => 'Catatan verifikator minimal berisi 3 karakter.',
        ]);

        // Normalisasi status awal dan status baru untuk perbandingan
        $statusMap = [
            'verified' => 'in_process',
            'completed' => 'ready_for_pickup',
            'archived' => 'picked_up',
        ];
        $currentNormalizedStatus = $statusMap[$birth->status] ?? $birth->status;
        $newNormalizedStatus = $statusMap[$validated['status']] ?? $validated['status'];

        $isStatusChanged = $currentNormalizedStatus !== $newNormalizedStatus;
        $initialNote = trim((string)$birth->rejection_note);
        $newNote = trim((string)$validated['rejection_note']);

        if ($isStatusChanged && $initialNote !== '' && $initialNote === $newNote) {
            return back()->withErrors([
                'rejection_note' => 'Status pengajuan telah diubah. Silakan perbarui Catatan Verifikator / Pesan terlebih dahulu sesuai status yang baru.'
            ])->withInput();
        }

        $birth->status = $validated['status'];
        $birth->is_archived = in_array($validated['status'], ['picked_up', 'archived']);
        $birth->rejection_note = $validated['rejection_note'];
        $birth->processed_by = Auth::user()->name;
        $birth->save();

        ActivityLog::record([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_role' => 'admin',
            'action_type' => in_array($validated['status'], ['picked_up', 'completed', 'ready_for_pickup']) ? 'approve' : ($validated['status'] === 'rejected' ? 'reject' : 'update'),
            'module' => 'birth_certificate',
            'title' => 'Pembaruan Status Akte Kelahiran',
            'description' => 'Pembaruan status permohonan menjadi "' . $birth->status_label . '".' . (!empty($validated['rejection_note']) ? ' Catatan: ' . $validated['rejection_note'] : ''),
            'reference_no' => $birth->registration_no,
            'subject_type' => BirthCertificate::class,
            'subject_id' => $birth->id,
        ]);

        $message = $birth->isPickedUp()
            ? 'Status permohonan Akte Kelahiran berhasil diperbarui menjadi Sudah diambil dan telah otomatis masuk ke arsip. Pemberitahuan telah ditampilkan di portal notifikasi warga.'
            : 'Status permohonan Akte Kelahiran berhasil diperbarui menjadi ' . $birth->status_label . '. Pemberitahuan telah ditampilkan di portal notifikasi warga.';

        return redirect()->route('admin.birth.show', $birth)
            ->with('success', $message);
    }

    public function destroy(Request $request, BirthCertificate $birth)
    {
        $regNo = $birth->registration_no;
        $childName = $birth->child_name;
        $applicantName = $birth->applicant_name;
        $wasArchived = $birth->isArchived();
        $reason = trim((string) $request->input('reason', 'Data warga pindah domisili / pembersihan arsip'));

        $attachmentFields = [
            'doc_birth_cert',
            'doc_family_card',
            'doc_marriage_cert',
            'doc_parents_ktp',
            'doc_witness_ktp',
            'applicant_signature',
        ];

        foreach ($attachmentFields as $field) {
            $path = $birth->$field;
            if ($path && !str_starts_with($path, 'data:image')) {
                $cleanPath = ltrim(preg_replace('#^(public/|storage/)#', '', $path), '/\\');
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($cleanPath);
                }
            }
        }

        ActivityLog::where('subject_type', BirthCertificate::class)
            ->where('subject_id', $birth->id)
            ->update([
                'subject_type' => null,
                'subject_id' => null,
            ]);

        $birth->delete();

        ActivityLog::record([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_role' => 'admin',
            'action_type' => 'delete',
            'module' => 'birth_certificate',
            'title' => 'Penghapusan Data Pengajuan Akte Kelahiran',
            'description' => "Menghapus permanen data pengajuan Akte Kelahiran {$regNo} (Bayi: {$childName}, Pelapor: {$applicantName}). Alasan: {$reason}.",
            'reference_no' => $regNo,
            'properties' => [
                'child_name' => $childName,
                'applicant_name' => $applicantName,
                'reason' => $reason,
            ],
        ]);

        if ($request->input('from') === 'archive' || $wasArchived) {
            return redirect()->route('admin.archive.index', ['tab' => 'birth'])
                ->with('success', "Data pengajuan Akte Kelahiran ({$regNo} - {$childName}) berhasil dihapus secara permanen.");
        }

        return redirect()->route('admin.birth.index')
            ->with('success', "Data pengajuan Akte Kelahiran ({$regNo} - {$childName}) berhasil dihapus secara permanen.");
    }

    public function printLetter(BirthCertificate $birth)
    {
        return view('admin.birth.print-letter', compact('birth'));
    }

    public function export(BirthCertificate $birth, string $format, CertificateExportService $exportService)
    {
        return $exportService->export('birth', $birth, $format);
    }

    public function viewAttachment(BirthCertificate $birth, string $field)
    {
        $allowed = [
            'doc_birth_cert',
            'doc_family_card',
            'doc_marriage_cert',
            'doc_parents_ktp',
            'doc_witness_ktp',
            'applicant_signature',
        ];

        if (!in_array($field, $allowed)) {
            abort(404, 'Jenis berkas lampiran tidak valid.');
        }

        $path = $birth->$field;
        if (!$path) {
            abort(404, 'Berkas belum diunggah.');
        }

        $filePath = $this->resolveFilePath($path);
        if (!$filePath || !file_exists($filePath)) {
            abort(404, 'Berkas fisik lampiran tidak ditemukan pada server.');
        }

        $mime = null;
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filePath);
        }
        if (!$mime || $mime === 'application/x-empty') {
            $mime = match (strtolower(pathinfo($filePath, PATHINFO_EXTENSION))) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                default => 'application/octet-stream',
            };
        }

        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            'Cache-Control' => 'no-cache, private',
        ]);
    }

    public function downloadAttachment(BirthCertificate $birth, string $field)
    {
        $allowed = [
            'doc_birth_cert',
            'doc_family_card',
            'doc_marriage_cert',
            'doc_parents_ktp',
            'doc_witness_ktp',
            'applicant_signature',
        ];

        if (!in_array($field, $allowed)) {
            abort(404, 'Jenis berkas lampiran tidak valid.');
        }

        $path = $birth->$field;
        if (!$path) {
            abort(404, 'Berkas belum diunggah.');
        }

        $filePath = $this->resolveFilePath($path);
        if (!$filePath || !file_exists($filePath)) {
            abort(404, 'Berkas fisik lampiran tidak ditemukan pada server.');
        }

        $labels = [
            'doc_birth_cert' => 'Surat_Keterangan_Lahir',
            'doc_family_card' => 'Kartu_Keluarga',
            'doc_marriage_cert' => 'Buku_Nikah',
            'doc_parents_ktp' => 'KTP_Orang_Tua',
            'doc_witness_ktp' => 'Surat_Pengantar_RTRW',
            'applicant_signature' => 'Tanda_Tangan_Pemohon',
        ];

        $label = $labels[$field] ?? 'Berkas_Lampiran';
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $filename = $label . '_' . $birth->registration_no . ($ext ? '.' . $ext : '');

        return response()->download($filePath, $filename);
    }

    protected function resolveFilePath(string $path): ?string
    {
        $cleanPath = ltrim(preg_replace('#^(public/|storage/)#', '', $path), '/\\');

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->path($cleanPath);
        }

        $storageAppPublic = storage_path('app/public/' . $cleanPath);
        if (file_exists($storageAppPublic)) {
            return $storageAppPublic;
        }

        $publicStorage = public_path('storage/' . $cleanPath);
        if (file_exists($publicStorage)) {
            return $publicStorage;
        }

        return null;
    }
}
