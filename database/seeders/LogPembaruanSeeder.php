<?php

namespace Database\Seeders;

use App\Models\DataPenduduk;
use App\Models\LogPembaruan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LogPembaruanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('log_pembaruan')->truncate();
        Schema::enableForeignKeyConstraints();

        $admin = User::where('role', 'admin')->first() ?? User::where('username', 'admin')->first();
        $op1 = User::where('username', 'operator')->first();
        $op2 = User::where('username', 'operator2')->first();

        $adminId = $admin ? $admin->id : 1;
        $op1Id = $op1 ? $op1->id : 2;
        $op2Id = $op2 ? $op2->id : 3;

        $pendudukList = DataPenduduk::take(30)->get();

        foreach ($pendudukList as $idx => $item) {
            $operatorId = ($item->jalur === 'Jalur B') ? $op2Id : $op1Id;
            $waktuInput = $item->created_at ? $item->created_at->copy()->subHours(rand(1, 24)) : now()->subDays(rand(1, 7));

            // Log 1: Create Data (Perekaman Baru oleh Surveyor)
            LogPembaruan::create([
                'data_penduduk_id' => $item->id,
                'user_id' => $operatorId,
                'aksi' => 'create',
                'keterangan' => 'Penginputan data survei rumah tangga baru di ' . $item->jalur,
                'data_sebelum' => null,
                'data_sesudah' => [
                    'nama_kk' => $item->nama_kk,
                    'jalur' => $item->jalur,
                    'status_penduduk' => $item->status_penduduk ?? 'Tetap',
                    'total_skor' => $item->total_skor,
                    'klasifikasi' => $item->klasifikasi,
                    'pekerjaan' => $item->pekerjaan,
                    'pendapatan' => $item->pendapatan,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                ],
                'created_at' => $waktuInput,
            ]);

            // Log 2: Verifikasi Lapangan oleh Admin jika data terverifikasi
            if ($item->status_verifikasi === 'terverifikasi') {
                $waktuVerif = $waktuInput->copy()->addHours(rand(2, 8));
                LogPembaruan::create([
                    'data_penduduk_id' => $item->id,
                    'user_id' => $adminId,
                    'aksi' => 'verifikasi',
                    'keterangan' => 'Pemeriksaan kesesuaian fisik bangunan dengan foto rumah dan validasi koordinat GPS.',
                    'data_sebelum' => [
                        'status_verifikasi' => 'menunggu_verifikasi',
                    ],
                    'data_sesudah' => [
                        'status_verifikasi' => 'terverifikasi',
                        'diverifikasi_oleh' => 'Jhoni Arafat',
                        'catatan' => 'Sesuai kondisi faktual lapangan dan foto tampak depan.',
                    ],
                    'created_at' => $waktuVerif,
                ]);
            }

            // Log 3: Beberapa data ada pembaruan koordinat / perbaikan data (Aksi update)
            if ($idx % 5 === 0) {
                $waktuUpdate = $waktuInput->copy()->addMinutes(45);
                LogPembaruan::create([
                    'data_penduduk_id' => $item->id,
                    'user_id' => $operatorId,
                    'aksi' => 'update',
                    'keterangan' => 'Koreksi penyesuaian titik penanda koordinat rumah agar tepat di atap bangunan.',
                    'data_sebelum' => [
                        'latitude' => round($item->latitude - 0.00015, 6),
                        'longitude' => round($item->longitude - 0.00012, 6),
                    ],
                    'data_sesudah' => [
                        'latitude' => $item->latitude,
                        'longitude' => $item->longitude,
                    ],
                    'created_at' => $waktuUpdate,
                ]);
            }
        }
    }
}
