<?php

namespace App\Tenant\Scopes;

use App\Tenant\Exceptions\TenantContextMissingException;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SekolahScope implements Scope
{
    /**
     * Terapkan scope ke model Eloquent.
     *
     * Kebijakan: fail-closed.
     * Bila TenantContext::get() mengembalikan null, LEMPAR exception,
     * BUKAN melewatkan filter atau mengembalikan query tanpa kondisi.
     *
     * @throws TenantContextMissingException
     */
    public function apply(Builder $builder, Model $model): void
    {
        $sekolahId = TenantContext::get();

        if ($sekolahId === null) {
            throw new TenantContextMissingException(
                sprintf('TenantContext kosong saat mengakses model [%s]. Operasi ditolak demi keamanan isolasi tenant (fail-closed).', get_class($model))
            );
        }

        $builder->where($model->qualifyColumn('sekolah_id'), '=', $sekolahId);
    }
}
