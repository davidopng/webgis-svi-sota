<?php

namespace App\Services;

/**
 * Service Komputasi Social Vulnerability Index (SVI)
 * Merujuk pada Metodologi Naskah Skripsi Bab 2 & Bab 3
 * Lokus: Kampung Sota, Distrik Sota, Kabupaten Merauke
 */
class SviCalculator
{
    /**
     * Hitung Skor Komposit Fisik Bangunan (Variabel 5)
     * Menggunakan rumus: Ceiling((Skor_Dinding + Skor_Lantai) / 2)
     * Catatan: Atap adalah material seng umum (homogen) di lokasi penelitian sehingga tidak diikutsertakan.
     *
     * @param int $skorDinding (1: Tembok, 2: Kayu & Tembok/Campuran, 3: Kayu)
     * @param int $skorLantai  (1: Keramik/Ubin, 2: Semen, 3: Tanah/Papan/Kayu)
     * @return int Nilai ordinal 1, 2, atau 3
     */
    public static function hitungFisikBangunan(int $skorDinding, int $skorLantai): int
    {
        return (int) ceil(($skorDinding + $skorLantai) / 2);
    }

    /**
     * Hitung Total Skor SVI (Additive Scoring Linear Tanpa Bobot)
     * Persamaan: SVI = \sum_{i=1}^9 X_i
     * Rentang Skor: 9 sampai 27
     *
     * @param array $skorVariabel Array asosiatif 9 skor variabel
     * @return int
     */
    public static function hitungTotalSvi(array $skorVariabel): int
    {
        $total = 0;
        foreach ($skorVariabel as $skor) {
            $total += (int) $skor;
        }
        return max(9, min(27, $total));
    }

    /**
     * Hitung Skor Rata-Rata SVI
     * Persamaan: Skor Rata-rata = Total Skor SVI / 9
     * Rentang Skor: 1,00 sampai 3,00
     *
     * @param int $totalSkor
     * @return float
     */
    public static function hitungRataRata(int $totalSkor): float
    {
        return round($totalSkor / 9, 2);
    }

    /**
     * Klasifikasi Tingkat Kerentanan Menggunakan Metode Equal Interval (Tabel 3.3 Naskah)
     * Lebar Interval = (3,00 - 1,00) / 3 = 0,67
     *
     * - 9 – 14 (1,00 – < 1,67) => Kerentanan Rendah (Hijau #2C7A4B)
     * - 15 – 20 (1,67 – < 2,33) => Kerentanan Sedang (Kuning #E0A32E)
     * - 21 – 27 (2,33 – 3,00) => Kerentanan Tinggi (Merah #B3352C)
     *
     * @param int $totalSkor
     * @return array
     */
    public static function tentukanKlasifikasi(int $totalSkor): array
    {
        if ($totalSkor <= 14) {
            return [
                'tingkat' => 'rendah',
                'label' => 'Kerentanan Rendah',
                'warna' => 'hijau',
                'hex' => '#2C7A4B',
                'rentang_total' => '9 – 14',
                'rentang_rata_rata' => '1,00 – < 1,67'
            ];
        } elseif ($totalSkor <= 20) {
            return [
                'tingkat' => 'sedang',
                'label' => 'Kerentanan Sedang',
                'warna' => 'kuning',
                'hex' => '#E0A32E',
                'rentang_total' => '15 – 20',
                'rentang_rata_rata' => '1,67 – < 2,33'
            ];
        } else {
            return [
                'tingkat' => 'tinggi',
                'label' => 'Kerentanan Tinggi',
                'warna' => 'merah',
                'hex' => '#B3352C',
                'rentang_total' => '21 – 27',
                'rentang_rata_rata' => '2,33 – 3,00'
            ];
        }
    }

    /**
     * Kalkulasi Komprehensif dari Input Data Mentah
     *
     * @param array $input [
     *   'skor_pendapatan' => 1..3,
     *   'skor_pekerjaan' => 1..3,
     *   'skor_pendidikan' => 1..3,
     *   'skor_jumlah_keluarga' => 1..3,
     *   'skor_dinding' => 1..3,
     *   'skor_lantai' => 1..3,
     *   'skor_sanitasi' => 1..3,
     *   'skor_sumber_air' => 1..3,
     *   'skor_pendapatan_tambahan' => 1..3,
     *   'skor_kepemilikan_lahan' => 1..3
     * ]
     * @return array
     */
    public static function hitung(array $input): array
    {
        $skorFisikBangunan = self::hitungFisikBangunan(
            (int) ($input['skor_dinding'] ?? 1),
            (int) ($input['skor_lantai'] ?? 1)
        );

        $daftarSkor = [
            'pendapatan'           => (int) ($input['skor_pendapatan'] ?? 1),
            'pekerjaan'            => (int) ($input['skor_pekerjaan'] ?? 1),
            'pendidikan'           => (int) ($input['skor_pendidikan'] ?? 1),
            'jumlah_keluarga'      => (int) ($input['skor_jumlah_keluarga'] ?? 1),
            'fisik_bangunan'       => $skorFisikBangunan,
            'sanitasi'             => (int) ($input['skor_sanitasi'] ?? 1),
            'sumber_air'           => (int) ($input['skor_sumber_air'] ?? 1),
            'pendapatan_tambahan'  => (int) ($input['skor_pendapatan_tambahan'] ?? 1),
            'kepemilikan_lahan'    => (int) ($input['skor_kepemilikan_lahan'] ?? 1),
        ];

        $totalSkor = self::hitungTotalSvi($daftarSkor);
        $skorRataRata = self::hitungRataRata($totalSkor);
        $klasifikasi = self::tentukanKlasifikasi($totalSkor);

        return [
            'skor_dinding'          => (int) ($input['skor_dinding'] ?? 1),
            'skor_lantai'           => (int) ($input['skor_lantai'] ?? 1),
            'skor_fisik_bangunan'   => $skorFisikBangunan,
            'daftar_skor'           => $daftarSkor,
            'total_skor_svi'        => $totalSkor,
            'skor_rata_rata'        => $skorRataRata,
            'skor_rata_rata_format' => number_format($skorRataRata, 2, ',', '.'),
            'klasifikasi'           => $klasifikasi['tingkat'],
            'klasifikasi_label'     => $klasifikasi['label'],
            'warna_kerentanan'      => $klasifikasi['warna'],
            'kode_hex'              => $klasifikasi['hex']
        ];
    }
}
