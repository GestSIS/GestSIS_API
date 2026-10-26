<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une fiche de travail peut être liée à un exercice ou à une intervention
 * (au plus un), selon la configuration de son type.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travail_types', function (Blueprint $table) {
            $table->boolean('lien_exercice')->default(false)->comment('Liable à un exercice');
            $table->boolean('lien_intervention')->default(false)->comment('Liable à une intervention');
            $table->boolean('lien_obligatoire')->default(false)->comment('Doit être lié à un événement autorisé');
        });

        Schema::table('travaux', function (Blueprint $table) {
            $table->foreignId('exercice_id')->nullable()->default(null)->constrained();
            $table->foreignId('intervention_id')->nullable()->default(null)->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('travaux', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exercice_id');
            $table->dropConstrainedForeignId('intervention_id');
        });

        Schema::table('travail_types', function (Blueprint $table) {
            $table->dropColumn(['lien_exercice', 'lien_intervention', 'lien_obligatoire']);
        });
    }
};
