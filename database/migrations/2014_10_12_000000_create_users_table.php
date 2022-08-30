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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('image')->nullable();
            $table->integer('age')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->boolean('blocked')->default(false);
            $table->string('password');
            $table->string('role');
            $table->string('lastvisit');

            
            $table->timestamp('email_verified_at');

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
