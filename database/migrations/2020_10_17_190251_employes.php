<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Employes extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('location_id')->unsigned();
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('position_type_id')->unsigned();
            $table->foreign('position_type_id')->references('id')->on('position_types')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('call_order')->unsigned();
            $table->string('caller_password', 150);
            $table->string('personal_password', 150);
            $table->integer('panel_order')->unsigned();
            $table->string('panel_code', 75);
            $table->integer('biometric_id')->unsigned()->nullable();
            $table->timestamps();
            $table->dateTime('cancel_request', 0)->nullable();
            $table->dateTime('cancel_at', 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employes');
    }
}
