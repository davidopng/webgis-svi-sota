<?php

namespace App\Http\Controllers;

use App\Models\DataPenduduk;
use App\Services\SviAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PimpinanController extends Controller
{
    /**
     * Tampilkan Dashboard Eksekutif Kepala Kampung
     */
    public function dashboard()
    {
        $totalKK = DataPenduduk::where('status_verifikasi', 'terverifikasi')->count();
        $rendahCount = DataPenduduk::where('status_verifikasi', 'terverifikasi')->where('klasifikasi', 'rendah')->count();
        $sedangCount = DataPenduduk::where('status_verifikasi', 'terverifikasi')->where('klasifikasi', 'sedang')->count();
        $tinggiCount = DataPenduduk::where('status_verifikasi', 'terverifikasi')->where('klasifikasi', 'tinggi')->count();

        $rendahPct = $totalKK > 0 ? round(($rendahCount / $totalKK) * 100, 1) : 0;
        $sedangPct = $totalKK > 0 ? round(($sedangCount / $totalKK) * 100, 1) : 0;
        $tinggiPct = $totalKK > 0 ? round(($tinggiCount / $totalKK) * 100, 1) : 0;

        // Rincian Statistik Sebaran Per Wilayah (Jalur A, Jalur B, Poros Trans)
        $allVerified = DataPenduduk::where('status_verifikasi', 'terverifikasi')->get();

        $wilayahStats = [];
        $wilayahPatterns = [
            'Jalur A'     => '/(jalur.*a|jalur\s*a)/i',
            'Jalur B'     => '/(jalur.*b|jalur\s*b)/i',
            'Poros Trans' => '/poros/i',
        ];

        foreach ($wilayahPatterns as $wName => $pattern) {
            $wGroup = $allVerified->filter(fn($d) => preg_match($pattern, (string) ($d->jalur ?? '')));
            $wilayahStats[$wName] = [
                'rendah' => $wGroup->where('klasifikasi', 'rendah')->count(),
                'sedang' => $wGroup->where('klasifikasi', 'sedang')->count(),
                'tinggi' => $wGroup->where('klasifikasi', 'tinggi')->count(),
                'total'  => $wGroup->count(),
            ];
        }

        // Sinkronisasi Waktu & Status Pembaruan Data
        $lastVerified = DataPenduduk::where('status_verifikasi', 'terverifikasi')
            ->latest('updated_at')
            ->first();

        if ($lastVerified && ($lastVerified->updated_at || $lastVerified->created_at)) {
            $lastTime = $lastVerified->updated_at ?? $lastVerified->created_at;
            $tanggalVerifikasiText = Carbon::parse($lastTime)->setTimezone('Asia/Jayapura')->translatedFormat('d F Y');
        } else {
            $tanggalVerifikasiText = now('Asia/Jayapura')->translatedFormat('d F Y');
        }

        $statusSinkronisasi = "Sinkron WIT: " . now("Asia/Jayapura")->translatedFormat("d F Y, H:i") . " WIT";
        $waktuSekarangWIT = $statusSinkronisasi;

        $wargaPrioritas = DataPenduduk::where('status_verifikasi', 'terverifikasi')
            ->where('klasifikasi', 'tinggi')
            ->orderByDesc('total_skor')
            ->take(5)
            ->get();

        $wargaPoints = DataPenduduk::where('status_verifikasi', 'terverifikasi')->get();

        $analisisAI = SviAnalysisService::generateExecutiveSummary();

        return view('pimpinan.dashboard', compact(
            'totalKK',
            'rendahCount',
            'sedangCount',
            'tinggiCount',
            'rendahPct',
            'sedangPct',
            'tinggiPct',
            'wilayahStats',
            'tanggalVerifikasiText',
            'statusSinkronisasi',
            'waktuSekarangWIT',
            'wargaPrioritas',
            'wargaPoints',
            'analisisAI'
        ));
    }

    /**
     * Tampilkan Peta Sebaran Khusus Pimpinan
     */
    public function peta()
    {
        $wargaPoints = DataPenduduk::where('status_verifikasi', 'terverifikasi')->get();

        return view('shared.peta-sebaran', [
            'role' => 'kepala_kampung',
            'userName' => Auth::user()?->name ?? 'Conrada Murnan',
            'wargaPoints' => $wargaPoints
        ]);
    }

    /**
     * Tampilkan Panel Laporan Rekapitulasi
     */
    public function laporan()
    {
        $dataWarga = DataPenduduk::where('status_verifikasi', 'terverifikasi')
            ->orderByDesc('total_skor')
            ->get();

        $tanggalLaporan = now('Asia/Jayapura')->translatedFormat('d F Y');
        $waktuLaporanWIT = now('Asia/Jayapura')->translatedFormat('d F Y, H:i') . ' WIT';

        return view('pimpinan.laporan', compact('dataWarga', 'tanggalLaporan', 'waktuLaporanWIT'));
    }

    /**
     * Tampilkan Halaman Penjelasan Metode SVI
     */
    public function metodeSvi()
    {
        $totalKK = DataPenduduk::count();

        return view('pimpinan.metode-svi', [
            'role' => 'kepala_kampung',
            'userName' => 'Kepala Kampung Sota',
            'totalKK' => $totalKK
        ]);
    }
}
