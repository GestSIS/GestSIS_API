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
            $table->boolean('est_emplacement')->default(false);
        });

        Schema::table('emplacements', function (Blueprint $table) {
            $table->foreignId('article_id')->nullable()->unique()->constrained('articles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emplacements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('article_id');
        });

        Schema::table('materiel_types', function (Blueprint $table) {
            $table->dropColumn('est_emplacement');
        });
    }
};
