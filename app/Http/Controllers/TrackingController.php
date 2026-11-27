<?php

namespace App\Http\Controllers;

use App\Models\BirthCertificate;
use App\Models\DeathCertificate;
use App\Services\CertificateExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));
        $result = null;
        $type = null;
        $user = Auth::user();

        if (!empty($keyword)) {
            $isWargaOrAdmin = $user && ($user->isWarga() || $user->isActiveAdmin());

            // Cek di Akte Kelahiran (Guest hanya boleh melacak dengan Nomor Registrasi)
            $birthQuery = BirthCertificate::where(function ($q) use ($keyword, $isWargaOrAdmin) {
                $q->where('registration_no', $keyword);
                if ($isWargaOrAdmin) {
                    $q->orWhere('applicant_nik', $keyword)
                      ->orWhere('father_nik', $keyword)
                      ->orWhere('mother_nik', $keyword);
                }
            });

            // Jika Warga sedang login, batasi hanya KK miliknya
            if ($user && $user->isWarga()) {
                $birthQuery->where(function ($q) use ($user) {
                    $q->where('family_card_no', $user->family_card_no)
                      ->orWhere('user_id', $user->id);
                });
            }

            $birth = $birthQuery->latest()->first();

            if ($birth) {
                $result = $birth;
                $type = 'birth';
            } else {
                // Cek di Akte Kematian (Guest hanya boleh melacak dengan Nomor Registrasi)
                $deathQuery = DeathCertificate::where(function ($q) use ($keyword, $isWargaOrAdmin) {
                    $q->where('registration_no', $keyword);
                    if ($isWargaOrAdmin) {
                        $q->orWhere('deceased_nik', $keyword)
                          ->orWhere('applicant_nik', $keyword);
                    }
                });

                if ($user && $user->isWarga()) {
                    $deathQuery->where(function ($q) use ($user) {
                        $q->where('family_card_no', $user->family_card_no)
                          ->orWhere('user_id', $user->id);
                    });
                }

                $death = $deathQuery->latest()->first();

                if ($death) {
                    $result = $death;
                    $type = 'death';
                }
            }
        }

        return view('tracking.index', compact('keyword', 'result', 'type'));
    }

    public function show(string $type, string $registrationNo)
    {
        if ($type === 'birth') {
            $data = BirthCertificate::where('registration_no', $registrationNo)->firstOrFail();
        } elseif ($type === 'death') {
            $data = DeathCertificate::where('registration_no', $registrationNo)->firstOrFail();
        } else {
            abort(404, 'Jenis permohonan tidak valid.');
        }

        // Keamanan Akses Data Sensitif Berbasis KK
        if (!Auth::check()) {
            return redirect()->guest(route('warga.login'))
                ->with('info', 'Silakan masuk ke akun warga Anda untuk mengakses detail dan status pengajuan.');
        }

        $user = Auth::user();
        if ($user->isWarga()) {
            $isSameKK = $data->family_card_no && $data->family_card_no === $user->family_card_no;
            $isSameUser = $data->user_id && $data->user_id === $user->id;
            if (!$isSameKK && !$isSameUser) {
                abort(403, 'Akses Ditolak: Anda hanya berhak melihat dokumen dan status pengajuan dari Kartu Keluarga (KK) Anda sendiri.');
            }
        }

        return view('tracking.show', compact('data', 'type'));
    }

    public function printReceipt(string $type, string $registrationNo)
    {
        if ($type === 'birth') {
            $data = BirthCertificate::where('registration_no', $registrationNo)->firstOrFail();
        } elseif ($type === 'death') {
            $data = DeathCertificate::where('registration_no', $registrationNo)->firstOrFail();
        } else {
            abort(404, 'Jenis permohonan tidak valid.');
        }

        // Tidak dapat mencetak tanda terima saat status permohonan dalam revisi atau dibatalkan
        if (in_array($data->status, ['revision', 'rejected'], true)) {
            $reasonMsg = $data->status === 'rejected'
                ? 'Tanda terima permohonan tidak dapat dicetak karena permohonan telah dibatalkan/ditolak.'
                : 'Tanda terima permohonan tidak dapat dicetak selama permohonan dalam status revisi berkas.';

            return redirect()->route('tracking.show', ['type' => $type, 'registrationNo' => $registrationNo])
                ->with('error', $reasonMsg);
        }

        // Keamanan Akses Data Sensitif Berbasis KK
        if (!Auth::check()) {
            return redirect()->guest(route('warga.login'))
                ->with('info', 'Silakan masuk ke akun warga Anda untuk mencetak bukti pendaftaran.');
        }

        $user = Auth::user();
        if ($user->isWarga()) {
            $isSameKK = $data->family_card_no && $data->family_card_no === $user->family_card_no;
            $isSameUser = $data->user_id && $data->user_id === $user->id;
            if (!$isSameKK && !$isSameUser) {
                abort(403, 'Akses Ditolak: Anda hanya berhak mencetak bukti pengajuan dari Kartu Keluarga (KK) Anda sendiri.');
            }
        }

        return view('tracking.print-receipt', compact('data', 'type'));
    }

    public function printLetter($type, $registrationNo)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('admin.login'))
                ->with('error', 'Silakan masuk ke panel admin petugas terlebih dahulu.');
        }

        $user = Auth::user();
        if (!$user->isActiveAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Admin / Petugas Kalurahan yang berhak mencetak Surat Keterangan.');
        }

        if ($type === 'birth') {
            $birth = BirthCertificate::where('registration_no', $registrationNo)->firstOrFail();
            return view('admin.birth.print-letter', compact('birth'));
        } elseif ($type === 'death') {
            $death = DeathCertificate::where('registration_no', $registrationNo)->firstOrFail();
            return view('admin.death.print-letter', compact('death'));
        } else {
            abort(404, 'Jenis permohonan tidak mendukung cetak surat ini.');
        }
    }

    public function export(string $type, string $registrationNo, string $format, CertificateExportService $exportService)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('admin.login'))
                ->with('error', 'Silakan masuk ke panel admin petugas terlebih dahulu.');
        }

        $user = Auth::user();
        if ($user->isWarga()) {
            if (!in_array($type, ['birth', 'death'], true)) {
                abort(404, 'Jenis permohonan tidak valid.');
            }
            $model = $type === 'birth'
                ? BirthCertificate::where('registration_no', $registrationNo)->firstOrFail()
                : DeathCertificate::where('registration_no', $registrationNo)->firstOrFail();

            $isSameKK = $model->family_card_no && $model->family_card_no === $user->family_card_no;
            $isSameUser = $model->user_id && $model->user_id === $user->id;
            if (!$isSameKK && !$isSameUser) {
                abort(403, 'Akses Ditolak: Anda hanya berhak mengunduh berkas dari Kartu Keluarga (KK) Anda sendiri.');
            }
            return $exportService->export($type, $model, $format);
        }

        if (!$user->isActiveAdmin()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk mengunduh berkas ini.');
        }

        if ($type === 'birth') {
            $certificate = BirthCertificate::where('registration_no', $registrationNo)->firstOrFail();
        } elseif ($type === 'death') {
            $certificate = DeathCertificate::where('registration_no', $registrationNo)->firstOrFail();
        } else {
            abort(404, 'Jenis permohonan tidak valid.');
        }

        return $exportService->export($type, $certificate, $format);
    }
}
