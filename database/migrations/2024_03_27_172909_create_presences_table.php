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
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index()->unique();
            $table->time('clock_in');
            $table->time('clock_out')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('device', ['web', 'android', 'ios'])->default('web');
            $table->string('latitude_in');
            $table->string('longitude_in');
            $table->string('latitude_out')->nullable();
            $table->string('longitude_out')->nullable();
            $table->integer('distance_in');
            $table->integer('distance_out')->nullable();
            $table->string('photo_in')->nullable();
            $table->string('photo_out')->nullable();
            $table->date('presence_date');
            // $table->integer('presence_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
