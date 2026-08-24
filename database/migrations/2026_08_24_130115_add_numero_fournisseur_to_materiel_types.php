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
        Schema::table('materiel_types', function (Blueprint $table) {
            $table->string('numero_fournisseur')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materiel_types', function (Blueprint $table) {
            $table->dropColumn('numero_fournisseur');
        });
    }
};
