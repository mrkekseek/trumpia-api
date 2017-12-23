<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceiversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receivers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('message_id')->unsigned()->default(0);
            $table->string('request_id');
            $table->string('phone');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('link');
            $table->text('text');
            $table->string('company');
            $table->text('attachment');
            $table->tinyInteger('landline')->unsigned()->default(0);
            $table->tinyInteger('finish')->unsigned()->default(0);
            $table->tinyInteger('success')->unsigned()->default(0);
            $table->text('message');
            $table->timestamp('sent_at');
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
        Schema::dropIfExists('receivers');
    }
}
