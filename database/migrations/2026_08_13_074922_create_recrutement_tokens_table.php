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
        Schema::create('recrutement_tokens', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('token', 64)->unique();
            $table->timestamp('expire_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recrutement_tokens');
    }
};
