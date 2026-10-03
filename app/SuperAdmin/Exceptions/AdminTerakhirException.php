<?php

namespace App\SuperAdmin\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika super-admin mencoba menonaktifkan admin aktif terakhir di sekolah.
 */
class AdminTerakhirException extends RuntimeException {}
