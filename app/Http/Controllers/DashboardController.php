<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Halaman Dasbor Admin Sekolah.
     */
    public function admin(string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        return view('dashboard.admin', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Halaman Dasbor Supervisor / Penilai.
     */
    public function supervisor(string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        return view('dashboard.supervisor', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Halaman Dasbor Guru.
     */
    public function guru(string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        return view('dashboard.guru', [
            'sekolah' => $sekolah,
            'user' => Auth::user(),
        ]);
    }
}
