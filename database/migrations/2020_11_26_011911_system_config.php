<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SystemConfig extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('system_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key', 50);
            $table->string('value', 50);
            $table->timestamps();
        });

        //Insertamos los valores por defecto.
        DB::table('system_configs')->insert([
            ['key' => 'user_pass_expire_days', 'value' => '180'],
            ['key' => 'employe_remember_mail_days', 'value' => '7'],
            ['key' => 'default_employe_expiration_days', 'value' => '180'],
            ['key' => 'employe_cancel_mail_days', 'value' => '2'],
            ['key' => 'password_remember_mail_days', 'value' => '2'],
            ['key' => 'user_pass_validate_days', 'value' => '2']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_configs');
    }
}
