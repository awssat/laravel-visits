<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVisitsArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits_archive', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('visitable_type');
            $table->unsignedBigInteger('visitable_id');
            $table->string('tag');
            $table->date('date');
            $table->unsignedBigInteger('count');
            $table->timestamps();

            $table->index(['visitable_type', 'visitable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits_archive');
    }
}
