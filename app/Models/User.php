<?php

namespace App\Models;

use App\Tenant\Traits\BelongsToSekolah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use BelongsToSekolah, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'sekolah_id',
        'nama',
        'username',
        'email',
        'password',
        'nip',
        'nuptk',
        'must_change_password',
        'aktif',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'aktif' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Relasi ke role yang dimiliki user.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    /**
     * Cek apakah user memiliki role tertentu.
     */
    public function hasRole(string $role): bool
    {
        if ($this->is_super_admin && $role === 'super-admin') {
            return true;
        }

        if ($this->relationLoaded('roles')) {
            return $this->roles->contains('role', $role);
        }

        return $this->roles()->where('role', $role)->exists();
    }

    /**
     * Cek apakah user memiliki salah satu dari sekumpulan role.
     *
     * @param  array<string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper untuk menambahkan role ke user.
     */
    public function assignRole(string $role): UserRole
    {
        return $this->roles()->firstOrCreate([
            'sekolah_id' => $this->sekolah_id,
            'role' => $role,
        ]);
    }

    /**
     * Relasi ke penugasan di mana user ini adalah guru yang disupervisi.
     */
    public function penugasanSebagaiGuru(): HasMany
    {
        return $this->hasMany(Penugasan::class, 'guru_id');
    }

    /**
     * Relasi ke penugasan di mana user ini adalah supervisor / penilai.
     */
    public function penugasanSebagaiPenilai(): HasMany
    {
        return $this->hasMany(Penugasan::class, 'penilai_id');
    }

    /**
     * Relasi ke surat tugas yang ditugaskan ke user ini sebagai penilai.
     */
    public function suratTugasSebagaiPenilai(): HasMany
    {
        return $this->hasMany(SuratTugas::class, 'penilai_id');
    }
}
