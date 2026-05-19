<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('event_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('entered_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->enum('category', [
                'fuel',
                'service',
                'repair',
                'insurance',
                'tax',
                'fine',
                'parts',
                'other',
            ]);
            $table->decimal('amount', 10, 2);
            $table->date('incurred_at');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'incurred_at']);
            $table->index('category');
            $table->index('event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};
