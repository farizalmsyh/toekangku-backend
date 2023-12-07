<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seeker_id');
            $table->unsignedBigInteger('worker_id');
            $table->unsignedBigInteger('canceled_id')->nullable();
            $table->unsignedBigInteger('done_id')->nullable();
            $table->integer('status')->default(0);
            $table->text('profesion');
            $table->float('payment');
            $table->string('payment_type');
            $table->date('start_date');
            $table->text('location_province');
            $table->text('location_city');
            $table->text('location_subdistrict');
            $table->text('location_village');
            $table->text('location_zipcode');
            $table->text('location_detail');
            $table->foreign('seeker_id')->references('id')->on('users');
            $table->foreign('worker_id')->references('id')->on('users');
            $table->foreign('canceled_id')->references('id')->on('users');
            $table->foreign('done_id')->references('id')->on('users');
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
        Schema::dropIfExists('jobs');
    }
}
