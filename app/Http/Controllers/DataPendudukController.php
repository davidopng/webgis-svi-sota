<?php

namespace App\Http\Controllers;

use App\Models\DataPenduduk;
use App\Models\LogPembaruan;
use App\Services\SviCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataPendudukController extends Controller
{
    /**
     * Simpan data warga baru ke database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateInput($request);

            $currentUser = Auth::user();
            $statusVerifikasi = ($currentUser && $currentUser->role === 'admin') ? 'terverifikasi' : 'menunggu_verifikasi';

            $data = array_merge($validated, [
                'created_by' => $currentUser?->id,
                'status_verifikasi' => $statusVerifikasi,
            ]);

            $penduduk = DataPenduduk::create($data);

            LogPembaruan::catat(
                $penduduk->id,
                $currentUser?->id ?? 1,
                'create',
                'Penginputan data survei rumah tangga baru: ' . $penduduk->nama_kk . ' di ' . $penduduk->jalur,
                null,
                $penduduk->toArray()
            );

            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil disimpan.',
                    'data' => $penduduk,
                    'redirect' => (($currentUser && $currentUser->role === 'admin') ? route('admin.data.index') : route('operator.data.index')) . '?notif=simpan',
                ]);
            }

            return redirect((($currentUser && $currentUser->role === 'admin') ? route('admin.data.index') : route('operator.data.index')) . '?notif=simpan')
                ->with('success', 'Data berhasil disimpan.');
        } catch (\Throwable $e) {
            \Log::error('Error store data penduduk: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gagal disimpan. Silakan coba lagi.',
                ], 500);
            }
            return back()->withInput()->with('error', 'Data gagal disimpan. Silakan coba lagi.');
        }
    }

    /**
     * Perbarui data warga yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $penduduk = DataPenduduk::findOrFail($id);
            $sebelum = $penduduk->toArray();

            $validated = $this->validateInput($request);
            $currentUser = Auth::user();

            // Jika admin mengubah data terverifikasi, pertahankan status_verifikasi terverifikasi
            if (!isset($validated['status_verifikasi'])) {
                $validated['status_verifikasi'] = $penduduk->status_verifikasi ?? 'terverifikasi';
            }

            $penduduk->update($validated);

            LogPembaruan::catat(
                $penduduk->id,
                $currentUser?->id ?? 1,
                'update',
                'Pembaruan data warga: ' . $penduduk->nama_kk . ' (Jalur: ' . $penduduk->jalur . ')',
                $sebelum,
                $penduduk->fresh()->toArray()
            );

            $redirectUrl = (($currentUser && $currentUser->role === 'admin') ? route('admin.data.index') : route('operator.data.index')) . '?notif=ubah';

            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil diubah.',
                    'data' => $penduduk,
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect($redirectUrl)->with('success', 'Data berhasil diubah.');
        } catch (\Throwable $e) {
            \Log::error('Error update data penduduk: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gagal disimpan. Silakan coba lagi.',
                ], 500);
            }
            return back()->withInput()->with('error', 'Data gagal disimpan. Silakan coba lagi.');
        }
    }

    /**
     * Validasi dan sanitasi input data warga.
     */
    private function validateInput(Request $request): array
    {
        $namaKk = trim($request->input('nama_kk', ''));
        $statusPenduduk = $request->input('status_penduduk', 'Tetap');
        $jalur = $request->input('jalur', 'Poros Trans');
        $rt = $request->input('rt');
        $rw = $request->input('rw');
        $noRumah = $request->input('no_rumah');
        $patokan = $request->input('patokan');

        $alamat = $request->input('alamat');
        if (empty($alamat)) {
            $parts = [];
            if ($rt) $parts[] = $rt;
            if ($rw) $parts[] = $rw;
            if ($noRumah) $parts[] = (str_starts_with($noRumah, 'No.') ? $noRumah : 'No. ' . $noRumah);
            if ($patokan) $parts[] = '(' . $patokan . ')';
            $alamat = !empty($parts) ? implode(' ', $parts) : ($jalur . ' Kampung Sota');
        }

        $latitude = (float) $request->input('latitude', -8.42827);
        $longitude = (float) $request->input('longitude', 141.00373);
        $fotoRumah = $request->input('foto_rumah');

        // Simpan foto dari base64 data URL ke file disk untuk menghemat memori & performa database
        if (!empty($fotoRumah) && str_starts_with($fotoRumah, 'data:image/')) {
            try {
                $uploadDir = public_path('uploads/foto_rumah');
                if (!file_exists($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }

                if (preg_match('/^data:image\/(\w+);base64,/', $fotoRumah, $type)) {
                    $imageType = strtolower($type[1]);
                    if ($imageType === 'jpeg') $imageType = 'jpg';
                    $data = substr($fotoRumah, strpos($fotoRumah, ',') + 1);
                    $decoded = base64_decode($data);

                    if ($decoded !== false) {
                        $fileName = 'foto_' . time() . '_' . uniqid() . '.' . $imageType;
                        file_put_contents($uploadDir . DIRECTORY_SEPARATOR . $fileName, $decoded);
                        $fotoRumah = '/uploads/foto_rumah/' . $fileName;
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal menyimpan foto base64 ke disk: ' . $e->getMessage());
            }
        } elseif ($request->hasFile('foto_rumah')) {
            try {
                $file = $request->file('foto_rumah');
                if ($file->isValid()) {
                    $uploadDir = public_path('uploads/foto_rumah');
                    if (!file_exists($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }
                    $fileName = 'foto_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fileName);
                    $fotoRumah = '/uploads/foto_rumah/' . $fileName;
                }
            } catch (\Throwable $e) {
                \Log::warning('Gagal memproses file upload foto_rumah: ' . $e->getMessage());
            }
        }

        // 9 Variabel SVI
        $pendapatan = $request->input('pendapatan', 'Rp1.000.000 – Rp2.499.999 / bulan');
        $pekerjaan = $request->input('pekerjaan', 'Petani');
        $pendidikan = $request->input('pendidikan', 'SMA / SMK Sederajat');
        $rawAnggota = $request->input('jumlah_anggota_keluarga', $request->input('jumlah_anggota', 4));
        if (is_string($rawAnggota)) {
            preg_match('/\d+/', $rawAnggota, $matches);
            $jumlahAnggota = !empty($matches) ? (int)$matches[0] : 4;
        } else {
            $jumlahAnggota = (int) $rawAnggota;
        }
        if ($jumlahAnggota <= 0) $jumlahAnggota = 4;

        $jenisDinding = $request->input('jenis_dinding', $request->input('dinding', 'Dinding Kayu / Papan'));
        $jenisLantai = $request->input('jenis_lantai', $request->input('lantai', 'Lantai Semen Plester'));
        $fasilitasSanitasi = $request->input('fasilitas_sanitasi', 'Jamban Sehat Pribadi (Leher Angsa)');
        $sumberAir = $request->input('sumber_air', 'Sumur Gali Terlindung');
        $pendapatanTambahan = $request->input('pendapatan_tambahan', 'Tidak ada pendapatan tambahan');
        $kepemilikanLahan = $request->input('kepemilikan_lahan', 'Pinjam Pakai / Numpang Lahan Adat');

        $totalSkor = (int) $request->input('total_skor', $request->input('skor', 18));
        $klasifikasi = $request->input('klasifikasi', 'sedang');

        return [
            'nama_kk' => $namaKk,
            'status_penduduk' => $statusPenduduk,
            'jalur' => $jalur,
            'rt' => $rt,
            'rw' => $rw,
            'alamat' => $alamat,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'foto_rumah' => $fotoRumah,
            'pendapatan' => $pendapatan,
            'pekerjaan' => $pekerjaan,
            'pendidikan' => $pendidikan,
            'jumlah_anggota_keluarga' => $jumlahAnggota,
            'jenis_dinding' => $jenisDinding,
            'jenis_lantai' => $jenisLantai,
            'fasilitas_sanitasi' => $fasilitasSanitasi,
            'sumber_air' => $sumberAir,
            'pendapatan_tambahan' => $pendapatanTambahan,
            'kepemilikan_lahan' => $kepemilikanLahan,
            'total_skor' => $totalSkor,
            'klasifikasi' => strtolower($klasifikasi),
        ];
    }

    /**
     * Verifikasi data warga oleh Admin (PRD 8 & 10).
     */
    public function verifikasi(Request $request, $id)
    {
        try {
            $penduduk = DataPenduduk::findOrFail($id);
            $sebelum = $penduduk->toArray();

            $penduduk->status_verifikasi = 'terverifikasi';
            $penduduk->save();

            $currentUser = Auth::user();
            LogPembaruan::catat(
                $penduduk->id,
                $currentUser?->id ?? 1,
                'verifikasi',
                'Verifikasi data survei warga oleh Admin: ' . $penduduk->nama_kk . ' (Jalur: ' . $penduduk->jalur . ')',
                $sebelum,
                $penduduk->fresh()->toArray()
            );

            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil diverifikasi.',
                    'data' => $penduduk,
                ]);
            }

            return back()->with('success', 'Data berhasil diverifikasi.');
        } catch (\Throwable $e) {
            \Log::error('Error verifikasi data penduduk: ' . $e->getMessage());
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gagal disimpan. Silakan coba lagi.',
                ], 500);
            }
            return back()->with('error', 'Data gagal disimpan. Silakan coba lagi.');
        }
    }

    /**
     * Hapus data warga permanen dari database (PRD 10).
     */
    public function destroy(Request $request, $id)
    {
        try {
            $penduduk = DataPenduduk::findOrFail($id);
            $sebelum = $penduduk->toArray();
            $namaKk = $penduduk->nama_kk;
            $foto = $penduduk->foto_rumah;

            $currentUser = Auth::user();
            LogPembaruan::catat(
                $penduduk->id,
                $currentUser?->id ?? 1,
                'delete',
                'Penghapusan data survei warga: ' . $namaKk,
                $sebelum,
                null
            );

            // Jika foto merupakan file lokal di uploads, hapus berkas fisik
            if (!empty($foto) && str_starts_with($foto, '/uploads/')) {
                $filePath = public_path(ltrim($foto, '/'));
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $penduduk->delete();

            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dihapus.',
                ]);
            }

            return back()->with('success', 'Data berhasil dihapus.');
        } catch (\Throwable $e) {
            \Log::error('Error destroy data penduduk: ' . $e->getMessage());
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data gagal dihapus. Silakan coba lagi.',
                ], 500);
            }
            return back()->with('error', 'Data gagal dihapus. Silakan coba lagi.');
        }
    }

    /**
     * Detail JSON data warga.
     */
    public function show(Request $request, $id)
    {
        $penduduk = DataPenduduk::findOrFail($id);

        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'data' => $penduduk,
            ]);
        }

        return redirect()->route('admin.data.index');
    }
}
