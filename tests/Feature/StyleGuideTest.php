<?php

use Illuminate\Support\Facades\Route;

/**
 * Smoke test untuk halaman /style-guide.
 *
 * Route hanya tersedia di lingkungan local, sehingga di sini
 * kita daftarkan langsung. withoutVite() mencegah error manifest
 * pada environment testing tanpa build frontend.
 */
beforeEach(function () {
    // Daftarkan route style-guide langsung untuk keperluan test
    Route::get('/style-guide', fn () => view('style-guide'))->name('style-guide');
});

it('menampilkan halaman style-guide dengan status 200', function () {
    $this->withoutVite()->get('/style-guide')->assertStatus(200);
});

it('style-guide memuat semua varian badge status', function () {
    $this->withoutVite()
        ->get('/style-guide')
        ->assertStatus(200)
        ->assertSee('Belum dinilai')
        ->assertSee('Sedang dinilai')
        ->assertSee('Selesai')
        ->assertSee('Sedang direvisi');
});

it('style-guide memuat semua varian tombol', function () {
    $this->withoutVite()
        ->get('/style-guide')
        ->assertStatus(200)
        ->assertSee('Tambah Guru')
        ->assertSee('Ubah Data')
        ->assertSee('Hapus')
        ->assertSee('Tombol Utama');
});

it('style-guide memuat komponen alert banner', function () {
    $this->withoutVite()
        ->get('/style-guide')
        ->assertStatus(200)
        ->assertSee('Data guru berhasil disimpan')
        ->assertSee('Terjadi kesalahan');
});

it('style-guide memuat palet warna', function () {
    $this->withoutVite()
        ->get('/style-guide')
        ->assertStatus(200)
        ->assertSee('Palet Warna')
        ->assertSee('#253C6D')  // navy-900
        ->assertSee('#FFFAF3'); // cream
});
