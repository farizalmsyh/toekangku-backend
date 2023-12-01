<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
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
            $table->string('type')->default('Internal');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('picture')->nullable();
            $table->text('nik');
            $table->text('phone');
            $table->string('gender');
            $table->date('birth_date');
            $table->text('address_province');
            $table->text('address_city');
            $table->text('address_subdistrict');
            $table->text('address_village');
            $table->text('address_zipcode');
            $table->text('address_detail')->nullable();
            $table->text('location_province')->nullable();
            $table->text('location_city')->nullable();
            $table->text('location_subdistrict')->nullable();
            $table->text('location_village')->nullable();
            $table->text('profesion')->nullable();
            $table->integer('start_year')->nullable();
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
}
