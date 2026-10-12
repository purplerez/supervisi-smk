<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * Tampilkan halaman utama portal pencarian sekolah.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // 1. Jika pengguna sudah login, arahkan langsung ke dashboard sesuai perannya
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->is_super_admin) {
                return redirect()->route('super-admin.dashboard');
            }

            $kode = session('sekolah_kode');
            $role = session('active_role', 'guru');

            if ($kode) {
                return match ($role) {
                    'admin' => redirect()->route('dashboard.admin', ['kode' => $kode]),
                    'supervisor' => redirect()->route('dashboard.supervisor', ['kode' => $kode]),
                    default => redirect()->route('dashboard.guru', ['kode' => $kode]),
                };
            }
        }

        // 2. Ambil daftar sekolah yang aktif
        $sekolahList = Sekolah::query()
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'npsn', 'kode', 'logo_path', 'alamat']);

        // 3. Deteksi sekolah yang terakhir dibuka dari cookie
        $lastKode = $request->cookie('supervisi_last_sekolah');
        $lastSekolah = null;

        if ($lastKode) {
            $lastSekolah = $sekolahList->firstWhere('kode', $lastKode);
        }

        return view('portal.index', [
            'sekolahList' => $sekolahList,
            'lastSekolah' => $lastSekolah,
        ]);
    }

    /**
     * Simpan pilihan sekolah ke cookie dan redirect ke halaman login sekolah.
     */
    public function pilih(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'exists:sekolah,kode'],
        ]);

        $sekolah = Sekolah::query()
            ->where('kode', $validated['kode'])
            ->where('status', 'aktif')
            ->firstOrFail();

        // Simpan cookie selama 60 hari (86400 menit)
        Cookie::queue('supervisi_last_sekolah', $sekolah->kode, 60 * 24 * 60);

        return redirect()->route('sekolah.login', ['kode' => $sekolah->kode]);
    }
}
