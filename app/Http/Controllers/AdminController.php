<?php

namespace App\Http\Controllers;

use App\Models\DataPenduduk;
use App\Services\SviAnalysisService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Tampilkan Dashboard Admin
     */
    public function dashboard()
    {
        $totalWarga = DataPenduduk::count();
        $menungguCount = DataPenduduk::where('status_verifikasi', 'menunggu_verifikasi')->count();
        $terverifikasiCount = DataPenduduk::where('status_verifikasi', 'terverifikasi')->count();
        $tinggiCount = DataPenduduk::where('status_verifikasi', 'terverifikasi')->where('klasifikasi', 'tinggi')->count();
        $sedangCount = DataPenduduk::where('status_verifikasi', 'terverifikasi')->where('klasifikasi', 'sedang')->count();
        $rendahCount = DataPenduduk::where('status_verifikasi', 'terverifikasi')->where('klasifikasi', 'rendah')->count();

        $antreanTerbaru = DataPenduduk::with('creator')
            ->where('status_verifikasi', 'menunggu_verifikasi')
            ->latest()
            ->take(5)
            ->get();

        $analisisAI = SviAnalysisService::generateExecutiveSummary();
        $statusSinkronisasi = "Sinkron WIT: " . now("Asia/Jayapura")->translatedFormat("d F Y, H:i") . " WIT";

        return view('admin.dashboard', compact(
            'totalWarga',
            'menungguCount',
            'terverifikasiCount',
            'tinggiCount',
            'sedangCount',
            'rendahCount',
            'antreanTerbaru',
            'analisisAI',
            'statusSinkronisasi'
        ));
    }
}
