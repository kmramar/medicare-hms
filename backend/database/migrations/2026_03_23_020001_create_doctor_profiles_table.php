<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('specialty');
            $table->text('qualifications')->nullable();
            $table->integer('experience_years')->default(0);
            $table->text('bio')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(0);
            $table->string('available_days'); // comma-separated days
            $table->time('available_time_start');
            $table->time('available_time_end');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
