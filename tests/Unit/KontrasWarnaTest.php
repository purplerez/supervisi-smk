<?php

/**
 * Test Kontras Warna – Design System Supervisi Guru
 *
 * Menggunakan algoritma WCAG 2.1 untuk menghitung luminans relatif
 * dan rasio kontras. Semua pasangan teks wajib >= 4,5:1.
 *
 * TIDAK menguji orange-500 (#F2842F) sebagai teks (sesuai AGENTS.md).
 */

/**
 * Menghitung luminans relatif sebuah warna dari hex string.
 */
function relativeLuminance(string $hex): float
{
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;

    $linearize = fn (float $c): float => $c <= 0.04045
        ? $c / 12.92
        : (($c + 0.055) / 1.055) ** 2.4;

    return 0.2126 * $linearize($r)
         + 0.7152 * $linearize($g)
         + 0.0722 * $linearize($b);
}

/**
 * Menghitung rasio kontras antara dua warna.
 * Nilai >= 4,5 lulus WCAG AA normal text.
 */
function contrastRatio(string $hex1, string $hex2): float
{
    $l1 = relativeLuminance($hex1);
    $l2 = relativeLuminance($hex2);

    [$lighter, $darker] = $l1 > $l2 ? [$l1, $l2] : [$l2, $l1];

    return ($lighter + 0.05) / ($darker + 0.05);
}

// ─── Definisi pasangan warna ────────────────────────────────────────────────
// Format: [deskripsi, hex-latar, hex-teks, rasio-minimum]
$pasangan = [
    // Tombol utama navy-900 + teks putih
    ['Teks putih di navy-900 (tombol utama)',        '#253C6D', '#FFFFFF', 4.5],

    // Hover navy-700 + teks putih
    ['Teks putih di navy-700 (hover)',               '#30497D', '#FFFFFF', 4.5],

    // Teks ink di latar krem (teks halaman)
    ['Teks ink (#1F2937) di latar krem (#FFFAF3)',   '#FFFAF3', '#1F2937', 4.5],

    // Teks muted di latar krem – harus cukup kontras
    ['Teks muted (#5B6475) di latar krem (#FFFAF3)', '#FFFAF3', '#5B6475', 4.5],

    // Teks ink di white (kartu)
    ['Teks ink di kartu putih (#FFFFFF)',             '#FFFFFF', '#1F2937', 4.5],

    // Tombol Tambah/Simpan: hijau + teks putih
    ['Teks putih di tombol hijau (#2E7D32)',          '#2E7D32', '#FFFFFF', 4.5],

    // Tombol Edit/Ubah: kuning + teks ink (gelap)
    ['Teks ink di tombol kuning (#F5B82E)',           '#F5B82E', '#1F2937', 4.5],

    // Tombol Hapus: merah + teks putih
    ['Teks putih di tombol merah (#C62828)',          '#C62828', '#FFFFFF', 4.5],

    // Badge "belum" (abu): teks #4B5563 di bg #EEF0F4
    ['Badge belum: teks #4B5563 di #EEF0F4',         '#EEF0F4', '#4B5563', 4.5],

    // Badge "draft" (kuning): teks #8A5A00 di bg #FFF3D6
    ['Badge draft: teks #8A5A00 di #FFF3D6',         '#FFF3D6', '#8A5A00', 4.5],

    // Badge "final" (hijau): teks #1B5E20 di bg #E8F5E9
    ['Badge final: teks #1B5E20 di #E8F5E9',         '#E8F5E9', '#1B5E20', 4.5],

    // Badge "direvisi" (oranye muda): teks #8A3B00 di bg #FDE8D4
    ['Badge direvisi: teks #8A3B00 di #FDE8D4',      '#FDE8D4', '#8A3B00', 4.5],

    // orange-700 sebagai teks di atas krem (BUKAN orange-500)
    ['Teks orange-700 (#B54A08) di latar krem',      '#FFFAF3', '#B54A08', 4.5],

    // Teks di nav header navy-50
    ['Teks navy-900 di header navy-50',              '#E6ECF7', '#253C6D', 4.5],
];

foreach ($pasangan as [$deskripsi, $latar, $teks, $minimum]) {
    $rasio = contrastRatio($latar, $teks);

    it("Kontras >= {$minimum}:1 — {$deskripsi}", function () use ($rasio, $minimum, $deskripsi, $latar, $teks) {
        $pesanGagal = sprintf(
            'Kontras terlalu rendah untuk "%s" (%s di %s): %.2f:1 (minimum %.1f:1)',
            $deskripsi,
            $teks,
            $latar,
            $rasio,
            $minimum
        );

        expect($rasio)->toBeGreaterThanOrEqual($minimum, $pesanGagal);
    });
}
