<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            // $table->string('phone');
            $table->dateTime('lastSignInTime')->nullable();
            $table->string('uid')->nullable();
            $table->string('status')->nullable();
            $table->string('FcmToken')->nullable();
            $table->string('password');
            // $table->string('update_code')->nullable();
            $table->string('guard');
            
            //$table->enum('guard',['admin','user'])->default('user');
            $table->boolean('is_online')->default(false);
            $table->unsignedBigInteger('section_id');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->string('profile_image')->nullable();
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
