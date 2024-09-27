<?php

use App\Infrastructure\Models\Sms;
use App\Infrastructure\Models\SmsNumero;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms', function (Blueprint $table) {
            $table->longText('message')->change();
        });
        Schema::create('sms_numeros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('numero');

            $table->unsignedBigInteger('sapeur_id')->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->unsignedBigInteger('sms_id');
            $table->foreign('sms_id')->references('id')->on('sms');
        });

        // Migrate date
        $sms = Sms::all();
        foreach ($sms as $smsEntry) {
            $nums = explode(";", $smsEntry->numeros);
            SmsNumero::insert(array_map(fn($num) => (['sms_id' => $smsEntry->id, 'sapeur_id' => null, 'numero' => $num]), $nums));
        }

        Schema::table('sms', function (Blueprint $table) {
            $table->dropColumn('numeros');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_numeros');
        Schema::table('sms', function (Blueprint $table) {
            $table->longText('numeros');
        });
    }
};
