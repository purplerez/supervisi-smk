<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    use BelongsToSekolah, HasFactory;

    protected $table = 'user_roles';

    protected $fillable = [
        'sekolah_id',
        'user_id',
        'role',
    ];

    /**
     * Relasi ke User pemilik role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
