<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sapeurs', function (Blueprint $table) {
            $table->string('suffixe', 50)->default('')->change();
            $table->string('no_avs')->default('')->change();
            $table->string('profession')->default('')->change();
            $table->string('employeur')->default('')->change();
            $table->string('lieu_de_travail')->default('')->change();
            $table->string('email')->default('')->change();
            $table->string('iban')->default('')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sapeurs', function (Blueprint $table) {
            $table->string('suffixe', 50)->change();
            $table->string('no_avs')->change();
            $table->string('profession')->change();
            $table->string('employeur')->change();
            $table->string('lieu_de_travail')->change();
            $table->string('email')->change();
            $table->string('iban')->change();
        });
    }
};
