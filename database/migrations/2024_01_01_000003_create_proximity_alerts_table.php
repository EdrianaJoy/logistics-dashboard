<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proximity_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_id')->nullable();
            $table->decimal('warehouse_lat', 10, 8);
            $table->decimal('warehouse_lng', 11, 8);
            $table->decimal('delivery_lat', 10, 8);
            $table->decimal('delivery_lng', 11, 8);
            $table->decimal('distance', 8, 2); // in meters
            $table->integer('radius'); // alert radius in meters
            $table->boolean('within_range');
            $table->enum('alert_type', ['proximity_check', 'batch_check', 'real_time']);
            $table->enum('status', ['sent', 'failed', 'pending']);
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('alert_sent_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['delivery_id', 'created_at']);
            $table->index(['within_range', 'created_at']);
            $table->index('alert_sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proximity_alerts');
    }
};
