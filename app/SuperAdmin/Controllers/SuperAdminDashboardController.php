<?php

namespace App\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Tenant\TenantContext;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    /**
     * Tampilkan halaman Dasbor Super Admin.
     */
    public function index(): View
    {
        TenantContext::clear();

        $jumlahSekolah = Sekolah::count();
        $sekolahList = Sekolah::latest()->take(10)->get();

        return view('super-admin.dashboard', [
            'jumlahSekolah' => $jumlahSekolah,
            'sekolahList' => $sekolahList,
        ]);
    }
}
