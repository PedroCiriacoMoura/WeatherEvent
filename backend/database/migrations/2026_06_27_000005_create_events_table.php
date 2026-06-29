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
        $supportsSpatial = Schema::getConnection()->getDriverName() !== 'sqlite';

        Schema::create('events', function (Blueprint $table) use ($supportsSpatial) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('event_categories');
            $table->foreignId('status_id')->constrained('event_statuses');

            $table->string('name');
            $table->text('description')->nullable();

            $table->string('city', 120);
            $table->char('country', 2); 
            if ($supportsSpatial) {
                $table->point('coordinates', 4326);
            } else {
                $table->binary('coordinates')->nullable();
            }

            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('timezone', 64)->default('UTC');

            $table->unsignedInteger('attendees')->default(0);
            $table->boolean('is_outdoor')->default(false);

            $table->timestamps();
            $table->softDeletes();

            if ($supportsSpatial) {
                $table->spatialIndex('coordinates');
            }
            $table->index(['status_id', 'starts_at']); 
            $table->index(['city', 'country']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
