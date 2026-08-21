<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedTinyInteger('workers_required')->default(1)->after('duration_minutes');
        });

        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'is_active', 'name']);
        });

        Schema::create('worker_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['worker_id', 'service_id']);
            $table->index(['service_id', 'worker_id']);
        });

        Schema::create('worker_absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'date']);
            $table->index(['worker_id', 'date']);
        });

        Schema::create('appointment_worker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['appointment_id', 'worker_id']);
            $table->index(['worker_id', 'appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_worker');
        Schema::dropIfExists('worker_absences');
        Schema::dropIfExists('worker_service');
        Schema::dropIfExists('workers');

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('workers_required');
        });
    }
};
