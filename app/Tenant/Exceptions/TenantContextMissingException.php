<?php

namespace App\Tenant\Exceptions;

use RuntimeException;

/**
 * Exception yang dilempar saat operasi Eloquent pada model bertenant
 * dijalankan tanpa TenantContext aktif (kebijakan fail-closed).
 */
class TenantContextMissingException extends RuntimeException
{
    public function __construct(string $message = 'Tenant context tidak ditemukan (fail-closed). Operasi dibatalkan demi keamanan isolasi data.')
    {
        parent::__construct($message);
    }
}
