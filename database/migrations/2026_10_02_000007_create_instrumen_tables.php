<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel jenis_instrumen (sekolah_id nullable: null = template global milik super-admin)
        Schema::create('jenis_instrumen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('sekolah_id')->nullable()->constrained('sekolah')->cascadeOnDelete();
            $table->string('kode', 50); // kbm, administrasi, pengelolaan_kelas, perencanaan
            $table->string('nama', 255);
            $table->unsignedInteger('urutan')->default(1);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['sekolah_id', 'kode']);
            $table->index(['sekolah_id', 'aktif']);
        });

        // 2. Tabel versi_instrumen (berversi: nomor_versi, status draft/terbit)
        Schema::create('versi_instrumen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('jenis_instrumen_id')->constrained('jenis_instrumen')->cascadeOnDelete();
            $table->unsignedInteger('nomor_versi')->default(1);
            $table->enum('status', ['draft', 'terbit'])->default('draft');
            $table->unsignedTinyInteger('skor_maks_butir')->default(4);
            $table->json('ambang_predikat')->nullable(); // Default mis. {"A":86,"B":76,"C":56}
            $table->timestamps();

            $table->unique(['jenis_instrumen_id', 'nomor_versi']);
            $table->index(['jenis_instrumen_id', 'status']);
        });

        // 3. Tabel bagian_instrumen (bagian dalam satu versi instrumen)
        Schema::create('bagian_instrumen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('versi_instrumen_id')->constrained('versi_instrumen')->cascadeOnDelete();
            $table->string('kode', 50);
            $table->string('judul', 255);
            $table->unsignedInteger('urutan')->default(1);
            $table->string('kategori', 100)->nullable(); // Mis. wajib/penunjang
            $table->timestamps();

            $table->index(['versi_instrumen_id', 'urutan']);
        });

        // 4. Tabel butir_instrumen (butir penilaian di bawah bagian)
        Schema::create('butir_instrumen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('bagian_id')->constrained('bagian_instrumen')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->text('uraian');
            $table->unsignedTinyInteger('skor_maks')->default(4);
            $table->timestamps();

            $table->index(['bagian_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('butir_instrumen');
        Schema::dropIfExists('bagian_instrumen');
        Schema::dropIfExists('versi_instrumen');
        Schema::dropIfExists('jenis_instrumen');
    }
};
