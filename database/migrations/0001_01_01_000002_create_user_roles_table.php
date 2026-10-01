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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sekolah_id')->constrained('sekolah')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['admin', 'supervisor', 'guru']);
            $table->timestamps();

            // Constraint unik: satu user tidak boleh punya role ganda untuk jenis yang sama
            $table->unique(['user_id', 'role']);

            // FK komposit untuk mencegah relasi lintas sekolah (Lapis 3 database)
            $table->foreign(['user_id', 'sekolah_id'])
                ->references(['id', 'sekolah_id'])
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
