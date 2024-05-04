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
        Schema::create('time_offs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index()->unique();
            $table->enum('type', [
                'annual', 'sick_without_docs',
                'sick_with_docs', 'holiday', 'unpaid',
                'special_permit'
            ]);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->text('description');
            $table->string('document')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('total_days');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_offs');
    }
};
