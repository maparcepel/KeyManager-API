<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmployeHistory extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('employe_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('location_id')->unsigned();
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('employe_id')->unsigned();
            $table->foreign('employe_id')->references('id')->on('employes')->onDelete('restrict')->onUpdate('cascade');
            $table->string('caller_password', 150);
            $table->string('personal_password', 150);
            $table->integer('panel_order')->unsigned();
            $table->string('panel_code', 75);
            $table->integer('biometric_id')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('EmployeHistory');
    }
}
