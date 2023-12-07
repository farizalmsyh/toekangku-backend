<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('banned')->default(0);
            $table->integer('closed')->default(0);
            $table->text('banned_reason')->nullable();
            $table->text('type');
            $table->text('title');
            $table->text('description');
            $table->float('payment')->nullable();
            $table->string('payment_type')->nullable();
            $table->date('job_start_date')->nullable();
            $table->text('location_province')->nullable();
            $table->text('location_city')->nullable();
            $table->text('location_subdistrict')->nullable();
            $table->text('location_village')->nullable();
            $table->text('location_zipcode')->nullable();
            $table->text('location_detail')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('threads');
    }
}
