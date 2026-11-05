<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\SviCalculator;

class SviCalculatorTest extends TestCase
{
    /**
     * Uji Rumus Ceiling Variabel 5 (Fisik Bangunan)
     */
    public function test_ceiling_fisik_bangunan(): void
    {
        // Tembok (1) + Keramik (1) = 1.0 => 1
        $this->assertEquals(1, SviCalculator::hitungFisikBangunan(1, 1));

        // Tembok (1) + Semen (2) = 1.5 => 2
        $this->assertEquals(2, SviCalculator::hitungFisikBangunan(1, 2));

        // Campuran (2) + Semen (2) = 2.0 => 2
        $this->assertEquals(2, SviCalculator::hitungFisikBangunan(2, 2));

        // Kayu (3) + Semen (2) = 2.5 => 3 (Sesuai Naskah Bab 3 Tabel 3.4)
        $this->assertEquals(3, SviCalculator::hitungFisikBangunan(3, 2));

        // Campuran (2) + Kayu/Tanah (3) = 2.5 => 3
        $this->assertEquals(3, SviCalculator::hitungFisikBangunan(2, 3));

        // Kayu (3) + Kayu/Tanah (3) = 3.0 => 3
        $this->assertEquals(3, SviCalculator::hitungFisikBangunan(3, 3));
    }

    /**
     * Uji Perhitungan SVI untuk Responden A (Tabel 3.4 Naskah)
     */
    public function test_responden_a_sesuai_tabel_3_4_naskah(): void
    {
        $hasil = SviCalculator::hitung([
            'skor_pendapatan'          => 3,
            'skor_pekerjaan'           => 2,
            'skor_pendidikan'          => 3,
            'skor_jumlah_keluarga'     => 1,
            'skor_dinding'             => 3, // Kayu
            'skor_lantai'              => 2, // Semen
            'skor_sanitasi'            => 2, // Jamban Berbagi
            'skor_sumber_air'          => 3, // Sumur Gali
            'skor_pendapatan_tambahan' => 3, // Tidak ada
            'skor_kepemilikan_lahan'   => 1  // Milik Sendiri
        ]);

        $this->assertEquals(3, $hasil['skor_fisik_bangunan']);
        $this->assertEquals(21, $hasil['total_skor_svi']);
        $this->assertEquals(2.33, $hasil['skor_rata_rata']);
        $this->assertEquals('tinggi', $hasil['klasifikasi']);
        $this->assertEquals('Kerentanan Tinggi', $hasil['klasifikasi_label']);
        $this->assertEquals('merah', $hasil['warna_kerentanan']);
    }

    /**
     * Uji Batas Klasifikasi Equal Interval (Tabel 3.3 Naskah)
     */
    public function test_ambang_batas_equal_interval(): void
    {
        // Skor 9 - 14: Rendah
        $this->assertEquals('rendah', SviCalculator::tentukanKlasifikasi(9)['tingkat']);
        $this->assertEquals('rendah', SviCalculator::tentukanKlasifikasi(14)['tingkat']);

        // Skor 15 - 20: Sedang
        $this->assertEquals('sedang', SviCalculator::tentukanKlasifikasi(15)['tingkat']);
        $this->assertEquals('sedang', SviCalculator::tentukanKlasifikasi(20)['tingkat']);

        // Skor 21 - 27: Tinggi
        $this->assertEquals('tinggi', SviCalculator::tentukanKlasifikasi(21)['tingkat']);
        $this->assertEquals('tinggi', SviCalculator::tentukanKlasifikasi(27)['tingkat']);
    }
}
