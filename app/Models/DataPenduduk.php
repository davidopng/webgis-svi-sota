<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPenduduk extends Model
{
    use HasFactory;

    protected $table = 'data_penduduk';

    protected $guarded = ['id'];

    protected $appends = [
        'klasifikasi_label',
        'rt_rw',
        'nama_penginput',
        'skor_rata_rata',
        'warna_kerentanan',
        'skor_pendapatan',
        'skor_pekerjaan',
        'skor_pendidikan',
        'skor_jumlah_keluarga',
        'skor_dinding',
        'skor_lantai',
        'skor_fisik_bangunan',
        'skor_sanitasi',
        'skor_sumber_air',
        'skor_pendapatan_tambahan',
        'skor_kepemilikan_lahan',
    ];

    public function getSkorRataRataAttribute(): string
    {
        if ($this->total_skor) {
            return number_format($this->total_skor / 9, 2, ',', '.');
        }
        return '-';
    }

    public function getWarnaKerentananAttribute(): string
    {
        $k = strtolower($this->klasifikasi ?? 'sedang');
        if ($k === 'rendah') return 'hijau';
        if ($k === 'tinggi') return 'merah';
        return 'kuning';
    }

    public function getSkorPendapatanAttribute(): int
    {
        $p = strtolower($this->pendapatan ?? '');
        return str_contains($p, 'kurang') ? 3 : (str_contains($p, '2.500.000') ? 1 : 2);
    }

    public function getSkorPekerjaanAttribute(): int
    {
        $pk = strtolower($this->pekerjaan ?? '');
        if (preg_match('/buruh|serabutan|tidak bekerja|menganggur|irt|ibu rumah/', $pk)) return 3;
        if (preg_match('/pns|tni|polri|tetap|wiraswasta|guru|dosen/', $pk)) return 1;
        return 2;
    }

    public function getSkorPendidikanAttribute(): int
    {
        $pd = strtolower($this->pendidikan ?? '');
        if (preg_match('/perguruan|diploma|s1|s2|s3|sarjana|kuliah/', $pd)) return 1;
        if (preg_match('/sma|smk|sederajat|paket c|ma/', $pd)) return 2;
        return 3;
    }

    public function getSkorJumlahKeluargaAttribute(): int
    {
        $jml = (int) ($this->jumlah_anggota_keluarga ?? 4);
        if ($jml > 4) return 3;
        if ($jml >= 3) return 2;
        return 1;
    }

    public function getSkorDindingAttribute(): int
    {
        $d = strtolower($this->jenis_dinding ?? '');
        return (str_contains($d, 'tembok') || str_contains($d, 'permanen')) ? 1 : ((str_contains($d, 'campur') || str_contains($d, 'semi')) ? 2 : 3);
    }

    public function getSkorLantaiAttribute(): int
    {
        $l = strtolower($this->jenis_lantai ?? '');
        return (str_contains($l, 'keramik') || str_contains($l, 'ubin') || str_contains($l, 'granit')) ? 1 : ((str_contains($l, 'semen') || str_contains($l, 'plester')) ? 2 : 3);
    }

    public function getSkorFisikBangunanAttribute(): int
    {
        return (int) ceil(($this->skor_dinding + $this->skor_lantai) / 2);
    }

    public function getSkorSanitasiAttribute(): int
    {
        $sn = strtolower($this->fasilitas_sanitasi ?? '');
        if (str_contains($sn, 'tidak') || str_contains($sn, 'numpang') || str_contains($sn, 'sungai') || str_contains($sn, 'kebun')) return 3;
        if (str_contains($sn, 'pribadi') || str_contains($sn, 'leher angsa') || str_contains($sn, 'sehat')) return 1;
        return 2;
    }

    public function getSkorSumberAirAttribute(): int
    {
        $air = strtolower($this->sumber_air ?? '');
        if (str_contains($air, 'pdam') || str_contains($air, 'perpipaan')) return 1;
        if (str_contains($air, 'bor') || str_contains($air, 'galon') || str_contains($air, 'hujan')) return 2;
        return 3;
    }

    public function getSkorPendapatanTambahanAttribute(): int
    {
        $pt = strtolower($this->pendapatan_tambahan ?? '');
        if (str_contains($pt, 'tidak ada')) return 3;
        if (str_contains($pt, 'bantuan') || str_contains($pt, 'blt') || str_contains($pt, 'pkh')) return 2;
        return 1;
    }

    public function getSkorKepemilikanLahanAttribute(): int
    {
        $lh = strtolower($this->kepemilikan_lahan ?? '');
        if (str_contains($lh, 'sendiri') || str_contains($lh, 'warisan') || str_contains($lh, 'shm')) return 1;
        if (str_contains($lh, 'adat') || str_contains($lh, 'pinjam') || str_contains($lh, 'sewa') || str_contains($lh, 'garap') || str_contains($lh, 'numpang')) return 2;
        return 3;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNamaPenginputAttribute(): string
    {
        if ($this->relationLoaded('creator') && $this->creator) {
            if ($this->creator->username === 'operator1') {
                return 'Harpiyansa (Operator Surveyor 1)';
            }
            if ($this->creator->username === 'operator2') {
                return 'Kusnaeni (Operator Surveyor 2)';
            }
            if ($this->creator->role === 'admin') {
                return $this->creator->name . ' (Admin)';
            }
            return $this->creator->name . ' (' . ucfirst($this->creator->role) . ')';
        }
        return 'Surveyor SOTA (Data Awal Lapangan)';
    }

    public function getKlasifikasiLabelAttribute(): string
    {
        $k = strtolower($this->klasifikasi ?? 'sedang');
        return 'Kerentanan ' . ucfirst($k);
    }

    public function getRtRwAttribute(): string
    {
        if ($this->rt && $this->rw) {
            return trim($this->rt) . ' / ' . trim($this->rw);
        }
        if ($this->alamat) {
            return trim($this->alamat);
        }
        return 'Kampung Sota';
    }

    public function getSkorSviAttribute()
    {
        return $this->total_skor;
    }

    public function logPembaruan()
    {
        return $this->hasMany(LogPembaruan::class, 'data_penduduk_id');
    }

    /**
     * Hitung rincian 9 indikator SVI beserta skor dan jawaban masing-masing
     */
    public function getSviItemsAttribute(): array
    {
        return [
            ['no' => 1, 'label' => 'Pendapatan', 'jawaban' => $this->pendapatan ?? '-', 'skor' => $this->skor_pendapatan],
            ['no' => 2, 'label' => 'Pekerjaan', 'jawaban' => $this->pekerjaan ?? '-', 'skor' => $this->skor_pekerjaan],
            ['no' => 3, 'label' => 'Pendidikan', 'jawaban' => $this->pendidikan ?? '-', 'skor' => $this->skor_pendidikan],
            ['no' => 4, 'label' => 'Anggota keluarga', 'jawaban' => ($this->jumlah_anggota_keluarga ?? 4) . ' orang', 'skor' => $this->skor_jumlah_keluarga],
            ['no' => 5, 'label' => 'Fisik bangunan', 'jawaban' => ($this->jenis_dinding ?? '-') . ' + ' . ($this->jenis_lantai ?? '-'), 'skor' => $this->skor_fisik_bangunan],
            ['no' => 6, 'label' => 'Fasilitas sanitasi', 'jawaban' => $this->fasilitas_sanitasi ?? '-', 'skor' => $this->skor_sanitasi],
            ['no' => 7, 'label' => 'Sumber air', 'jawaban' => $this->sumber_air ?? '-', 'skor' => $this->skor_sumber_air],
            ['no' => 8, 'label' => 'Pendapatan tambahan', 'jawaban' => $this->pendapatan_tambahan ?? '-', 'skor' => $this->skor_pendapatan_tambahan],
            ['no' => 9, 'label' => 'Kepemilikan lahan', 'jawaban' => $this->kepemilikan_lahan ?? '-', 'skor' => $this->skor_kepemilikan_lahan],
        ];
    }
}
