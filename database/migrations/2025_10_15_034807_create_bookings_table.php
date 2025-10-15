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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('room_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('purpose');
            $table->integer('participants')->unsigned();
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'completed'
            ])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern', 50)->nullable();
            $table->foreignId('approved_by')->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['room_id', 'booking_date', 'status']);
            $table->index('user_id');
            $table->index('status');
            $table->index(['booking_date', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
