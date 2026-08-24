<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('materiel_types', function (Blueprint $table) {
            $table->boolean('a_batterie')->default(false);
        });

        DB::table('materiel_types')
            ->where('type', 2)
            ->update(['a_batterie' => true, 'type' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('materiel_types')
            ->where('a_batterie', true)
            ->update(['type' => 2]);

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->dropColumn('a_batterie');
        });
    }
};
