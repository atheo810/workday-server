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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')->after('uuid')->references('id')->on('employees');
        });

        Schema::table('presences', function (Blueprint $table) {
            $table->foreignId('employee_id')->after('id')->references('id')->on('employees');
        });

        Schema::table('time_offs', function (Blueprint $table) {
            $table->foreignId('employee_id')->after('uuid')->references('id')->on('employees');
            $table->foreignId('accepted_by')->after('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
