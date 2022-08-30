<?php

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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_server');
            $table->bigInteger('id_cook')->nullable();
            $table->integer('table');
            $table->string('label')->nullable();
            $table->string('state')->default('en attente');
            $table->float('total');
            $table->string('date')->nullable();
            $table->string('order_time')->nullable();
            $table->string('prepared_time')->nullable();
            $table->string('delivered_time')->nullable();
            $table->string('payed_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('commandes');
    }
};
