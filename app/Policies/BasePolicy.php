<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Memeriksa apakah model bertenant berada pada sekolah yang sama dengan user.
     * Bila berbeda sekolah, lempar HTTP 404 (bukan 403) sesuai aturan tenant Lapis 2
     * agar penyerang dari sekolah lain tidak dapat menebak keberadaan data sekolah lain.
     *
     * @throws NotFoundHttpException
     */
    protected function checkTenant(User $user, mixed $model): bool
    {
        // Super admin memiliki hak lintas sekolah bila diperlukan
        if ($user->is_super_admin) {
            return true;
        }

        if (is_object($model) && isset($model->sekolah_id)) {
            if ((int) $model->sekolah_id !== (int) $user->sekolah_id) {
                abort(404, 'Data tidak ditemukan.');
            }
        }

        return true;
    }

    /**
     * Alias ekspresif untuk checkTenant.
     */
    protected function belongsToSameSekolah(User $user, mixed $model): bool
    {
        return $this->checkTenant($user, $model);
    }
}
