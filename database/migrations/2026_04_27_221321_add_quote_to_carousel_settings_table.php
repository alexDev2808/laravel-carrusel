<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carousel_settings', function (Blueprint $table) {
            $table->text('quote')->nullable()->after('duration');
            $table->string('author')->nullable()->after('quote');
        });
    }

    public function down(): void
    {
        Schema::table('carousel_settings', function (Blueprint $table) {
            $table->dropColumn(['quote', 'author']);
        });
    }
};
