<?php

namespace App\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\SuperAdmin\Exceptions\AdminTerakhirException;
use App\SuperAdmin\Requests\ResetPasswordAdminRequest;
use App\SuperAdmin\Requests\StoreAdminSekolahRequest;
use App\SuperAdmin\Requests\StoreSekolahRequest;
use App\SuperAdmin\Requests\UpdateSekolahRequest;
use App\SuperAdmin\Services\SekolahService;
use App\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SekolahController extends Controller
{
    public function __construct(
        private readonly SekolahService $service,
    ) {}

    // -------------------------------------------------------------------------
    // CRUD SEKOLAH
    // -------------------------------------------------------------------------

    /**
     * Daftar semua sekolah dengan pencarian dan statistik.
     */
    public function index(): View
    {
        TenantContext::clear();

        $cari = request('cari');

        $sekolah = Sekolah::query()
            ->when($cari, fn ($q) => $q->where('nama', 'like', "%{$cari}%")
                ->orWhere('kode', 'like', "%{$cari}%")
                ->orWhere('npsn', 'like', "%{$cari}%"))
            ->withCount([
                'users as jumlah_guru' => fn ($q) => $q->tanpaTenant()
                    ->whereHas('roles', fn ($r) => $r->tanpaTenant()->where('role', 'guru')),
                'users as jumlah_admin' => fn ($q) => $q->tanpaTenant()
                    ->whereHas('roles', fn ($r) => $r->tanpaTenant()->where('role', 'admin')),
            ])
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        return view('super-admin.sekolah.index', compact('sekolah', 'cari'));
    }

    /**
     * Form tambah sekolah baru.
     */
    public function create(): View
    {
        TenantContext::clear();

        return view('super-admin.sekolah.create');
    }

    /**
     * Simpan sekolah baru.
     */
    public function store(StoreSekolahRequest $request): RedirectResponse
    {
        TenantContext::clear();

        $sekolah = $this->service->simpanSekolah(
            $request->validated(),
            $request->file('logo'),
        );

        return redirect()
            ->route('super-admin.sekolah.show', $sekolah)
            ->with('sukses', 'Sekolah "'.$sekolah->nama.'" berhasil ditambahkan.');
    }

    /**
     * Halaman detail sekolah beserta daftar admin-nya.
     */
    public function show(Sekolah $sekolah): View
    {
        TenantContext::clear();

        $admins = $this->service->adminSekolah($sekolah);

        $urlLogin = url("/s/{$sekolah->kode}/login");

        return view('super-admin.sekolah.show', compact('sekolah', 'admins', 'urlLogin'));
    }

    /**
     * Form ubah data sekolah.
     */
    public function edit(Sekolah $sekolah): View
    {
        TenantContext::clear();

        return view('super-admin.sekolah.edit', compact('sekolah'));
    }

    /**
     * Simpan perubahan data sekolah (kode tidak berubah).
     */
    public function update(UpdateSekolahRequest $request, Sekolah $sekolah): RedirectResponse
    {
        TenantContext::clear();

        $this->service->perbaruiSekolah(
            $sekolah,
            $request->validated(),
            $request->file('logo'),
        );

        return redirect()
            ->route('super-admin.sekolah.show', $sekolah)
            ->with('sukses', 'Data sekolah berhasil diperbarui.');
    }

    // -------------------------------------------------------------------------
    // MANAJEMEN ADMIN SEKOLAH
    // -------------------------------------------------------------------------

    /**
     * Tambah akun admin baru untuk sekolah ini.
     */
    public function storeAdmin(StoreAdminSekolahRequest $request, Sekolah $sekolah): RedirectResponse
    {
        TenantContext::clear();

        $this->service->tambahAdmin($sekolah, $request->validated());

        return redirect()
            ->route('super-admin.sekolah.show', $sekolah)
            ->with('sukses', 'Akun admin berhasil ditambahkan. Admin wajib mengganti password saat pertama login.');
    }

    /**
     * Atur ulang password admin (super-admin mengisi password baru).
     */
    public function resetPasswordAdmin(ResetPasswordAdminRequest $request, Sekolah $sekolah, int|string $admin): RedirectResponse
    {
        TenantContext::clear();

        $adminUser = $this->service->cariAdmin($sekolah, $admin);

        $this->service->aturUlangPassword($adminUser, $request->validated()['password']);

        return redirect()
            ->route('super-admin.sekolah.show', $sekolah)
            ->with('sukses', 'Password akun "'.$adminUser->nama.'" berhasil diatur ulang.');
    }

    /**
     * Nonaktifkan akun admin (tolak jika admin aktif terakhir).
     */
    public function nonaktifkanAdmin(Sekolah $sekolah, int|string $admin): RedirectResponse
    {
        TenantContext::clear();

        $adminUser = $this->service->cariAdmin($sekolah, $admin);

        try {
            $this->service->nonaktifkanAdmin($sekolah, $adminUser);
        } catch (AdminTerakhirException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('super-admin.sekolah.show', $sekolah)
            ->with('sukses', 'Akun "'.$adminUser->nama.'" berhasil dinonaktifkan.');
    }

    /**
     * Aktifkan kembali akun admin.
     */
    public function aktifkanAdmin(Sekolah $sekolah, int|string $admin): RedirectResponse
    {
        TenantContext::clear();

        $adminUser = $this->service->cariAdmin($sekolah, $admin);

        $this->service->aktifkanAdmin($adminUser);

        return redirect()
            ->route('super-admin.sekolah.show', $sekolah)
            ->with('sukses', 'Akun "'.$adminUser->nama.'" berhasil diaktifkan kembali.');
    }
}
