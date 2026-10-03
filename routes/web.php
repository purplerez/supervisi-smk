<?php

use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\GuruImportController;
use App\Http\Controllers\Admin\InstrumenSekolahController;
use App\Http\Controllers\Admin\PenugasanController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\SuratTugasController;
use App\Http\Controllers\Auth\SekolahAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\GuruDashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\Supervisor\SupervisorDashboardController;
use App\SuperAdmin\Controllers\InstrumenController;
use App\SuperAdmin\Controllers\SekolahController;
use App\SuperAdmin\Controllers\SuperAdminAuthController;
use App\SuperAdmin\Controllers\SuperAdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Aplikasi Supervisi Guru
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Logout global
Route::post('/logout', [SekolahAuthController::class, 'logout'])->middleware(['tenant'])->name('logout');

// Fallback route 'login' yang diperlukan middleware auth Laravel bawaan.
// Redirect ke super-admin.login sebagai default.
Route::get('/login', function () {
    return redirect()->route('super-admin.login');
})->name('login');

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.post');
    });

    Route::middleware(['auth', 'super.admin'])->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');

        // CRUD Sekolah (tidak ada rute delete — hanya nonaktifkan lewat update status)
        Route::resource('sekolah', SekolahController::class)->except(['destroy']);

        // Manajemen Admin Sekolah (sub-resource dari sekolah)
        Route::prefix('sekolah/{sekolah}/admin')->name('sekolah.admin.')->group(function () {
            Route::post('/', [SekolahController::class, 'storeAdmin'])->name('store');
            Route::patch('/{admin}/reset-password', [SekolahController::class, 'resetPasswordAdmin'])->name('reset-password');
            Route::patch('/{admin}/nonaktifkan', [SekolahController::class, 'nonaktifkanAdmin'])->name('nonaktifkan');
            Route::patch('/{admin}/aktifkan', [SekolahController::class, 'aktifkanAdmin'])->name('aktifkan');
        });

        // Template Instrumen Global (Super Admin)
        Route::get('/instrumen', [InstrumenController::class, 'index'])->name('instrumen.index');
        Route::get('/instrumen/{jenis}', [InstrumenController::class, 'show'])->name('instrumen.show');
        Route::get('/instrumen/{jenis}/versi/{versi}', [InstrumenController::class, 'showVersi'])->name('instrumen.versi.show');
        Route::post('/instrumen/{jenis}/versi/{versi}/buat-versi', [InstrumenController::class, 'buatVersiBaru'])->name('instrumen.versi.buat-versi');
        Route::patch('/instrumen/{jenis}/versi/{versi}/terbitkan', [InstrumenController::class, 'terbitkanVersi'])->name('instrumen.versi.terbitkan');
        Route::patch('/instrumen/{jenis}/versi/{versi}/ambang', [InstrumenController::class, 'updateAmbang'])->name('instrumen.versi.ambang');

        Route::post('/instrumen/{jenis}/versi/{versi}/bagian', [InstrumenController::class, 'storeBagian'])->name('instrumen.bagian.store');
        Route::put('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}', [InstrumenController::class, 'updateBagian'])->name('instrumen.bagian.update');
        Route::delete('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}', [InstrumenController::class, 'destroyBagian'])->name('instrumen.bagian.destroy');
        Route::patch('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/geser', [InstrumenController::class, 'geserBagian'])->name('instrumen.bagian.geser');

        Route::post('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir', [InstrumenController::class, 'storeButir'])->name('instrumen.butir.store');
        Route::put('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir/{butir}', [InstrumenController::class, 'updateButir'])->name('instrumen.butir.update');
        Route::delete('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir/{butir}', [InstrumenController::class, 'destroyButir'])->name('instrumen.butir.destroy');
        Route::patch('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir/{butir}/geser', [InstrumenController::class, 'geserButir'])->name('instrumen.butir.geser');
    });
});

/*
|--------------------------------------------------------------------------
| Sekolah Tenant Routes (/s/{kode})
|--------------------------------------------------------------------------
*/
Route::prefix('s/{kode}')->middleware(['tenant'])->group(function () {
    // 1. Halaman Login Sekolah (Guest)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SekolahAuthController::class, 'showLoginForm'])->name('sekolah.login');
        Route::post('/login', [SekolahAuthController::class, 'login'])->name('sekolah.login.post');
    });

    // 2. Rute Wajib Ganti Password (khusus saat must_change_password = true)
    Route::middleware(['auth'])->group(function () {
        Route::get('/ganti-password', [SekolahAuthController::class, 'showChangePasswordForm'])->name('password.change');
        Route::post('/ganti-password', [SekolahAuthController::class, 'updatePassword'])->name('password.update');
    });

    // 3. Rute Sekolah Terautentikasi (Dibatasi middleware must_change_password)
    Route::middleware(['auth', 'must.change.password'])->group(function () {
        // Ganti Role Aktif
        Route::post('/switch-role', [SekolahAuthController::class, 'switchRole'])->name('role.switch');
        Route::post('/logout', [SekolahAuthController::class, 'logout'])->name('sekolah.logout');

        // Dasbor Admin Sekolah
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard.admin');
        });

        // Modul Admin Sekolah
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {

            // CRUD Pengguna / Guru Sekolah
            Route::get('/guru', [GuruController::class, 'index'])->name('guru.index');
            Route::post('/guru', [GuruController::class, 'store'])->name('guru.store');
            Route::put('/guru/{guru}', [GuruController::class, 'update'])->name('guru.update');
            Route::patch('/guru/{guru}/toggle-status', [GuruController::class, 'toggleStatus'])->name('guru.toggle-status');
            Route::patch('/guru/{guru}/toggle-supervisor', [GuruController::class, 'toggleSupervisor'])->name('guru.toggle-supervisor');
            Route::patch('/guru/{guru}/toggle-guru', [GuruController::class, 'toggleGuru'])->name('guru.toggle-guru');
            Route::patch('/guru/{guru}/reset-password', [GuruController::class, 'resetPassword'])->name('guru.reset-password');

            // Penetapan Kepala Sekolah (Profil Sekolah)
            Route::patch('/sekolah/kepala-sekolah', [GuruController::class, 'setKepalaSekolah'])->name('sekolah.kepala-sekolah');

            // Impor Excel Guru (Alur 3 langkah: unggah -> dry run pratinjau -> konfirmasi)
            Route::get('/guru-import', [GuruImportController::class, 'index'])->name('guru.import');
            Route::get('/guru-import/template', [GuruImportController::class, 'downloadTemplate'])->name('guru.import.template');
            Route::post('/guru-import/upload', [GuruImportController::class, 'upload'])->name('guru.import.upload');
            Route::post('/guru-import/confirm', [GuruImportController::class, 'confirm'])->name('guru.import.confirm');
            Route::delete('/guru-import/cancel', [GuruImportController::class, 'cancel'])->name('guru.import.cancel');

            // Instrumen Supervisi Sekolah
            Route::get('/instrumen', [InstrumenSekolahController::class, 'index'])->name('instrumen.index');
            Route::post('/instrumen/{templateGlobal}/salin', [InstrumenSekolahController::class, 'salinKeSekolah'])->name('instrumen.salin');
            Route::get('/instrumen/{jenis}', [InstrumenSekolahController::class, 'show'])->name('instrumen.show');
            Route::get('/instrumen/{jenis}/versi/{versi}', [InstrumenSekolahController::class, 'showVersi'])->name('instrumen.versi.show');
            Route::post('/instrumen/{jenis}/versi/{versi}/buat-versi', [InstrumenSekolahController::class, 'buatVersiBaru'])->name('instrumen.versi.buat-versi');
            Route::patch('/instrumen/{jenis}/versi/{versi}/terbitkan', [InstrumenSekolahController::class, 'terbitkanVersi'])->name('instrumen.versi.terbitkan');
            Route::patch('/instrumen/{jenis}/versi/{versi}/ambang', [InstrumenSekolahController::class, 'updateAmbang'])->name('instrumen.versi.ambang');

            Route::post('/instrumen/{jenis}/versi/{versi}/bagian', [InstrumenSekolahController::class, 'storeBagian'])->name('instrumen.bagian.store');
            Route::put('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}', [InstrumenSekolahController::class, 'updateBagian'])->name('instrumen.bagian.update');
            Route::delete('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}', [InstrumenSekolahController::class, 'destroyBagian'])->name('instrumen.bagian.destroy');
            Route::patch('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/geser', [InstrumenSekolahController::class, 'geserBagian'])->name('instrumen.bagian.geser');

            Route::post('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir', [InstrumenSekolahController::class, 'storeButir'])->name('instrumen.butir.store');
            Route::put('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir/{butir}', [InstrumenSekolahController::class, 'updateButir'])->name('instrumen.butir.update');
            Route::delete('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir/{butir}', [InstrumenSekolahController::class, 'destroyButir'])->name('instrumen.butir.destroy');
            Route::patch('/instrumen/{jenis}/versi/{versi}/bagian/{bagian}/butir/{butir}/geser', [InstrumenSekolahController::class, 'geserButir'])->name('instrumen.butir.geser');

            // Manajemen Periode Supervisi
            Route::get('/periode', [PeriodeController::class, 'index'])->name('periode.index');
            Route::post('/periode', [PeriodeController::class, 'store'])->name('periode.store');
            Route::put('/periode/{periode}', [PeriodeController::class, 'update'])->name('periode.update');
            Route::delete('/periode/{periode}', [PeriodeController::class, 'destroy'])->name('periode.destroy');
            Route::patch('/periode/{periode}/aktifkan', [PeriodeController::class, 'aktifkan'])->name('periode.aktifkan');
            Route::patch('/periode/{periode}/tutup', [PeriodeController::class, 'tutup'])->name('periode.tutup');

            // Manajemen Penugasan Supervisi
            Route::get('/penugasan', [PenugasanController::class, 'index'])->name('penugasan.index');
            Route::post('/penugasan', [PenugasanController::class, 'store'])->name('penugasan.store');
            Route::patch('/penugasan/{penugasan}/penilai', [PenugasanController::class, 'updatePenilai'])->name('penugasan.update-penilai');
            Route::delete('/penugasan/{penugasan}', [PenugasanController::class, 'destroy'])->name('penugasan.destroy');
            Route::get('/penugasan/belum-dinilai', [PenugasanController::class, 'belumPunyaPenilai'])->name('penugasan.belum-dinilai');
            Route::patch('/penilaian/{penilaian}/buka-kunci', [PenugasanController::class, 'bukaKunciPenilaian'])->name('penilaian.buka-kunci');

            // Penerbitan & Unduh Surat Tugas Supervisor (DOCX)
            Route::get('/surat-tugas', [SuratTugasController::class, 'index'])->name('surat-tugas.index');
            Route::post('/surat-tugas/{periode}/{penilai}', [SuratTugasController::class, 'store'])->name('surat-tugas.store');
            Route::get('/surat-tugas/{suratTugas}/unduh', [SuratTugasController::class, 'download'])->name('surat-tugas.download');

            // Rekap & Laporan Supervisi Admin
            Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
            Route::get('/laporan/export/rekap', [LaporanController::class, 'exportRekap'])->name('laporan.export.rekap');
            Route::get('/laporan/export/ketuntasan', [LaporanController::class, 'exportKetuntasan'])->name('laporan.export.ketuntasan');
        });

        // Dasbor & Modul Supervisor / Penilai
        Route::middleware('role:supervisor')->prefix('supervisor')->group(function () {
            Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard.supervisor');
            Route::get('/penugasan/{penugasan}', [SupervisorDashboardController::class, 'showPenugasan'])->name('supervisor.penugasan.show');
            Route::get('/penilaian/{penilaian}', [SupervisorDashboardController::class, 'showPenilaian'])->name('supervisor.penilaian.show');
            Route::get('/laporan/export/rekap', [LaporanController::class, 'exportRekapSupervisor'])->name('supervisor.laporan.export.rekap');
            Route::get('/laporan/export/ketuntasan', [LaporanController::class, 'exportKetuntasanSupervisor'])->name('supervisor.laporan.export.ketuntasan');
        });

        // Dasbor & Modul Supervisi Guru
        Route::middleware('role:guru')->prefix('guru')->name('guru.')->group(function () {
            Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
            Route::get('/informasi', [GuruDashboardController::class, 'infoJadwal'])->name('info-jadwal');
            Route::post('/informasi', [GuruDashboardController::class, 'simpanInfoJadwal'])->name('info-jadwal.simpan');
            Route::get('/rapor/{periode?}', [GuruDashboardController::class, 'rapor'])->name('rapor');
            Route::get('/riwayat', [GuruDashboardController::class, 'riwayat'])->name('riwayat');
        });

        // Cetak Lembar Penilaian Final (PDF, route key ULID: Admin, Supervisor Penilai, Guru yang Dinilai)
        Route::get('/penilaian/{penilaian}/cetak-pdf', [LaporanController::class, 'cetakPenilaianPdf'])->name('penilaian.cetak-pdf');

        // Alias untuk kompatibilitas route('dashboard.guru')
        Route::get('/guru', [GuruDashboardController::class, 'index'])->name('dashboard.guru');
    });
});

/*
|--------------------------------------------------------------------------
| Style Guide — hanya tersedia di lingkungan local
|--------------------------------------------------------------------------
*/
if (app()->isLocal()) {
    Route::get('/style-guide', function () {
        return view('style-guide');
    })->name('style-guide');
}
