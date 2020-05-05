<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePanoramaLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('panorama_link', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('panorama_start_id')->index();
            $table->unsignedInteger('panorama_end_id')->index();
            $table->double('heading')->comment('Besar sudut');

            $table->foreign('panorama_start_id')->references('id')->on('panorama')->cascadeOnDelete();
            $table->foreign('panorama_end_id')->references('id')->on('panorama')->cascadeOnDelete();
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
        Schema::dropIfExists('panorama_link');
    }
}
