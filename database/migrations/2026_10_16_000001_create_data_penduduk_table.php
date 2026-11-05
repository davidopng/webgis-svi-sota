<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_penduduk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('nama_kk', 100);
            $table->string('status_penduduk', 50)->default('Tetap');
            $table->string('alamat', 255);
            $table->string('jalur', 50); // Jalur A, Jalur B, Poros Trans
            $table->string('rt', 20)->nullable();
            $table->string('rw', 20)->nullable();
            $table->double('latitude');
            $table->double('longitude');
            $table->string('pendapatan', 100);
            $table->string('pekerjaan', 100);
            $table->string('pendidikan', 100);
            $table->integer('jumlah_anggota_keluarga')->default(4);
            $table->string('jenis_dinding', 100);
            $table->string('jenis_lantai', 100);
            $table->string('fasilitas_sanitasi', 100);
            $table->string('sumber_air', 100);
            $table->string('pendapatan_tambahan', 100);
            $table->string('kepemilikan_lahan', 100);
            $table->integer('total_skor')->default(18);
            $table->string('klasifikasi', 50); // rendah, sedang, tinggi
            $table->string('status_verifikasi', 50)->default('terverifikasi'); // menunggu_verifikasi, terverifikasi
            $table->longText('foto_rumah')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_penduduk');
    }
};
