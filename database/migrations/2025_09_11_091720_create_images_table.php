<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('public_id')->unique();
            $table->string('url');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('format')->nullable();
            $table->integer('size')->nullable();
            $table->string('folder')->default('uploads');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
};
