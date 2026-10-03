<?php

namespace App\SuperAdmin\Auth;

use App\Models\User;
use App\Tenant\TenantContext;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class SuperAdminUserProvider extends EloquentUserProvider
{
    /**
     * Dapatkan user berdasarkan ID autentikasi.
     *
     * @param  mixed  $identifier
     * @return Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $sekolahId = TenantContext::get();

        if ($sekolahId !== null) {
            // Dalam lingkup sekolah, delegasikan ke provider bawaan (scoping sekolah aktif)
            return parent::retrieveById($identifier);
        }

        // Ketika TenantContext kosong (misal saat navigasi di /super-admin/*):
        // Cari user super-admin menggunakan bypass resmi tanpaTenant()
        return User::tanpaTenant()
            ->where($this->createModel()->getAuthIdentifierName(), $identifier)
            ->where('is_super_admin', true)
            ->first();
    }

    /**
     * Dapatkan user berdasarkan "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        $sekolahId = TenantContext::get();

        if ($sekolahId !== null) {
            return parent::retrieveByToken($identifier, $token);
        }

        $model = $this->createModel();

        return User::tanpaTenant()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where($model->getRememberTokenName(), $token)
            ->where('is_super_admin', true)
            ->first();
    }
}
