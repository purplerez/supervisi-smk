<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImportParser implements ToArray, WithHeadingRow
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $rows = [];

    /**
     * Tangkap data baris hasil parsing.
     *
     * @param  array<array-key, mixed>  $array
     */
    public function array(array $array): void
    {
        $this->rows = $array;
    }
}
