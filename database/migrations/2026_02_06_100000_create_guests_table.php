<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enterprise_id')->constrained('enterprises')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('access_code', 20); // Code tablette (ex. 6 chiffres), unique par entreprise
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['enterprise_id', 'access_code']);
            $table->index(['enterprise_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
