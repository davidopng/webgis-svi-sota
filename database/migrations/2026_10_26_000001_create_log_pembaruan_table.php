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
        Schema::create('log_pembaruan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_penduduk_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('aksi', ['create', 'update', 'delete', 'verifikasi']);
            $table->text('keterangan');
            $table->longText('data_sebelum')->nullable();
            $table->longText('data_sesudah')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_pembaruan');
    }
};
