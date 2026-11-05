<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPenduduk extends Model
{
    use HasFactory;

    protected $table = 'data_penduduk';

    protected $guarded = ['id'];

    protected $appends = ['klasifikasi_label', 'rt_rw', 'nama_penginput'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNamaPenginputAttribute(): string
    {
        if ($this->creator) {
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
        // 1. Pendapatan
        $p = strtolower($this->pendapatan ?? '');
        $s1 = str_contains($p, 'kurang') ? 3 : (str_contains($p, '2.500.000') ? 1 : 2);

        // 2. Pekerjaan
        $pk = strtolower($this->pekerjaan ?? '');
        if (preg_match('/buruh|serabutan|tidak bekerja|menganggur|irt|ibu rumah/', $pk)) $s2 = 3;
        elseif (preg_match('/pns|tni|polri|tetap|wiraswasta|guru|dosen/', $pk)) $s2 = 1;
        else $s2 = 2;

        // 3. Pendidikan
        $pd = strtolower($this->pendidikan ?? '');
        if (preg_match('/perguruan|diploma|s1|s2|s3|sarjana|kuliah/', $pd)) $s3 = 1;
        elseif (preg_match('/sma|smk|sederajat|paket c|ma/', $pd)) $s3 = 2;
        else $s3 = 3;

        // 4. Anggota keluarga
        $jml = (int) ($this->jumlah_anggota_keluarga ?? 4);
        if ($jml > 4) $s4 = 3;
        elseif ($jml >= 3) $s4 = 2;
        else $s4 = 1;

        // 5. Fisik Bangunan (Dinding & Lantai)
        $d = strtolower($this->jenis_dinding ?? '');
        $s5a = str_contains($d, 'tembok') || str_contains($d, 'permanen') ? 1 : (str_contains($d, 'campur') || str_contains($d, 'semi') ? 2 : 3);
        $l = strtolower($this->jenis_lantai ?? '');
        $s5b = str_contains($l, 'keramik') || str_contains($l, 'ubin') || str_contains($l, 'granit') ? 1 : (str_contains($l, 'semen') || str_contains($l, 'plester') ? 2 : 3);
        $s5 = (int) ceil(($s5a + $s5b) / 2);

        // 6. Sanitasi
        $sn = strtolower($this->fasilitas_sanitasi ?? '');
        if (str_contains($sn, 'tidak') || str_contains($sn, 'numpang') || str_contains($sn, 'sungai') || str_contains($sn, 'kebun')) $s6 = 3;
        elseif (str_contains($sn, 'pribadi') || str_contains($sn, 'leher angsa') || str_contains($sn, 'sehat')) $s6 = 1;
        else $s6 = 2;

        // 7. Air
        $air = strtolower($this->sumber_air ?? '');
        if (str_contains($air, 'pdam') || str_contains($air, 'perpipaan')) $s7 = 1;
        elseif (str_contains($air, 'bor') || str_contains($air, 'galon') || str_contains($air, 'hujan')) $s7 = 2;
        else $s7 = 3;

        // 8. Pendapatan tambahan
        $pt = strtolower($this->pendapatan_tambahan ?? '');
        if (str_contains($pt, 'tidak ada')) $s8 = 3;
        elseif (str_contains($pt, 'bantuan') || str_contains($pt, 'blt') || str_contains($pt, 'pkh')) $s8 = 2;
        else $s8 = 1;

        // 9. Lahan
        $lh = strtolower($this->kepemilikan_lahan ?? '');
        if (str_contains($lh, 'sendiri') || str_contains($lh, 'warisan') || str_contains($lh, 'shm')) $s9 = 1;
        elseif (str_contains($lh, 'adat') || str_contains($lh, 'pinjam') || str_contains($lh, 'sewa') || str_contains($lh, 'garap') || str_contains($lh, 'numpang')) $s9 = 2;
        else $s9 = 3;

        return [
            ['no' => 1, 'label' => 'Pendapatan', 'jawaban' => $this->pendapatan ?? '-', 'skor' => $s1],
            ['no' => 2, 'label' => 'Pekerjaan', 'jawaban' => $this->pekerjaan ?? '-', 'skor' => $s2],
            ['no' => 3, 'label' => 'Pendidikan', 'jawaban' => $this->pendidikan ?? '-', 'skor' => $s3],
            ['no' => 4, 'label' => 'Anggota keluarga', 'jawaban' => $jml . ' orang', 'skor' => $s4],
            ['no' => 5, 'label' => 'Fisik bangunan', 'jawaban' => ($this->jenis_dinding ?? '-') . ' + ' . ($this->jenis_lantai ?? '-'), 'skor' => $s5],
            ['no' => 6, 'label' => 'Fasilitas sanitasi', 'jawaban' => $this->fasilitas_sanitasi ?? '-', 'skor' => $s6],
            ['no' => 7, 'label' => 'Sumber air', 'jawaban' => $this->sumber_air ?? '-', 'skor' => $s7],
            ['no' => 8, 'label' => 'Pendapatan tambahan', 'jawaban' => $this->pendapatan_tambahan ?? '-', 'skor' => $s8],
            ['no' => 9, 'label' => 'Kepemilikan lahan', 'jawaban' => $this->kepemilikan_lahan ?? '-', 'skor' => $s9],
        ];
    }
}
