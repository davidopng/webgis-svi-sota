<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PimpinanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataPendudukController;
use App\Models\LogPembaruan;
use App\Models\DataPenduduk;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes — SOTA-GIS (PRD Bagian 5)
|--------------------------------------------------------------------------
| Semua rute casing frontend untuk 4 peran pengguna:
| - Publik
| - Operator
| - Admin
| - Pimpinan (Kepala Kampung)
*/

// ==============================
// RUTE PUBLIK
// ==============================
$getInfografisData = function () {
    $data = DataPenduduk::where('status_verifikasi', 'terverifikasi')->get();

    $jalurPatterns = [
        'Jalur A'     => '/(jalur.*a|jalur\s*a)/i',
        'Jalur B'     => '/(jalur.*b|jalur\s*b)/i',
        'Poros Trans' => '/poros/i',
    ];

    $categorizeJob = function ($job) {
        $j = strtolower((string) $job);
        // 1. Sektor Formal / Bergaji Tetap (Skor 1 SVI)
        if (preg_match('/pns|tni|polri|guru|tetap|wiraswasta|wirausaha|swasta|plbn|stap|karyawan|pendeta|dokter|dosen/i', $j)) {
            return 'formal';
        }
        // 2. Sektor Informal & Pertanian (Skor 2 SVI)
        if (preg_match('/petani|peternak|kebun|tani|pedagang|honorer|kontrak|sopir|supir|angkot|warung|warunf|bengkel/i', $j)) {
            return 'informal';
        }
        // 3. Sektor Harian / Rentan (Skor 3 SVI)
        return 'harian';
    };

    $pekerjaanStats = [];
    $chartData = [
        'labels'   => array_keys($jalurPatterns),
        'informal' => [],
        'formal'   => [],
        'harian'   => [],
    ];

    foreach ($jalurPatterns as $name => $pattern) {
        $wargaJalur = $data->filter(fn($d) => preg_match($pattern, (string) ($d->jalur ?? '')));

        $formal   = $wargaJalur->filter(fn($d) => $categorizeJob($d->pekerjaan) === 'formal')->count();
        $informal = $wargaJalur->filter(fn($d) => $categorizeJob($d->pekerjaan) === 'informal')->count();
        $harian   = $wargaJalur->filter(fn($d) => $categorizeJob($d->pekerjaan) === 'harian')->count();
        $total    = $wargaJalur->count();

        $pekerjaanStats[$name] = [
            'formal'   => $formal,
            'informal' => $informal,
            'harian'   => $harian,
            'total'    => $total,
        ];

        $chartData['informal'][] = $informal;
        $chartData['formal'][]   = $formal;
        $chartData['harian'][]   = $harian;
    }

    return compact('pekerjaanStats', 'chartData');
};

Route::get('/', function () use ($getInfografisData) {
    return view('publik.infografis', $getInfografisData());
})->name('publik.infografis');

Route::get('/peta', function () use ($getInfografisData) {
    return view('publik.infografis', $getInfografisData());
})->name('peta');

Route::get('/tentang', function () {
    return view('publik.tentang');
})->name('publik.tentang');

Route::get('/profil-kampung', function () {
    return view('publik.tentang');
})->name('publik.profil-kampung');


Route::get('/kontak', function () {
    return view('publik.kontak');
})->name('publik.kontak');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// ==============================
// RUTE OPERATOR
// ==============================
Route::prefix('operator')->name('operator.')->middleware(['auth', 'role:operator,admin'])->group(function () {
    Route::get('/', function () {
        $user = Auth::user();
        $isOperator = ($user && $user->role === 'operator');

        $query = DataPenduduk::query();
        if ($isOperator) {
            $query->where('created_by', $user->id);
        }

        $totalData = (clone $query)->count();
        $totalTerverifikasi = (clone $query)->where('status_verifikasi', 'terverifikasi')->count();
        $totalPending = (clone $query)->where('status_verifikasi', 'menunggu_verifikasi')->count();
        $inputTerakhir = (clone $query)->latest()->take(5)->get();
        $statusSinkronisasi = "Sinkron WIT: " . now("Asia/Jayapura")->translatedFormat("d F Y, H:i") . " WIT";
        return view('operator.dashboard', compact('totalData', 'totalTerverifikasi', 'totalPending', 'inputTerakhir', 'statusSinkronisasi'));
    })->name('dashboard');

    Route::get('/data', function () {
        $user = Auth::user();
        $isOperator = ($user && $user->role === 'operator');

        $query = DataPenduduk::query();
        if ($isOperator) {
            $query->where('created_by', $user->id);
        }

        $dataList = $query->latest()->get();
        return view('operator.index', compact('dataList'));
    })->name('data.index');

    Route::get('/data/create', function () {
        return view('operator.form');
    })->name('data.create');

    Route::post('/data', [DataPendudukController::class, 'store'])->name('data.store');
    Route::match(['post', 'put'], '/data/{id}', [DataPendudukController::class, 'update'])->name('data.update');

    Route::get('/data/{id}/edit', function ($id) {
        $data = DataPenduduk::find($id);
        return view('operator.form', [
            'id' => $id,
            'data' => $data
        ]);
    })->name('data.edit');

    Route::get('/peta-sebaran', function () {
        return redirect('/operator');
    })->name('peta');
});

// ==============================
// RUTE ADMIN
// ==============================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/verifikasi', function () {
        $antreanList = DataPenduduk::with('creator')->latest()->get();
        $verifikasiCount = DataPenduduk::where('status_verifikasi', 'menunggu_verifikasi')->count();
        return view('admin.verifikasi', compact('antreanList', 'verifikasiCount'));
    })->name('verifikasi');

    Route::get('/verifikasi/{id}', function ($id) {
        return redirect('/admin/verifikasi?id=' . $id);
    })->name('verifikasi.detail');

    Route::get('/data', function () {
        $dataWarga = DataPenduduk::with('creator')->latest()->get();
        $verifikasiCount = DataPenduduk::where('status_verifikasi', 'menunggu_verifikasi')->count();
        return view('admin.data', compact('dataWarga', 'verifikasiCount'));
    })->name('data.index');

    Route::get('/peta-sebaran', function () {
        $wargaPoints = DataPenduduk::where('status_verifikasi', 'terverifikasi')->get();
        $verifikasiCount = DataPenduduk::where('status_verifikasi', 'menunggu_verifikasi')->count();
        return view('shared.peta-sebaran', [
            'role' => 'admin',
            'userName' => Auth::user()?->name ?? 'Jhoni Arafat',
            'verifikasiCount' => $verifikasiCount,
            'wargaPoints' => $wargaPoints
        ]);
    })->name('peta');

    Route::get('/data/create', function () {
        $verifikasiCount = DataPenduduk::where('status_verifikasi', 'menunggu_verifikasi')->count();
        return view('operator.form', [
            'role' => 'admin',
            'userName' => Auth::user()?->name ?? 'Jhoni Arafat',
            'verifikasiCount' => $verifikasiCount
        ]);
    })->name('data.create');

    Route::post('/data', [DataPendudukController::class, 'store'])->name('data.store');
    Route::match(['post', 'put'], '/data/{id}', [DataPendudukController::class, 'update'])->name('data.update');
    Route::post('/data/{id}/verifikasi', [DataPendudukController::class, 'verifikasi'])->name('data.verifikasi');
    Route::post('/verifikasi/{id}', [DataPendudukController::class, 'verifikasi'])->name('verifikasi.action');
    Route::delete('/data/{id}', [DataPendudukController::class, 'destroy'])->name('data.destroy');
    Route::post('/data/{id}/delete', [DataPendudukController::class, 'destroy'])->name('data.destroy.post');
    Route::get('/data/{id}', [DataPendudukController::class, 'show'])->name('data.show');

    Route::get('/data/{id}/edit', function ($id) {
        $data = DataPenduduk::find($id);
        $verifikasiCount = DataPenduduk::where('status_verifikasi', 'menunggu_verifikasi')->count();
        return view('operator.form', [
            'role' => 'admin',
            'userName' => Auth::user()?->name ?? 'Jhoni Arafat',
            'id' => $id,
            'data' => $data,
            'verifikasiCount' => $verifikasiCount
        ]);
    })->name('data.edit');

    Route::get('/log', function () {
        $logs = LogPembaruan::with(['user', 'dataPenduduk'])->latest()->get()->map(function ($item) {
            $sebelum = is_string($item->data_sebelum) ? json_decode($item->data_sebelum, true) : $item->data_sebelum;
            $sesudah = is_string($item->data_sesudah) ? json_decode($item->data_sesudah, true) : $item->data_sesudah;
            $perubahan = [];
            if ($sebelum && $sesudah && is_array($sebelum) && is_array($sesudah)) {
                foreach ($sesudah as $k => $v) {
                    if (!isset($sebelum[$k]) || $sebelum[$k] != $v) {
                        $perubahan[] = $k;
                    }
                }
            }
                $aksiMap = [
                    'create' => 'tambah',
                    'update' => 'ubah',
                    'delete' => 'hapus',
                    'verifikasi' => 'verifikasi',
                ];
                return [
                    'id' => $item->id,
                    'waktu' => $item->created_at ? $item->created_at->format('d/m/Y H:i') . ' WIT' : '-',
                    'tanggal' => $item->created_at ? $item->created_at->format('Y-m-d') : '',
                    'pengguna' => $item->user?->name ?? 'Sistem',
                    'peran' => $item->user?->role === 'admin' ? 'Admin' : ($item->user?->role === 'kepala_kampung' ? 'Kepala Kampung' : 'Operator'),
                    'aksi' => $aksiMap[$item->aksi] ?? $item->aksi,
                    'dataTerkait' => $item->dataPenduduk?->nama_kk ?? 'Data Warga #' . $item->data_penduduk_id,
                    'jalur' => $item->dataPenduduk?->jalur ?? 'Kampung Sota',
                    'keterangan' => $item->keterangan,
                    'sebelum' => $sebelum,
                    'sesudah' => $sesudah,
                    'perubahan' => $perubahan,
                ];
        });
        return view('admin.log', compact('logs'));
    })->name('log');
});

// ==============================
// RUTE KEPALA KAMPUNG (PIMPINAN)
// ==============================
Route::prefix('pimpinan')->name('pimpinan.')->middleware(['auth', 'role:kepala_kampung'])->group(function () {
    Route::get('/', [PimpinanController::class, 'dashboard'])->name('dashboard');
    Route::get('/peta-sebaran', [PimpinanController::class, 'peta'])->name('peta');
    Route::get('/laporan', [PimpinanController::class, 'laporan'])->name('laporan');
});
