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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->string('patient_name');
            $table->string('patient_folder')->nullable();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->string('status')->default('en_attente'); // en_attente, appele, en_cours, termine, absent, annule
            $table->foreignId('desk_id')->nullable()->constrained('desks')->nullOnDelete();
            $table->string('priority')->default('normal'); // normal, prioritaire
            $table->timestamp('called_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};