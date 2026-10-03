<?php

namespace App\Policies;

use App\Models\Penilaian;
use App\Models\User;

class PenilaianPolicy extends BasePolicy
{
    /**
     * Hak melihat data penilaian.
     */
    public function view(User $user, Penilaian $penilaian): bool
    {
        $this->checkTenant($user, $penilaian);

        // Admin sekolah dapat melihat seluruh penilaian di sekolahnya
        if ($user->hasRole('admin')) {
            return true;
        }

        // Supervisor hanya dapat melihat penilaian di mana ia penilainya
        if ($user->hasRole('supervisor') && (int) $penilaian->penugasan?->penilai_id === (int) $user->id) {
            return true;
        }

        // Guru hanya dapat melihat penilaian di mana ia yang dinilai
        if ($user->hasRole('guru') && (int) $penilaian->penugasan?->guru_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Hak mengakses formulir dan melakukan penilaian (hanya supervisor yang ditugaskan).
     * Admin TIDAK menilai. Guru tidak bisa mengakses form penilaian.
     */
    public function update(User $user, Penilaian $penilaian): bool
    {
        $this->checkTenant($user, $penilaian);

        // Periode yang ditutup mengunci semua penilaian
        if ($penilaian->penugasan?->periode?->isDitutup()) {
            return false;
        }

        // Penilaian final terkunci dari segala bentuk pengubahan nilai
        if ($penilaian->isFinal()) {
            return false;
        }

        // Hanya supervisor yang ditugaskan yang berwenang menilai
        return $user->hasRole('supervisor')
            && (int) $penilaian->penugasan?->penilai_id === (int) $user->id;
    }

    /**
     * Hak memfinalisasi penilaian (hanya supervisor yang ditugaskan).
     */
    public function finalisasi(User $user, Penilaian $penilaian): bool
    {
        return $this->update($user, $penilaian) && in_array($penilaian->status, ['draft', 'direvisi'], true);
    }

    /**
     * Hak membuka kunci penilaian (hanya Admin sekolah).
     */
    public function bukaKunci(User $user, Penilaian $penilaian): bool
    {
        $this->checkTenant($user, $penilaian);

        // Pada periode ditutup, buka kunci ditolak sampai admin membuka periodenya
        if ($penilaian->penugasan?->periode?->isDitutup()) {
            return false;
        }

        // Hanya penilaian berstatus final yang dapat dibuka kuncinya
        if (! $penilaian->isFinal()) {
            return false;
        }

        // Hanya admin sekolah yang memiliki hak membuka kunci
        return $user->hasRole('admin');
    }
}
