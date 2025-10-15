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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->enum('type', [
                'booking_created',
                'booking_approved',
                'booking_rejected',
                'booking_reminder',
                'booking_cancelled'
            ]);
            $table->string('title', 200);
            $table->text('message');
            $table->foreignId('related_booking_id')->nullable()
                  ->constrained('bookings')
                  ->onDelete('cascade');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
