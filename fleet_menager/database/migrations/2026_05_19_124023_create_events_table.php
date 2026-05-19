<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('reported_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->enum('type', [
                'insurance',
                'inspection',
                'service',
                'repair',
                'incident',
                'other',
            ]);
            $table->date('event_date');
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'in_progress', 'closed'])
                ->default('open');
            $table->timestamps();

            $table->index(['vehicle_id', 'event_date']);
            $table->index('expiry_date');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
