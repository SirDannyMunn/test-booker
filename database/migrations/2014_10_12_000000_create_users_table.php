<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('name');
            $table->string('test_date');
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->enum('contact_preference', ['sms', 'email']);
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('tier', ['free', 'paid', 'premium']);
            $table->boolean('booked')->default(false);
            $table->string('location');
            $table->string('password');
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
