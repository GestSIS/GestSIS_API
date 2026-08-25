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
        Schema::table('jalons', function (Blueprint $table) {
            $table->unsignedBigInteger('sapeur_id')->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');
            $table->string('sapeur')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jalons', function (Blueprint $table) {
            $table->dropForeign(['sapeur_id']);
            $table->dropColumn(['sapeur_id', 'sapeur']);
        });
    }
};
