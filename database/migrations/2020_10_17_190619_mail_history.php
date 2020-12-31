<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MailHistory extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('mail_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employe_id')->unsigned();
            $table->foreign('employe_id')->references('id')->on('employes')->onDelete('restrict')->onUpdate('cascade');
            $table->dateTime('date', 0);
            $table->integer('mail_type_id')->unsigned();
            $table->foreign('mail_type_id')->references('id')->on('mail_types')->onDelete('restrict')->onUpdate('cascade');
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
        Schema::dropIfExists('MailHistorys');
    }
}
