<?php

namespace App\Tenant\Exceptions;

use RuntimeException;

/**
 * Exception yang dilempar saat tanpaTenant() dipanggil dari luar namespace
 * yang diizinkan (hanya namespace super-admin yang diperbolehkan).
 */
class TenantBypassDisallowedException extends RuntimeException
{
    public function __construct(string $message = 'Bypass tanpaTenant() hanya diizinkan dari namespace super-admin.')
    {
        parent::__construct($message);
    }
}
