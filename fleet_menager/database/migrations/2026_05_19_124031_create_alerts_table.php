<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('event_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('type', [
                'expiry_warning',
                'overdue',
                'reminder',
                'incident',
            ]);
            $table->date('trigger_date');
            $table->boolean('dismissed')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['trigger_date', 'dismissed']);
            $table->index('vehicle_id');
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
