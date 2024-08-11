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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->boolean('photograph');
            $table->boolean('food');
            $table->boolean('music');
            $table->decimal('price',8,2,true);
            $table->time('start_time');
            $table->time('end_time');
            $table->json('available_day');
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('service_type');
            $table->enum('status',['0','1','2'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
