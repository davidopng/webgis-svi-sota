<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/', function () {
    return view('publik.peta');
})->name('publik.peta');

Route::get('/peta', function () {
    return view('publik.peta');
})->name('peta');

Route::get('/tentang', function () {
    return view('publik.tentang');
})->name('publik.tentang');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    $username = request('username');
    if ($username === 'admin') {
        return redirect('/admin');
    } elseif ($username === 'pimpinan' || $username === 'kepala') {
        return redirect('/pimpinan');
    }
    return redirect('/operator');
});

// ==============================
// RUTE OPERATOR
// ==============================
Route::prefix('operator')->name('operator.')->group(function () {
    Route::get('/', function () {
        return view('operator.dashboard');
    })->name('dashboard');

    Route::get('/data', function () {
        return view('operator.index');
    })->name('data.index');

    Route::get('/data/create', function () {
        return view('operator.form');
    })->name('data.create');

    Route::post('/data', function () {
        return redirect('/operator/data');
    })->name('data.store');

    Route::get('/data/{id}/edit', function ($id) {
        return view('operator.form', [
            'id' => $id,
            'data' => [
                'nama_kk' => 'Yohanis Kaize',
                'jalur' => 'Jalur B',
                'rt' => 'RT 03',
                'rw' => 'RW 01',
                'no_rumah' => '12',
            ]
        ]);
    })->name('data.edit');
});

// ==============================
// RUTE ADMIN
// ==============================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/verifikasi', function () {
        return view('admin.verifikasi');
    })->name('verifikasi');

    Route::get('/data', function () {
        return view('admin.data');
    })->name('data.index');

    Route::get('/data/create', function () {
        return view('operator.form', ['role' => 'admin', 'userName' => 'Administrator']);
    })->name('data.create');

    Route::get('/data/{id}/edit', function ($id) {
        return view('operator.form', [
            'role' => 'admin',
            'userName' => 'Administrator',
            'id' => $id,
            'data' => [
                'nama_kk' => 'Yohanis Kaize',
                'jalur' => 'Jalur B',
                'rt' => 'RT 03',
                'rw' => 'RW 01',
                'no_rumah' => '12',
            ]
        ]);
    })->name('data.edit');

    Route::get('/log', function () {
        return view('admin.log');
    })->name('log');
});

// ==============================
// RUTE KEPALA KAMPUNG (PIMPINAN)
// ==============================
Route::prefix('pimpinan')->name('pimpinan.')->group(function () {
    Route::get('/', function () {
        return view('pimpinan.dashboard');
    })->name('dashboard');

    Route::get('/laporan', function () {
        return view('pimpinan.laporan');
    })->name('laporan');
});
