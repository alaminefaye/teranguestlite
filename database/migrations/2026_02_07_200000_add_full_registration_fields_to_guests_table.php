<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->string('gender', 20)->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('nationality', 100)->nullable()->after('date_of_birth');
            $table->string('address')->nullable()->after('nationality');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('country', 100)->nullable()->after('city');
            $table->string('id_document_type', 50)->nullable()->after('country'); // CNI, Passeport, etc.
            $table->string('id_document_number', 100)->nullable()->after('id_document_type');
            $table->string('id_document_place_of_issue', 150)->nullable()->after('id_document_number');
            $table->date('id_document_issued_at')->nullable()->after('id_document_place_of_issue');
            $table->string('id_document_photo')->nullable()->after('id_document_issued_at'); // Chemin stockage
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn([
                'gender', 'date_of_birth', 'nationality', 'address', 'city', 'country',
                'id_document_type', 'id_document_number', 'id_document_place_of_issue',
                'id_document_issued_at', 'id_document_photo',
            ]);
        });
    }
};
