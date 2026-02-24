<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watchlist', function (Blueprint $table) {
            $table->string('poster_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('watchlist', function (Blueprint $table) {
            $table->dropColumn('poster_path');
        });
    }
};