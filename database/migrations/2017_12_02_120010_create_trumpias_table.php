<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrumpiasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trumpia', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('token_id')->unsigned()->default(0);
            $table->string('request_id')->default('');
            $table->string('type')->default('');
            $table->string('message')->default('');
            $table->json('data');
            $table->json('response');
            $table->json('push');
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
        Schema::dropIfExists('trumpia');
    }
}
