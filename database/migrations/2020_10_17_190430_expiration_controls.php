<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpirationControls extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('expiration_controls', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employe_id')->unsigned()->unique();
            $table->foreign('employe_id')->references('id')->on('employes')->onDelete('restrict')->onUpdate('cascade');
            $table->dateTime('date', 0);
            $table->integer('employe_status_id')->unsigned();
            $table->foreign('employe_status_id')->references('id')->on('employe_status')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('ExpirationControls');
    }
}
