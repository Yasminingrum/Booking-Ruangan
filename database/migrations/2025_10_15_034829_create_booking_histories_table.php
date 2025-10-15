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
        Schema::create('booking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('changed_by_user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('old_status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'completed'
            ])->nullable();
            $table->enum('new_status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'completed'
            ]);
            $table->text('notes')->nullable();
            $table->timestamp('created_at');

            // Indexes
            $table->index('booking_id');
            $table->index('changed_by_user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_histories');
    }
};
