<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sesuai Dokumen 3.8 Desain Database: Tabel users
     */
    public function up(): void
    {
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();                                                   // 1. id: bigint unsigned PK
            $table->string('name', 100);                                    // 2. name: varchar(100)
            $table->string('username', 50)->unique();                       // 3. username: varchar(50) UK
            $table->string('password', 255);                                // 4. password: varchar(255)
            $table->enum('role', ['operator', 'admin', 'kepala_kampung']);  // 5. role: enum
            $table->rememberToken();                                        // 6. remember_token: varchar(100) nullable
            $table->timestamps();                                           // 7 & 8. created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
