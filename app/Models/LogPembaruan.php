<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPembaruan extends Model
{
    use HasFactory;

    protected $table = 'log_pembaruan';

    protected $guarded = ['id'];

    protected $casts = [
        'data_sebelum' => 'array',
        'data_sesudah' => 'array',
        'created_at' => 'datetime',
    ];

    public function dataPenduduk()
    {
        return $this->belongsTo(DataPenduduk::class, 'data_penduduk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Catat aktivitas audit trail ke dalam database.
     */
    public static function catat($dataPendudukId, $userId, $aksi, $keterangan, $dataSebelum = null, $dataSesudah = null): self
    {
        return self::create([
            'data_penduduk_id' => $dataPendudukId,
            'user_id' => $userId,
            'aksi' => $aksi,
            'keterangan' => $keterangan,
            'data_sebelum' => is_array($dataSebelum) ? json_encode($dataSebelum) : $dataSebelum,
            'data_sesudah' => is_array($dataSesudah) ? json_encode($dataSesudah) : $dataSesudah,
            'created_at' => now(),
        ]);
    }
}
