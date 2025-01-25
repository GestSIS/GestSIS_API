<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Infrastructure\Models\ConvocationParam;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::drop('convocation_params');
        Schema::create('convocation_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('titre')->default('Convocation');
            $table->string('texte_debut')->default('');
            $table->string('texte_fin')->default('');
            $table->string('texte_pour_info')->default('Pour information');

            $table->boolean('affichage_pour_info')->default(true);
            $table->boolean('affichage_duree')->default(true);
        });

        (new ConvocationParam())->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('convocation_params');
    }
};
