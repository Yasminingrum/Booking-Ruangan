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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', [
                'laboratorium',
                'ruang_musik',
                'audio_visual',
                'lapangan_basket',
                'kolam_renang'
            ]);
            $table->integer('capacity')->unsigned();
            $table->string('location', 100);
            $table->text('description')->nullable();
            $table->text('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('is_active');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
