<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Periode
        Schema::create('periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->string('tahun_ajaran', 20);
            $table->string('semester', 20)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['draft', 'aktif', 'ditutup'])->default('draft');
            $table->timestamps();

            $table->unique(['id', 'sekolah_id']);
            $table->index(['sekolah_id', 'status']);
        });

        // 2. Tabel Penugasan (guru_id disupervisi oleh penilai_id)
        Schema::create('penugasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periode')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('penilai_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Constraint sesuai DESAIN 3.2:
            $table->unique(['periode_id', 'guru_id']);
            $table->unique(['id', 'sekolah_id']);

            // FK komposit dengan sekolah_id
            $table->foreign(['guru_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('users')->cascadeOnDelete();
            $table->foreign(['penilai_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('users')->cascadeOnDelete();
            $table->foreign(['periode_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('periode')->cascadeOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE penugasan ADD CONSTRAINT chk_penugasan_guru_penilai CHECK (guru_id <> penilai_id)');
        }

        // 3. Tabel Penilaian (4 baris per penugasan, dibuat otomatis saat penugasan dibuat)
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('penugasan_id')->constrained('penugasan')->cascadeOnDelete();
            $table->foreignId('jenis_instrumen_id')->constrained('jenis_instrumen')->cascadeOnDelete();
            $table->foreignId('versi_instrumen_id')->nullable()->constrained('versi_instrumen')->nullOnDelete();
            $table->enum('status', ['belum', 'draft', 'final', 'direvisi'])->default('belum');
            $table->unsignedInteger('total_skor')->nullable();
            $table->unsignedInteger('skor_maks')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('predikat', 10)->nullable();
            $table->text('catatan')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->unsignedInteger('jumlah_buka_kunci')->default(0);
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diperbarui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['penugasan_id', 'jenis_instrumen_id']);
            $table->unique(['id', 'sekolah_id']);

            // FK komposit dengan sekolah_id
            $table->foreign(['penugasan_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('penugasan')->cascadeOnDelete();
        });

        // 4. Tabel Penilaian Butir
        Schema::create('penilaian_butir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaian')->cascadeOnDelete();
            $table->foreignId('butir_instrumen_id')->constrained('butir_instrumen')->cascadeOnDelete();
            $table->tinyInteger('skor')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['penilaian_id', 'butir_instrumen_id']);
        });

        // 5. Tabel Info Supervisi (melekat ke penugasan)
        Schema::create('info_supervisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('penugasan_id')->unique()->constrained('penugasan')->cascadeOnDelete();
            $table->string('kelas', 50);
            $table->string('semester', 20)->nullable();
            $table->string('fase', 20)->nullable();
            $table->string('mata_pelajaran', 100);
            $table->string('elemen', 100)->nullable();
            $table->text('cp')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['id', 'sekolah_id']);
            $table->foreign(['penugasan_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('penugasan')->cascadeOnDelete();
        });

        // 6. Tabel Jadwal Supervisi
        Schema::create('jadwal_supervisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('penugasan_id')->constrained('penugasan')->cascadeOnDelete();
            $table->foreignId('jenis_instrumen_id')->nullable()->constrained('jenis_instrumen')->nullOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();

            $table->unique(['id', 'sekolah_id']);
            $table->foreign(['penugasan_id', 'sekolah_id'])->references(['id', 'sekolah_id'])->on('penugasan')->cascadeOnDelete();
        });

        // 7. Tabel Audit Log (DESAIN 3.4)
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('penilaian_id')->nullable()->constrained('penilaian')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('aksi', ['mulai', 'simpan_butir', 'finalisasi', 'buka_kunci', 'ubah_catatan']);
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->text('alasan')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('jadwal_supervisi');
        Schema::dropIfExists('info_supervisi');
        Schema::dropIfExists('penilaian_butir');
        Schema::dropIfExists('penilaian');
        Schema::dropIfExists('penugasan');
        Schema::dropIfExists('periode');
    }
};
