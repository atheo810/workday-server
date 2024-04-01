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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index()->unique();
            $table->string('employee_code', 25)->unique();
            $table->string('fullname', 70);
            $table->enum('gender', ['laki-laki', 'perempuan']);
            $table->enum('position', [
                'owner', 'administrator',
                'head-mechanic', 'mechanic',
                'cleaner', 'office-boy', 'security'
            ]);
            $table->string('phone_number', 15)->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();
            $table->timestamp('joined_company_at');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
