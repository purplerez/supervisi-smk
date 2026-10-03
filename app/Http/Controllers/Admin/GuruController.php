<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordGuruRequest;
use App\Http\Requests\Admin\StoreGuruRequest;
use App\Http\Requests\Admin\UpdateGuruRequest;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\GuruService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class GuruController extends Controller
{
    public function __construct(
        private readonly GuruService $guruService,
    ) {}

    /**
     * Tampilkan daftar seluruh pengguna sekolah dengan pencarian dan filter.
     */
    public function index(Request $request, string $kode): View
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $cari = $request->query('cari');
        $status = $request->query('status');
        $role = $request->query('role');

        $pengguna = $this->guruService->daftarPengguna($cari, $status, $role);

        // Ambil daftar supervisor aktif untuk pilihan Kepala Sekolah
        $supervisorList = User::whereHas('roles', fn ($q) => $q->where('role', 'supervisor'))
            ->where('aktif', true)
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin.guru.index', compact(
            'sekolah',
            'pengguna',
            'cari',
            'status',
            'role',
            'supervisorList',
        ));
    }

    /**
     * Simpan pengguna baru dari form admin.
     */
    public function store(StoreGuruRequest $request, string $kode): RedirectResponse
    {
        $user = $this->guruService->tambahPengguna($request->validated());

        return redirect()
            ->route('admin.guru.index', ['kode' => $kode])
            ->with('sukses', "Pengguna \"{$user->nama}\" berhasil ditambahkan dan otomatis memiliki peran Guru.");
    }

    /**
     * Perbarui data profil pengguna.
     */
    public function update(UpdateGuruRequest $request, string $kode, User $guru): RedirectResponse
    {
        $user = $this->guruService->perbaruiPengguna($guru, $request->validated());

        return redirect()
            ->route('admin.guru.index', ['kode' => $kode])
            ->with('sukses', "Data profil \"{$user->nama}\" berhasil diperbarui.");
    }

    /**
     * Nonaktifkan atau aktifkan pengguna sekolah.
     */
    public function toggleStatus(Request $request, string $kode, User $guru): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        try {
            $statusBaru = $this->guruService->toggleStatus($guru, auth()->user(), $sekolah);
        } catch (InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        }

        $label = $statusBaru ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()
            ->route('admin.guru.index', ['kode' => $kode])
            ->with('sukses', "Akun \"{$guru->nama}\" berhasil {$label}.");
    }

    /**
     * Toggle peran Supervisor untuk pengguna.
     * Tidak menghapus peran Guru yang sudah ada.
     */
    public function toggleSupervisor(Request $request, string $kode, User $guru): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        try {
            $isSupervisor = $this->guruService->toggleSupervisor($guru, $sekolah);
        } catch (InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        }

        $pesan = $isSupervisor
            ? "Peran Supervisor berhasil diberikan kepada \"{$guru->nama}\". Peran Guru tetap dipertahankan."
            : "Peran Supervisor berhasil dicabut dari \"{$guru->nama}\".";

        return redirect()
            ->route('admin.guru.index', ['kode' => $kode])
            ->with('sukses', $pesan);
    }

    /**
     * Toggle peran Guru (Disupervisi) untuk pengguna.
     */
    public function toggleGuru(Request $request, string $kode, User $guru): RedirectResponse
    {
        try {
            $isGuru = $this->guruService->toggleGuru($guru);
        } catch (InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        }

        $pesan = $isGuru
            ? "Peran Guru berhasil diberikan kepada \"{$guru->nama}\" (akan disupervisi)."
            : "Peran Guru berhasil dicabut dari \"{$guru->nama}\" (tidak disupervisi).";

        return redirect()
            ->route('admin.guru.index', ['kode' => $kode])
            ->with('sukses', $pesan);
    }

    /**
     * Atur ulang password pengguna.
     */
    public function resetPassword(ResetPasswordGuruRequest $request, string $kode, User $guru): RedirectResponse
    {
        $this->guruService->aturUlangPassword($guru, $request->validated()['password']);

        return redirect()
            ->route('admin.guru.index', ['kode' => $kode])
            ->with('sukses', "Password akun \"{$guru->nama}\" berhasil diatur ulang. Pengguna wajib mengganti password saat login berikutnya.");
    }

    /**
     * Tetapkan Kepala Sekolah pada profil sekolah.
     */
    public function setKepalaSekolah(Request $request, string $kode): RedirectResponse
    {
        $sekolah = Sekolah::where('kode', $kode)->firstOrFail();

        $request->validate([
            'kepala_sekolah_id' => ['nullable', 'integer'],
        ]);

        $id = $request->input('kepala_sekolah_id');

        try {
            $this->guruService->tetapkanKepalaSekolah($sekolah, $id ? (int) $id : null);
        } catch (InvalidArgumentException $e) {
            return back()->with('galat', $e->getMessage());
        }

        return redirect()
            ->route('admin.guru.index', ['kode' => $kode])
            ->with('sukses', 'Penetapan Kepala Sekolah berhasil diperbarui.');
    }
}
