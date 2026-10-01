<?php

namespace App\Tenant;

use App\Models\Sekolah;

class TenantContext
{
    /**
     * ID sekolah aktif saat ini pada singleton container.
     */
    public ?int $sekolahId = null;

    /**
     * Tetapkan ID sekolah yang aktif saat ini.
     */
    public static function set(int|Sekolah|null $sekolah): void
    {
        $id = $sekolah instanceof Sekolah ? $sekolah->id : $sekolah;
        app(self::class)->sekolahId = $id;
    }

    /**
     * Dapatkan ID sekolah yang aktif saat ini.
     */
    public static function get(): ?int
    {
        return app(self::class)->sekolahId;
    }

    /**
     * Kosongkan konteks sekolah aktif.
     */
    public static function clear(): void
    {
        app(self::class)->sekolahId = null;
    }

    /**
     * Jalankan callback dalam lingkup sekolah tertentu dan pulihkan konteks sebelumnya.
     */
    public static function runAs(int|Sekolah $sekolahId, callable $callback): mixed
    {
        $instance = app(self::class);
        $previous = $instance->sekolahId;

        self::set($sekolahId);

        try {
            return $callback();
        } finally {
            $instance->sekolahId = $previous;
        }
    }
}
