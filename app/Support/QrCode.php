<?php

namespace App\Support;

/**
 * QR Code Generator (Pure PHP, zero external dependencies).
 *
 * Menghasilkan QR Code dalam format SVG vector tajam tanpa dependensi
 * paket composer tambahan maupun ekstensi GD/Imagick.
 *
 * Berdasarkan algoritma QR Code standar (ISO/IEC 18004) dengan lisensi MIT.
 */
class QrCode
{
    /**
     * Hasilkan kode QR dalam format string SVG.
     *
     * @param  string  $data  Teks atau URL yang akan di-encode
     * @param  int  $size  Lebar/tinggi SVG dalam pixel
     * @param  string  $fg  Warna modul/foreground (hex, misal: '#253C6D')
     * @param  string  $bg  Warna latar belakang (hex, misal: '#FFFFFF')
     * @param  int  $ecl  Tingkat koreksi galat: 0 = L (7%), 1 = M (15%), 2 = Q (25%), 3 = H (30%)
     */
    public static function svg(
        string $data,
        int $size = 280,
        string $fg = '#253C6D',
        string $bg = '#FFFFFF',
        int $ecl = 1
    ): string {
        $encoder = new self($data);
        $res = $encoder->encode($data, $ecl);

        $matrix = $res['b'];
        $count = count($matrix);
        $quiet = 4;
        $total = $count + ($quiet * 2);

        $path = '';
        for ($r = 0; $r < $count; $r++) {
            for ($c = 0; $c < $count; $c++) {
                if ($matrix[$r][$c]) {
                    $len = 1;
                    while ($c + $len < $count && $matrix[$r][$c + $len]) {
                        $len++;
                    }
                    $x = $c + $quiet;
                    $y = $r + $quiet;
                    $path .= "M{$x},{$y}h{$len}v1h-{$len}z ";
                    $c += $len - 1;
                }
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" width="%2$d" height="%2$d" shape-rendering="crispEdges" role="img" aria-label="QR Code"><rect width="100%%" height="100%%" fill="%3$s"/><path d="%4$s" fill="%5$s"/></svg>',
            $total,
            $size,
            htmlspecialchars($bg, ENT_QUOTES, 'UTF-8'),
            trim($path),
            htmlspecialchars($fg, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Hasilkan data URI SVG (dapat langsung digunakan pada atribut src di tag img).
     */
    public static function dataUri(
        string $data,
        int $size = 280,
        string $fg = '#253C6D',
        string $bg = '#FFFFFF'
    ): string {
        $svg = self::svg($data, $size, $fg, $bg);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    // =========================================================================
    // IMPLEMENTASI INTERNAL QR CODE ENCODER
    // =========================================================================

    private string $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    /**
     * @return array{q: array<int, int>, s: array<int, int>, b: array<int, array<int, int>>}
     */
    public function encode(string $data, int $ecl = 1): array
    {
        [$mode, $vers, $ec, $dataArr] = $this->qrEncodeData($data, $ecl);
        $dataArr = $this->qrEncodeEc($dataArr, $ec, $vers);
        [$size, $mtx] = $this->qrCreateMatrix($vers, $dataArr);
        [$mask, $mtx] = $this->qrApplyBestMask($mtx, $size);
        $mtx = $this->qrFinalizeMatrix($mtx, $size, $ecl, $mask, $vers);

        return [
            'q' => [4, 4, 4, 4],
            's' => [$size, $size],
            'b' => $mtx,
        ];
    }

    /**
     * @return array{0: int, 1: int, 2: array<int, int>, 3: array<int, int>}
     */
    private function qrEncodeData(string $data, int $ecl): array
    {
        $mode = $this->qrDetectMode($data);
        $version = $this->qrDetectVersion($data, $mode, $ecl);
        $versionGroup = ($version < 10) ? 0 : (($version < 27) ? 1 : 2);
        $ecParams = self::$qrEcParams[($version - 1) * 4 + $ecl];

        $maxChars = self::$qrCapacity[$version - 1][$ecl][$mode];
        if ($mode === 3) {
            $maxChars <<= 1;
        }
        $data = substr($data, 0, $maxChars);

        switch ($mode) {
            case 0:
                $code = $this->qrEncodeNumeric($data, $versionGroup);
                break;
            case 1:
                $code = $this->qrEncodeAlphanumeric($data, $versionGroup);
                break;
            case 3:
                $code = $this->qrEncodeKanji($data, $versionGroup);
                break;
            default:
                $code = $this->qrEncodeBinary($data, $versionGroup);
                break;
        }

        for ($i = 0; $i < 4; $i++) {
            $code[] = 0;
        }
        while (count($code) % 8) {
            $code[] = 0;
        }

        $outData = [];
        for ($i = 0, $n = count($code); $i < $n; $i += 8) {
            $byte = 0;
            if ($code[$i + 0]) {
                $byte |= 0x80;
            }
            if ($code[$i + 1]) {
                $byte |= 0x40;
            }
            if ($code[$i + 2]) {
                $byte |= 0x20;
            }
            if ($code[$i + 3]) {
                $byte |= 0x10;
            }
            if ($code[$i + 4]) {
                $byte |= 0x08;
            }
            if ($code[$i + 5]) {
                $byte |= 0x04;
            }
            if ($code[$i + 6]) {
                $byte |= 0x02;
            }
            if ($code[$i + 7]) {
                $byte |= 0x01;
            }
            $outData[] = $byte;
        }

        for ($i = count($outData), $a = 1, $n = $ecParams[0]; $i < $n; $i++, $a ^= 1) {
            $outData[] = $a ? 236 : 17;
        }

        return [$mode, $version, $ecParams, $outData];
    }

    private function qrDetectMode(string $data): int
    {
        if (preg_match('/^[0-9]*$/', $data)) {
            return 0;
        }
        if (preg_match('/^[0-9A-Z .\/:$%*+-]*$/', $data)) {
            return 1;
        }
        if (preg_match('/^([\x81-\x9F\xE0-\xEA][\x40-\xFC]|[\xEB][\x40-\xBF])*$/', $data)) {
            return 3;
        }

        return 2;
    }

    private function qrDetectVersion(string $data, int $mode, int $ecl): int
    {
        $length = strlen($data);
        if ($mode === 3) {
            $length >>= 1;
        }
        for ($v = 0; $v < 40; $v++) {
            if ($length <= self::$qrCapacity[$v][$ecl][$mode]) {
                return $v + 1;
            }
        }

        return 40;
    }

    /**
     * @return array<int, int>
     */
    private function qrEncodeNumeric(string $data, int $versionGroup): array
    {
        $code = [0, 0, 0, 1];
        $length = strlen($data);
        switch ($versionGroup) {
            case 2:
                $code[] = $length & 0x2000;
                $code[] = $length & 0x1000;
                // fall through
            case 1:
                $code[] = $length & 0x0800;
                $code[] = $length & 0x0400;
                // fall through
            case 0:
                $code[] = $length & 0x0200;
                $code[] = $length & 0x0100;
                $code[] = $length & 0x0080;
                $code[] = $length & 0x0040;
                $code[] = $length & 0x0020;
                $code[] = $length & 0x0010;
                $code[] = $length & 0x0008;
                $code[] = $length & 0x0004;
                $code[] = $length & 0x0002;
                $code[] = $length & 0x0001;
                break;
        }
        for ($i = 0; $i < $length; $i += 3) {
            $group = substr($data, $i, 3);
            $val = (int) $group;
            switch (strlen($group)) {
                case 3:
                    $code[] = $val & 0x200;
                    $code[] = $val & 0x100;
                    $code[] = $val & 0x080;
                    // fall through
                case 2:
                    $code[] = $val & 0x040;
                    $code[] = $val & 0x020;
                    $code[] = $val & 0x010;
                    // fall through
                case 1:
                    $code[] = $val & 0x008;
                    $code[] = $val & 0x004;
                    $code[] = $val & 0x002;
                    $code[] = $val & 0x001;
                    break;
            }
        }

        return array_map(fn ($b) => $b ? 1 : 0, $code);
    }

    /**
     * @return array<int, int>
     */
    private function qrEncodeAlphanumeric(string $data, int $versionGroup): array
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';
        $code = [0, 0, 1, 0];
        $length = strlen($data);
        switch ($versionGroup) {
            case 2:
                $code[] = $length & 0x1000;
                $code[] = $length & 0x0800;
                // fall through
            case 1:
                $code[] = $length & 0x0400;
                $code[] = $length & 0x0200;
                // fall through
            case 0:
                $code[] = $length & 0x0100;
                $code[] = $length & 0x0080;
                $code[] = $length & 0x0040;
                $code[] = $length & 0x0020;
                $code[] = $length & 0x0010;
                $code[] = $length & 0x0008;
                $code[] = $length & 0x0004;
                $code[] = $length & 0x0002;
                $code[] = $length & 0x0001;
                break;
        }
        for ($i = 0; $i < $length; $i += 2) {
            $group = substr($data, $i, 2);
            if (strlen($group) > 1) {
                $c1 = strpos($alphabet, $group[0]);
                $c2 = strpos($alphabet, $group[1]);
                $ch = $c1 * 45 + $c2;
                $code[] = $ch & 0x400;
                $code[] = $ch & 0x200;
                $code[] = $ch & 0x100;
                $code[] = $ch & 0x080;
                $code[] = $ch & 0x040;
                $code[] = $ch & 0x020;
                $code[] = $ch & 0x010;
                $code[] = $ch & 0x008;
                $code[] = $ch & 0x004;
                $code[] = $ch & 0x002;
                $code[] = $ch & 0x001;
            } else {
                $ch = strpos($alphabet, $group);
                $code[] = $ch & 0x020;
                $code[] = $ch & 0x010;
                $code[] = $ch & 0x008;
                $code[] = $ch & 0x004;
                $code[] = $ch & 0x002;
                $code[] = $ch & 0x001;
            }
        }

        return array_map(fn ($b) => $b ? 1 : 0, $code);
    }

    /**
     * @return array<int, int>
     */
    private function qrEncodeBinary(string $data, int $versionGroup): array
    {
        $code = [0, 1, 0, 0];
        $length = strlen($data);
        switch ($versionGroup) {
            case 2:
            case 1:
                $code[] = $length & 0x8000;
                $code[] = $length & 0x4000;
                $code[] = $length & 0x2000;
                $code[] = $length & 0x1000;
                $code[] = $length & 0x0800;
                $code[] = $length & 0x0400;
                $code[] = $length & 0x0200;
                $code[] = $length & 0x0100;
                // fall through
            case 0:
                $code[] = $length & 0x0080;
                $code[] = $length & 0x0040;
                $code[] = $length & 0x0020;
                $code[] = $length & 0x0010;
                $code[] = $length & 0x0008;
                $code[] = $length & 0x0004;
                $code[] = $length & 0x0002;
                $code[] = $length & 0x0001;
                break;
        }
        for ($i = 0; $i < $length; $i++) {
            $ch = ord($data[$i]);
            $code[] = $ch & 0x80;
            $code[] = $ch & 0x40;
            $code[] = $ch & 0x20;
            $code[] = $ch & 0x10;
            $code[] = $ch & 0x08;
            $code[] = $ch & 0x04;
            $code[] = $ch & 0x02;
            $code[] = $ch & 0x01;
        }

        return array_map(fn ($b) => $b ? 1 : 0, $code);
    }

    /**
     * @return array<int, int>
     */
    private function qrEncodeKanji(string $data, int $versionGroup): array
    {
        $code = [1, 0, 0, 0];
        $length = strlen($data) >> 1;
        switch ($versionGroup) {
            case 2:
                $code[] = $length & 0x1000;
                $code[] = $length & 0x0800;
                // fall through
            case 1:
                $code[] = $length & 0x0400;
                $code[] = $length & 0x0200;
                // fall through
            case 0:
                $code[] = $length & 0x0100;
                $code[] = $length & 0x0080;
                $code[] = $length & 0x0040;
                $code[] = $length & 0x0020;
                $code[] = $length & 0x0010;
                $code[] = $length & 0x0008;
                $code[] = $length & 0x0004;
                $code[] = $length & 0x0002;
                $code[] = $length & 0x0001;
                break;
        }
        for ($i = 0; $i < $length; $i++) {
            $ch = (ord($data[$i * 2]) << 8) | ord($data[$i * 2 + 1]);
            if ($ch <= 0x9FFC) {
                $ch -= 0x8140;
            } else {
                $ch -= 0xC140;
            }
            $ch = (($ch >> 8) * 0xC0) + ($ch & 0xFF);
            $code[] = $ch & 0x1000;
            $code[] = $ch & 0x0800;
            $code[] = $ch & 0x0400;
            $code[] = $ch & 0x0200;
            $code[] = $ch & 0x0100;
            $code[] = $ch & 0x0080;
            $code[] = $ch & 0x0040;
            $code[] = $ch & 0x0020;
            $code[] = $ch & 0x0010;
            $code[] = $ch & 0x0008;
            $code[] = $ch & 0x0004;
            $code[] = $ch & 0x0002;
            $code[] = $ch & 0x0001;
        }

        return array_map(fn ($b) => $b ? 1 : 0, $code);
    }

    /**
     * @param  array<int, int>  $data
     * @param  array<int, int>  $ecParams
     * @return array<int, int>
     */
    private function qrEncodeEc(array $data, array $ecParams, int $version): array
    {
        $blocks = $this->qrEcSplit($data, $ecParams);
        $ecBlocks = [];
        foreach ($blocks as $block) {
            $ecBlocks[] = $this->qrEcDivide($block, $ecParams[1]);
        }

        return $this->qrEcInterleave($blocks, $ecBlocks, $ecParams, $version);
    }

    /**
     * @param  array<int, int>  $data
     * @param  array<int, int>  $ecParams
     * @return array<int, array<int, int>>
     */
    private function qrEcSplit(array $data, array $ecParams): array
    {
        $blocks = [];
        $offset = 0;
        for ($b = 0; $b < $ecParams[2]; $b++) {
            $blocks[] = array_slice($data, $offset, $ecParams[3]);
            $offset += $ecParams[3];
        }
        for ($b = 0; $b < $ecParams[4]; $b++) {
            $blocks[] = array_slice($data, $offset, $ecParams[5]);
            $offset += $ecParams[5];
        }

        return $blocks;
    }

    /**
     * @param  array<int, int>  $data
     * @return array<int, int>
     */
    private function qrEcDivide(array $data, int $ecCount): array
    {
        $poly = self::$qrPoly[$ecCount];
        $msg = array_merge($data, array_fill(0, $ecCount, 0));

        for ($i = 0, $n = count($data); $i < $n; $i++) {
            $coef = $msg[$i];
            if ($coef !== 0) {
                $logCoef = self::$qrLog[$coef];
                for ($j = 0; $j <= $ecCount; $j++) {
                    $msg[$i + $j] ^= self::$qrExp[($poly[$j] + $logCoef) % 255];
                }
            }
        }

        return array_slice($msg, count($data));
    }

    /**
     * @param  array<int, array<int, int>>  $dataBlocks
     * @param  array<int, array<int, int>>  $ecBlocks
     * @param  array<int, int>  $ecParams
     * @return array<int, int>
     */
    private function qrEcInterleave(array $dataBlocks, array $ecBlocks, array $ecParams, int $version): array
    {
        $interleaved = [];
        $maxDataLen = max($ecParams[3], $ecParams[5]);

        for ($i = 0; $i < $maxDataLen; $i++) {
            foreach ($dataBlocks as $block) {
                if (isset($block[$i])) {
                    $interleaved[] = $block[$i];
                }
            }
        }

        for ($i = 0; $i < $ecParams[1]; $i++) {
            foreach ($ecBlocks as $block) {
                $interleaved[] = $block[$i];
            }
        }

        $remainderBits = self::$qrRemainder[$version - 1];
        if ($remainderBits > 0) {
            $interleaved[] = 0;
        }

        return $interleaved;
    }

    /**
     * @param  array<int, int>  $data
     * @return array{0: int, 1: array<int, array<int, int|null>>}
     */
    private function qrCreateMatrix(int $version, array $data): array
    {
        $size = 17 + 4 * $version;
        $matrix = array_fill(0, $size, array_fill(0, $size, null));

        // Finder patterns
        $this->qrPlaceFinder($matrix, 0, 0);
        $this->qrPlaceFinder($matrix, 0, $size - 7);
        $this->qrPlaceFinder($matrix, $size - 7, 0);

        // Alignment patterns
        if ($version > 1) {
            $alignCoords = self::$qrAlign[$version - 1];
            foreach ($alignCoords as $r) {
                foreach ($alignCoords as $c) {
                    if ($matrix[$r][$c] === null) {
                        $this->qrPlaceAlignment($matrix, $r, $c);
                    }
                }
            }
        }

        // Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $val = ($i % 2 === 0) ? 1 : 0;
            if ($matrix[6][$i] === null) {
                $matrix[6][$i] = $val;
            }
            if ($matrix[$i][6] === null) {
                $matrix[$i][6] = $val;
            }
        }

        // Dark module
        $matrix[4 * $version + 9][8] = 1;

        // Reserve format information areas
        for ($i = 0; $i < 9; $i++) {
            if ($matrix[8][$i] === null) {
                $matrix[8][$i] = 0;
            }
            if ($matrix[$i][8] === null) {
                $matrix[$i][8] = 0;
            }
        }
        for ($i = $size - 8; $i < $size; $i++) {
            if ($matrix[8][$i] === null) {
                $matrix[8][$i] = 0;
            }
        }
        for ($i = $size - 7; $i < $size; $i++) {
            if ($matrix[$i][8] === null) {
                $matrix[$i][8] = 0;
            }
        }

        // Reserve version information areas (Version >= 7)
        if ($version >= 7) {
            for ($i = 0; $i < 6; $i++) {
                for ($j = 0; $j < 3; $j++) {
                    $matrix[$i][$size - 11 + $j] = 0;
                    $matrix[$size - 11 + $j][$i] = 0;
                }
            }
        }

        // Place data bits
        $bitIdx = 0;
        $totalBits = count($data) * 8;
        $dir = -1;
        $row = $size - 1;
        $col = $size - 1;

        while ($col > 0) {
            if ($col === 6) {
                $col--;
            }
            for ($count = 0; $count < $size; $count++) {
                for ($c = 0; $c < 2; $c++) {
                    $currCol = $col - $c;
                    if ($matrix[$row][$currCol] === null) {
                        $bit = 0;
                        if ($bitIdx < $totalBits) {
                            $byte = $data[(int) ($bitIdx / 8)];
                            $bit = ($byte >> (7 - ($bitIdx % 8))) & 1;
                            $bitIdx++;
                        }
                        $matrix[$row][$currCol] = $bit;
                    }
                }
                $row += $dir;
            }
            $dir = -$dir;
            $row += $dir;
            $col -= 2;
        }

        return [$size, $matrix];
    }

    /**
     * @param  array<int, array<int, int|null>>  $matrix
     */
    private function qrPlaceFinder(array &$matrix, int $row, int $col): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $currR = $row + $r;
                $currC = $col + $c;
                if ($currR >= 0 && $currR < count($matrix) && $currC >= 0 && $currC < count($matrix)) {
                    if ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) {
                        $isDark = ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                        $matrix[$currR][$currC] = $isDark ? 1 : 0;
                    } else {
                        $matrix[$currR][$currC] = 0;
                    }
                }
            }
        }
    }

    /**
     * @param  array<int, array<int, int|null>>  $matrix
     */
    private function qrPlaceAlignment(array &$matrix, int $row, int $col): void
    {
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                $isDark = (abs($r) === 2 || abs($c) === 2 || ($r === 0 && $c === 0));
                $matrix[$row + $r][$col + $c] = $isDark ? 1 : 0;
            }
        }
    }

    /**
     * @param  array<int, array<int, int|null>>  $matrix
     * @return array{0: int, 1: array<int, array<int, int>>}
     */
    private function qrApplyBestMask(array $matrix, int $size): array
    {
        $bestMask = 0;
        $minPenalty = PHP_INT_MAX;
        $bestMatrix = [];

        for ($mask = 0; $mask < 8; $mask++) {
            $masked = $this->qrApplyMask($matrix, $size, $mask);
            $penalty = $this->qrPenalty($masked, $size);
            if ($penalty < $minPenalty) {
                $minPenalty = $penalty;
                $bestMask = $mask;
                $bestMatrix = $masked;
            }
        }

        return [$bestMask, $bestMatrix];
    }

    /**
     * @param  array<int, array<int, int|null>>  $matrix
     * @return array<int, array<int, int>>
     */
    private function qrApplyMask(array $matrix, int $size, int $mask): array
    {
        $res = [];
        for ($r = 0; $r < $size; $r++) {
            $res[$r] = [];
            for ($c = 0; $c < $size; $c++) {
                $val = (int) $matrix[$r][$c];
                // Check if module is part of function pattern
                if ($this->qrIsFunctionModule($r, $c, $size)) {
                    $res[$r][$c] = $val;
                } else {
                    $invert = match ($mask) {
                        0 => (($r + $c) % 2 === 0),
                        1 => ($r % 2 === 0),
                        2 => ($c % 3 === 0),
                        3 => (($r + $c) % 3 === 0),
                        4 => (((int) ($r / 2) + (int) ($c / 3)) % 2 === 0),
                        5 => (($r * $c) % 2 + ($r * $c) % 3 === 0),
                        6 => ((($r * $c) % 2 + ($r * $c) % 3) % 2 === 0),
                        7 => ((($r + $c) % 2 + ($r * $c) % 3) % 2 === 0),
                        default => false,
                    };
                    $res[$r][$c] = $invert ? ($val ^ 1) : $val;
                }
            }
        }

        return $res;
    }

    private function qrIsFunctionModule(int $r, int $c, int $size): bool
    {
        // Finder 1 + Separator
        if ($r <= 8 && $c <= 8) {
            return true;
        }
        // Finder 2 + Separator
        if ($r <= 8 && $c >= $size - 8) {
            return true;
        }
        // Finder 3 + Separator
        if ($r >= $size - 8 && $c <= 8) {
            return true;
        }
        // Timing
        if ($r === 6 || $c === 6) {
            return true;
        }
        // Dark module
        $version = (int) (($size - 17) / 4);
        if ($r === 4 * $version + 9 && $c === 8) {
            return true;
        }
        // Version info
        if ($version >= 7) {
            if ($r < 6 && $c >= $size - 11) {
                return true;
            }
            if ($r >= $size - 11 && $c < 6) {
                return true;
            }
        }
        // Alignment
        if ($version > 1) {
            $alignCoords = self::$qrAlign[$version - 1];
            foreach ($alignCoords as $ar) {
                foreach ($alignCoords as $ac) {
                    if (abs($r - $ar) <= 2 && abs($c - $ac) <= 2) {
                        // Exclude finders
                        if (! ($ar === 6 && $ac === 6) && ! ($ar === 6 && $ac === $size - 7) && ! ($ar === $size - 7 && $ac === 6)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<int, int>>  $m
     */
    private function qrPenalty(array $m, int $size): int
    {
        $score = 0;

        // Rule 1: Consecutive row/col
        for ($r = 0; $r < $size; $r++) {
            $curR = -1;
            $lenR = 0;
            $curC = -1;
            $lenC = 0;
            for ($c = 0; $c < $size; $c++) {
                if ($m[$r][$c] === $curR) {
                    $lenR++;
                } else {
                    if ($lenR >= 5) {
                        $score += 3 + ($lenR - 5);
                    }
                    $curR = $m[$r][$c];
                    $lenR = 1;
                }
                if ($m[$c][$r] === $curC) {
                    $lenC++;
                } else {
                    if ($lenC >= 5) {
                        $score += 3 + ($lenC - 5);
                    }
                    $curC = $m[$c][$r];
                    $lenC = 1;
                }
            }
            if ($lenR >= 5) {
                $score += 3 + ($lenR - 5);
            }
            if ($lenC >= 5) {
                $score += 3 + ($lenC - 5);
            }
        }

        // Rule 2: 2x2 blocks
        for ($r = 0; $r < $size - 1; $r++) {
            for ($c = 0; $c < $size - 1; $c++) {
                $val = $m[$r][$c];
                if ($m[$r + 1][$c] === $val && $m[$r][$c + 1] === $val && $m[$r + 1][$c + 1] === $val) {
                    $score += 3;
                }
            }
        }

        // Rule 3: 1:1:3:1:1 pattern
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size - 10; $c++) {
                if (
                    $m[$r][$c] === 1 && $m[$r][$c + 1] === 0 && $m[$r][$c + 2] === 1 && $m[$r][$c + 3] === 1
                    && $m[$r][$c + 4] === 1 && $m[$r][$c + 5] === 0 && $m[$r][$c + 6] === 1
                    && (($c >= 4 && $m[$r][$c - 1] === 0 && $m[$r][$c - 2] === 0 && $m[$r][$c - 3] === 0 && $m[$r][$c - 4] === 0)
                        || ($c + 10 < $size && $m[$r][$c + 7] === 0 && $m[$r][$c + 8] === 0 && $m[$r][$c + 9] === 0 && $m[$r][$c + 10] === 0))
                ) {
                    $score += 40;
                }
                if (
                    $m[$c][$r] === 1 && $m[$c + 1][$r] === 0 && $m[$c + 2][$r] === 1 && $m[$c + 3][$r] === 1
                    && $m[$c + 4][$r] === 1 && $m[$c + 5][$r] === 0 && $m[$c + 6][$r] === 1
                    && (($c >= 4 && $m[$c - 1][$r] === 0 && $m[$c - 2][$r] === 0 && $m[$c - 3][$r] === 0 && $m[$c - 4][$r] === 0)
                        || ($c + 10 < $size && $m[$c + 7][$r] === 0 && $m[$c + 8][$r] === 0 && $m[$c + 9][$r] === 0 && $m[$c + 10][$r] === 0))
                ) {
                    $score += 40;
                }
            }
        }

        // Rule 4: Balance of dark / light
        $dark = 0;
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($m[$r][$c] === 1) {
                    $dark++;
                }
            }
        }
        $ratio = (int) (($dark * 100) / ($size * $size));
        $score += (int) (abs($ratio - 50) / 5) * 10;

        return $score;
    }

    /**
     * @param  array<int, array<int, int>>  $matrix
     * @return array<int, array<int, int>>
     */
    private function qrFinalizeMatrix(array $matrix, int $size, int $ecl, int $mask, int $version): array
    {
        // Format information
        $formatBits = self::$qrFormatBits[($ecl << 3) | $mask];

        // Format info bits around top-left finder
        $topCoords = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8],
        ];
        for ($i = 0; $i < 15; $i++) {
            $bit = ($formatBits >> (14 - $i)) & 1;
            [$r, $c] = $topCoords[$i];
            $matrix[$r][$c] = $bit;
        }

        // Format info bits split between top-right and bottom-left
        for ($i = 0; $i < 7; $i++) {
            $bit = ($formatBits >> (14 - $i)) & 1;
            $matrix[$size - 1 - $i][8] = $bit;
        }
        for ($i = 7; $i < 15; $i++) {
            $bit = ($formatBits >> (14 - $i)) & 1;
            $matrix[8][$size - 15 + $i] = $bit;
        }

        // Version information (Version >= 7)
        if ($version >= 7) {
            $versionBits = self::$qrVersionBits[$version - 7];
            for ($i = 0; $i < 18; $i++) {
                $bit = ($versionBits >> $i) & 1;
                $r = (int) ($i / 3);
                $c = $size - 11 + ($i % 3);
                $matrix[$r][$c] = $bit;
                $matrix[$c][$r] = $bit;
            }
        }

        return $matrix;
    }

    // =========================================================================
    // TABEL STATIS KONSTANTA QR CODE
    // =========================================================================

    /** @var array<int, int> */
    private static array $qrFormatBits = [
        0x5412, 0x5125, 0x5E7C, 0x5B4B, 0x45F9, 0x40CE, 0x4F97, 0x4AA0,
        0x77C4, 0x72F3, 0x7DAA, 0x789D, 0x662F, 0x6318, 0x6C41, 0x6976,
        0x1689, 0x13BE, 0x1CE7, 0x19D0, 0x0762, 0x0255, 0x0D0C, 0x083B,
        0x355F, 0x3068, 0x3F31, 0x3A06, 0x24B4, 0x2183, 0x2EDA, 0x2BED,
    ];

    /** @var array<int, int> */
    private static array $qrVersionBits = [
        0x07C94, 0x085BC, 0x09A99, 0x0A4D3, 0x0BBF6, 0x0C762, 0x0D847, 0x0E60D,
        0x0F928, 0x10B78, 0x1145D, 0x12A17, 0x13532, 0x149A6, 0x15683, 0x168C9,
        0x177EC, 0x18EC4, 0x191E1, 0x1AFAB, 0x1B08E, 0x1CC1A, 0x1D33F, 0x1ED75,
        0x1F250, 0x209D5, 0x216F0, 0x228BA, 0x2379F, 0x24B0B, 0x2542E, 0x26A64,
        0x27541, 0x28C69,
    ];

    /** @var array<int, int> */
    private static array $qrRemainder = [
        0, 7, 7, 7, 7, 7, 0, 0, 0, 0, 0, 0, 0, 3, 3, 3, 3, 3, 3, 3,
        4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 0, 0, 0, 0, 0, 0,
    ];

    /** @var array<int, array<int, int>> */
    private static array $qrAlign = [
        [], [6, 18], [6, 22], [6, 26], [6, 30], [6, 34],
        [6, 22, 38], [6, 24, 42], [6, 26, 46], [6, 28, 50], [6, 30, 54],
        [6, 32, 58], [6, 34, 62], [6, 26, 46, 66], [6, 26, 48, 70], [6, 26, 50, 74],
        [6, 30, 54, 78], [6, 30, 56, 82], [6, 30, 58, 86], [6, 34, 62, 90],
        [6, 28, 50, 72, 94], [6, 26, 50, 74, 98], [6, 30, 54, 78, 102],
        [6, 28, 54, 80, 106], [6, 32, 58, 84, 110], [6, 30, 58, 86, 114],
        [6, 34, 62, 90, 118], [6, 26, 50, 74, 98, 122], [6, 30, 54, 78, 102, 126],
        [6, 26, 52, 78, 104, 130], [6, 30, 56, 82, 108, 134], [6, 34, 60, 86, 112, 138],
        [6, 30, 58, 86, 114, 142], [6, 34, 62, 90, 118, 146], [6, 30, 54, 78, 102, 126, 150],
        [6, 24, 50, 76, 102, 128, 154], [6, 28, 54, 80, 106, 132, 158],
        [6, 32, 58, 84, 110, 136, 162], [6, 26, 54, 82, 110, 138, 166],
        [6, 30, 58, 86, 114, 142, 170],
    ];

    /** @var array<int, array<int, array<int, int>>> */
    private static array $qrCapacity = [
        [[41, 25, 17, 10], [34, 20, 14, 8], [27, 16, 11, 7], [17, 10, 7, 4]],
        [[77, 47, 32, 20], [63, 38, 26, 16], [48, 29, 20, 12], [34, 20, 14, 8]],
        [[127, 77, 53, 32], [101, 61, 42, 26], [77, 47, 32, 20], [58, 35, 24, 15]],
        [[187, 114, 78, 48], [149, 90, 62, 38], [111, 67, 46, 28], [82, 50, 34, 21]],
        [[255, 154, 106, 65], [202, 122, 84, 52], [144, 87, 60, 37], [106, 64, 44, 27]],
        [[322, 195, 134, 82], [255, 154, 106, 65], [178, 108, 74, 45], [139, 84, 58, 36]],
        [[370, 224, 154, 95], [293, 178, 122, 75], [207, 125, 86, 53], [154, 93, 64, 39]],
        [[461, 279, 192, 118], [365, 221, 152, 93], [259, 157, 108, 66], [202, 122, 84, 52]],
        [[552, 335, 230, 141], [432, 262, 180, 111], [312, 189, 130, 80], [235, 143, 98, 60]],
        [[652, 395, 271, 167], [513, 311, 213, 131], [364, 221, 151, 93], [288, 174, 119, 74]],
        [[772, 468, 321, 198], [604, 366, 251, 155], [427, 259, 177, 109], [331, 200, 137, 85]],
        [[883, 535, 367, 226], [691, 419, 287, 177], [489, 296, 203, 125], [374, 227, 155, 96]],
        [[1022, 619, 425, 262], [796, 483, 331, 204], [580, 352, 241, 149], [427, 259, 177, 109]],
        [[1101, 667, 458, 282], [871, 528, 362, 223], [621, 376, 258, 159], [468, 283, 194, 120]],
        [[1250, 758, 520, 320], [991, 600, 412, 254], [703, 426, 292, 180], [530, 321, 220, 136]],
        [[1408, 854, 586, 361], [1082, 656, 450, 277], [775, 470, 322, 198], [602, 365, 250, 154]],
        [[1548, 938, 644, 397], [1212, 734, 504, 310], [876, 531, 364, 224], [674, 408, 280, 173]],
        [[1725, 1046, 718, 442], [1346, 816, 560, 345], [948, 574, 394, 243], [746, 452, 310, 191]],
        [[1903, 1153, 792, 488], [1500, 909, 624, 384], [1050, 636, 437, 269], [813, 493, 338, 208]],
        [[2061, 1249, 858, 528], [1600, 970, 666, 410], [1139, 690, 474, 292], [919, 557, 382, 235]],
        [[2232, 1352, 929, 572], [1708, 1035, 711, 438], [1214, 735, 505, 311], [999, 605, 415, 256]],
        [[2409, 1460, 1003, 618], [1872, 1134, 779, 480], [1345, 815, 560, 345], [1069, 648, 445, 274]],
        [[2620, 1588, 1091, 672], [2059, 1248, 857, 528], [1451, 879, 604, 372], [1169, 708, 486, 299]],
        [[2812, 1704, 1171, 721], [2188, 1326, 911, 561], [1589, 963, 661, 407], [1273, 772, 530, 326]],
        [[3042, 1844, 1267, 779], [2395, 1451, 997, 614], [1706, 1034, 710, 437], [1367, 828, 569, 350]],
        [[3283, 1990, 1367, 841], [2544, 1542, 1059, 652], [1843, 1117, 767, 472], [1465, 888, 610, 376]],
        [[3517, 2132, 1465, 901], [2701, 1637, 1125, 692], [1955, 1185, 814, 501], [1528, 926, 636, 392]],
        [[3669, 2223, 1528, 940], [2857, 1732, 1190, 732], [2081, 1261, 867, 534], [1628, 987, 678, 417]],
        [[3909, 2369, 1628, 1002], [3035, 1839, 1264, 778], [2240, 1358, 933, 574], [1732, 1050, 721, 444]],
        [[4158, 2520, 1732, 1065], [3289, 1994, 1370, 843], [2382, 1444, 992, 611], [1840, 1115, 766, 472]],
        [[4417, 2677, 1840, 1132], [3486, 2113, 1452, 894], [2573, 1559, 1072, 660], [1952, 1183, 813, 500]],
        [[4686, 2840, 1952, 1201], [3693, 2238, 1538, 947], [2769, 1678, 1154, 710], [2068, 1253, 861, 530]],
        [[4965, 3009, 2068, 1273], [3909, 2369, 1628, 1002], [2880, 1745, 1200, 738], [2188, 1326, 911, 561]],
        [[5253, 3183, 2188, 1347], [4134, 2506, 1722, 1060], [3166, 1919, 1319, 812], [2303, 1396, 959, 590]],
        [[5529, 3351, 2303, 1417], [4343, 2632, 1809, 1113], [3358, 2035, 1399, 861], [2431, 1473, 1013, 623]],
        [[5836, 3537, 2431, 1496], [4588, 2780, 1911, 1176], [3577, 2168, 1490, 917], [2563, 1553, 1068, 657]],
        [[6153, 3729, 2563, 1577], [4775, 2894, 1989, 1224], [3780, 2291, 1574, 969], [2699, 1636, 1124, 692]],
        [[6479, 3927, 2699, 1661], [5039, 3054, 2099, 1292], [4012, 2431, 1672, 1029], [2809, 1702, 1170, 720]],
        [[6743, 4087, 2809, 1729], [5313, 3220, 2213, 1362], [4265, 2585, 1777, 1094], [2977, 1804, 1240, 763]],
        [[7089, 4296, 2953, 1817], [5596, 3391, 2331, 1435], [4449, 2696, 1853, 1141], [3125, 1894, 1302, 801]],
    ];

    /** @var array<int, array<int, int>> */
    private static array $qrEcParams = [
        [19, 7, 1, 19, 0, 0], [16, 10, 1, 16, 0, 0], [13, 13, 1, 13, 0, 0], [9, 17, 1, 9, 0, 0],
        [34, 10, 1, 34, 0, 0], [28, 16, 1, 28, 0, 0], [22, 22, 1, 22, 0, 0], [16, 28, 1, 16, 0, 0],
        [55, 15, 1, 55, 0, 0], [44, 26, 1, 44, 0, 0], [34, 18, 2, 17, 0, 0], [26, 22, 2, 13, 0, 0],
        [80, 20, 1, 80, 0, 0], [64, 18, 2, 32, 0, 0], [48, 26, 2, 24, 0, 0], [36, 16, 4, 9, 0, 0],
        [108, 26, 1, 108, 0, 0], [86, 24, 2, 43, 0, 0], [62, 18, 2, 15, 2, 16], [46, 22, 2, 11, 2, 12],
        [136, 18, 2, 68, 0, 0], [108, 16, 4, 27, 0, 0], [76, 24, 4, 19, 0, 0], [60, 28, 4, 15, 0, 0],
        [156, 20, 2, 78, 0, 0], [124, 18, 4, 31, 0, 0], [88, 18, 2, 14, 4, 15], [66, 26, 4, 13, 1, 14],
        [194, 24, 2, 97, 0, 0], [154, 22, 2, 38, 2, 39], [110, 22, 4, 18, 2, 19], [86, 26, 4, 14, 2, 15],
        [232, 30, 2, 116, 0, 0], [182, 22, 3, 36, 2, 37], [132, 20, 4, 16, 4, 17], [100, 24, 4, 12, 4, 13],
        [274, 18, 2, 68, 2, 69], [216, 26, 4, 43, 1, 44], [154, 24, 6, 19, 2, 20], [122, 28, 6, 15, 2, 16],
        [324, 20, 4, 81, 0, 0], [254, 30, 1, 50, 4, 51], [180, 28, 4, 22, 4, 23], [140, 24, 3, 12, 8, 13],
        [370, 24, 2, 92, 2, 93], [290, 22, 6, 36, 2, 37], [206, 26, 4, 20, 6, 21], [158, 28, 7, 14, 4, 15],
        [428, 26, 4, 107, 0, 0], [334, 22, 8, 37, 1, 38], [244, 24, 8, 20, 4, 21], [180, 22, 12, 11, 4, 12],
        [461, 30, 3, 115, 1, 116], [365, 24, 4, 40, 5, 41], [261, 20, 11, 16, 5, 17], [197, 24, 11, 12, 5, 13],
        [523, 22, 5, 87, 1, 88], [415, 24, 5, 41, 5, 42], [295, 30, 5, 24, 7, 25], [223, 24, 11, 12, 7, 13],
        [589, 24, 5, 98, 1, 99], [453, 28, 7, 45, 3, 46], [325, 24, 15, 19, 2, 20], [253, 30, 3, 15, 13, 16],
        [647, 28, 1, 107, 5, 108], [507, 28, 10, 46, 1, 47], [367, 28, 1, 22, 15, 23], [283, 28, 2, 14, 17, 15],
        [721, 30, 5, 120, 1, 121], [563, 26, 9, 43, 4, 44], [397, 28, 17, 22, 1, 23], [313, 28, 2, 14, 19, 15],
        [795, 28, 3, 113, 4, 114], [627, 26, 3, 44, 11, 45], [445, 26, 17, 21, 4, 22], [341, 26, 9, 13, 16, 14],
        [861, 28, 3, 107, 5, 108], [669, 26, 3, 41, 13, 42], [485, 26, 15, 24, 5, 25], [385, 28, 15, 15, 10, 16],
        [932, 28, 4, 116, 4, 117], [714, 26, 17, 42, 0, 0], [512, 26, 17, 22, 6, 23], [406, 30, 19, 16, 6, 17],
        [1006, 28, 2, 111, 7, 112], [782, 28, 17, 46, 0, 0], [568, 28, 7, 24, 16, 25], [442, 24, 34, 13, 0, 0],
        [1094, 30, 4, 121, 5, 122], [860, 28, 4, 47, 14, 48], [614, 30, 11, 24, 14, 25], [464, 30, 16, 15, 14, 16],
        [1174, 30, 6, 117, 4, 118], [914, 28, 6, 45, 14, 46], [664, 30, 11, 24, 16, 25], [514, 30, 30, 16, 2, 17],
        [1276, 26, 8, 106, 4, 107], [1000, 28, 8, 47, 13, 48], [718, 30, 7, 24, 22, 25], [538, 30, 22, 15, 13, 16],
        [1370, 28, 10, 114, 2, 115], [1062, 28, 19, 46, 4, 47], [754, 28, 28, 22, 6, 23], [596, 30, 33, 16, 4, 17],
        [1468, 30, 8, 122, 4, 123], [1128, 28, 22, 45, 3, 46], [808, 30, 8, 23, 26, 24], [628, 30, 12, 15, 28, 16],
        [1531, 30, 3, 117, 10, 118], [1193, 28, 3, 45, 23, 46], [871, 30, 4, 24, 31, 25], [661, 30, 11, 15, 31, 16],
        [1631, 30, 7, 116, 7, 117], [1267, 28, 21, 45, 7, 46], [911, 30, 1, 23, 37, 24], [701, 30, 19, 15, 26, 16],
        [1735, 30, 5, 115, 10, 116], [1373, 28, 19, 47, 10, 48], [985, 30, 15, 24, 25, 25], [745, 30, 23, 15, 25, 16],
        [1843, 30, 13, 115, 3, 116], [1455, 28, 2, 46, 29, 47], [1055, 30, 42, 24, 1, 25], [793, 30, 23, 15, 28, 16],
        [1955, 30, 17, 115, 0, 0], [1541, 28, 10, 46, 23, 47], [1115, 30, 10, 24, 35, 25], [845, 30, 19, 15, 35, 16],
        [2071, 30, 17, 115, 1, 116], [1631, 28, 14, 46, 21, 47], [1211, 30, 29, 24, 19, 25], [901, 30, 11, 15, 46, 16],
        [2191, 30, 13, 115, 6, 116], [1725, 28, 14, 46, 23, 47], [1285, 30, 44, 24, 7, 25], [961, 30, 59, 16, 1, 17],
        [2306, 30, 12, 121, 7, 122], [1812, 28, 12, 47, 26, 48], [1353, 30, 39, 24, 14, 25], [986, 30, 22, 15, 41, 16],
        [2434, 30, 6, 121, 14, 122], [1914, 28, 6, 47, 34, 48], [1445, 30, 46, 24, 10, 25], [1054, 30, 2, 15, 64, 16],
        [2566, 30, 17, 122, 4, 123], [1992, 28, 29, 46, 14, 47], [1511, 30, 49, 24, 10, 25], [1096, 30, 24, 15, 46, 16],
        [2702, 30, 4, 122, 18, 123], [2102, 28, 13, 46, 32, 47], [1583, 30, 48, 24, 14, 25], [1142, 30, 42, 15, 32, 16],
        [2812, 30, 20, 117, 4, 118], [2216, 28, 40, 47, 7, 48], [1687, 30, 43, 24, 22, 25], [1222, 30, 10, 15, 67, 16],
        [2956, 30, 19, 118, 6, 119], [2334, 28, 18, 47, 31, 48], [1774, 30, 34, 24, 34, 25], [1276, 30, 20, 15, 61, 16],
    ];

    /** @var array<int, int> */
    private static array $qrExp = [
        1, 2, 4, 8, 16, 32, 64, 128, 29, 58, 116, 232, 205, 135, 19, 38,
        76, 152, 45, 90, 180, 117, 234, 201, 143, 3, 6, 12, 24, 48, 96, 192,
        157, 39, 78, 156, 37, 74, 148, 53, 106, 212, 181, 119, 238, 193, 159, 35,
        70, 140, 5, 10, 20, 40, 80, 160, 93, 186, 105, 210, 185, 111, 222, 161,
        95, 190, 97, 194, 153, 47, 94, 188, 101, 202, 137, 15, 30, 60, 120, 240,
        253, 231, 211, 187, 107, 214, 177, 127, 254, 225, 223, 163, 91, 182, 113, 226,
        217, 175, 67, 134, 17, 34, 68, 136, 13, 26, 52, 104, 208, 189, 103, 206,
        129, 31, 62, 124, 248, 237, 199, 147, 59, 118, 236, 197, 151, 51, 102, 204,
        133, 23, 46, 92, 184, 109, 218, 169, 79, 158, 33, 66, 132, 21, 42, 84,
        168, 77, 154, 41, 82, 164, 85, 170, 73, 146, 57, 114, 228, 213, 183, 115,
        230, 209, 191, 99, 198, 145, 63, 126, 252, 229, 215, 179, 123, 246, 241, 255,
        227, 219, 171, 75, 150, 49, 98, 196, 149, 55, 110, 220, 165, 87, 174, 65,
        130, 25, 50, 100, 200, 141, 7, 14, 28, 56, 112, 224, 221, 167, 83, 166,
        81, 162, 89, 178, 121, 242, 249, 239, 195, 155, 43, 86, 172, 69, 138, 9,
        18, 36, 72, 144, 61, 122, 244, 245, 247, 243, 251, 235, 203, 139, 11, 22,
        44, 88, 176, 125, 250, 233, 207, 131, 27, 54, 108, 216, 173, 71, 142, 1,
    ];

    /** @var array<int, int> */
    private static array $qrLog = [
        0, 0, 1, 25, 2, 50, 26, 198, 3, 223, 51, 238, 27, 104, 199, 75,
        4, 100, 224, 14, 52, 141, 239, 129, 28, 193, 105, 248, 200, 8, 76, 113,
        5, 138, 101, 47, 225, 36, 15, 33, 53, 147, 142, 218, 240, 18, 130, 69,
        29, 181, 194, 125, 106, 39, 249, 185, 201, 154, 9, 120, 77, 228, 114, 166,
        6, 191, 139, 98, 102, 221, 48, 253, 226, 152, 37, 179, 16, 145, 34, 136,
        54, 208, 148, 206, 143, 150, 219, 189, 241, 210, 19, 92, 131, 56, 70, 64,
        30, 66, 182, 163, 195, 72, 126, 110, 107, 58, 40, 84, 250, 133, 186, 61,
        202, 94, 155, 159, 10, 21, 121, 43, 78, 212, 229, 172, 115, 243, 167, 87,
        7, 112, 192, 247, 140, 128, 99, 13, 103, 74, 222, 237, 49, 197, 254, 24,
        227, 165, 153, 119, 38, 184, 180, 124, 17, 68, 146, 217, 35, 32, 137, 46,
        55, 63, 209, 91, 149, 188, 207, 205, 144, 135, 151, 178, 220, 252, 190, 97,
        242, 86, 211, 171, 20, 42, 93, 158, 132, 60, 57, 83, 71, 109, 65, 162,
        31, 45, 67, 216, 183, 123, 164, 118, 196, 23, 73, 73, 127, 12, 111, 246,
        108, 161, 59, 82, 41, 157, 85, 170, 251, 96, 134, 177, 187, 204, 62, 90,
        203, 89, 95, 176, 156, 169, 160, 81, 11, 245, 22, 235, 122, 117, 44, 215,
        79, 174, 213, 233, 230, 231, 173, 232, 116, 214, 244, 234, 168, 80, 88, 175,
    ];

    /** @var array<int, array<int, int>> */
    private static array $qrPoly = [
        [],
        [0],
        [1, 1],
        [2, 2, 1],
        [3, 3, 2, 1],
        [4, 4, 3, 2, 1],
        [5, 5, 4, 3, 2, 1],
        [87, 229, 146, 149, 238, 102, 21],
        [175, 238, 208, 249, 215, 252, 196, 28],
        [251, 67, 46, 61, 118, 70, 64, 94, 36],
        [216, 194, 159, 111, 199, 94, 95, 113, 157, 45],
        [17, 60, 79, 50, 61, 163, 26, 187, 202, 180, 221, 225],
        [74, 152, 176, 57, 86, 115, 247, 1, 142, 69, 33, 173, 196, 148],
        [65, 32, 1, 91, 236, 155, 157, 192, 117, 22, 234, 108, 112, 218, 154],
        [8, 183, 61, 91, 202, 37, 51, 58, 58, 237, 140, 124, 5, 99, 105, 119],
        [59, 116, 79, 161, 252, 98, 128, 205, 128, 161, 247, 57, 163, 56, 235, 106, 53],
        [246, 215, 112, 104, 168, 80, 191, 157, 166, 199, 87, 84, 137, 237, 162, 57, 83, 141],
        [141, 143, 207, 72, 25, 250, 48, 198, 210, 162, 146, 216, 222, 60, 202, 182, 176, 227, 221],
        [171, 83, 101, 40, 80, 193, 158, 21, 215, 170, 77, 98, 43, 76, 149, 119, 189, 78, 206, 139],
        [203, 102, 47, 142, 234, 217, 18, 196, 175, 158, 242, 164, 146, 18, 200, 138, 194, 224, 107, 111, 201],
        [106, 236, 121, 103, 107, 12, 129, 152, 98, 170, 195, 15, 191, 210, 215, 171, 74, 247, 161, 94, 178, 127],
        [65, 84, 235, 137, 58, 220, 254, 187, 243, 254, 62, 120, 58, 64, 239, 154, 56, 190, 183, 127, 122, 213, 237],
        [45, 131, 170, 214, 80, 248, 160, 94, 229, 8, 96, 142, 120, 89, 113, 89, 213, 140, 125, 236, 125, 84, 150, 162],
        [193, 157, 122, 133, 155, 153, 158, 48, 176, 88, 14, 105, 214, 85, 225, 142, 233, 240, 180, 247, 162, 61, 211, 103, 128],
        [197, 211, 162, 217, 173, 137, 145, 221, 224, 234, 184, 234, 91, 156, 218, 143, 155, 197, 130, 208, 131, 25, 223, 91, 166, 117],
        [149, 73, 123, 40, 71, 88, 150, 48, 143, 163, 149, 127, 49, 202, 43, 234, 40, 197, 171, 203, 195, 85, 107, 131, 149, 10, 200],
        [19, 150, 171, 78, 159, 10, 198, 201, 197, 237, 72, 164, 110, 177, 221, 224, 230, 188, 139, 44, 214, 136, 155, 147, 129, 216, 120, 11],
        [111, 77, 146, 94, 26, 21, 108, 19, 105, 94, 113, 193, 86, 140, 163, 125, 58, 158, 229, 239, 218, 103, 56, 70, 114, 61, 183, 129, 167],
        [168, 223, 200, 104, 224, 234, 108, 180, 110, 190, 195, 147, 162, 54, 219, 75, 222, 73, 158, 2, 229, 165, 132, 221, 172, 130, 189, 195, 250, 182],
        [41, 173, 145, 152, 216, 31, 179, 182, 50, 48, 110, 86, 239, 96, 222, 125, 42, 173, 226, 193, 224, 130, 156, 37, 251, 216, 236, 171, 20, 84, 155],
    ];
}
