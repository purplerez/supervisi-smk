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
        Schema::create('surat_tugas', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('penilai_id')->constrained('users')->cascadeOnDelete();

            $table->string('nomor_surat', 100);
            $table->date('tanggal_surat');
            $table->string('penandatangan_nama', 255);
            $table->string('penandatangan_nip', 50)->nullable();
            $table->string('penandatangan_jabatan', 100)->default('Kepala Sekolah');

            // Snapshot guru saat surat diterbitkan (array of {id, nama, nip, nuptk})
            $table->json('daftar_guru');
            $table->timestamp('diterbitkan_at');
            $table->timestamps();

            // Constraint unik sesuai DESAIN 3.5 & aturan multitenant
            $table->unique(['periode_id', 'penilai_id']);
            $table->unique(['sekolah_id', 'periode_id', 'penilai_id']);
            $table->unique(['id', 'sekolah_id']);

            // FK komposit dengan sekolah_id
            $table->foreign(['periode_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('periode')->cascadeOnDelete();
            $table->foreign(['penilai_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_tugas');
    }
};
