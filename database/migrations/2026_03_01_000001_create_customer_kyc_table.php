<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_kyc')) {
            Schema::create('customer_kyc', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id');   // references users.id
                $table->string('document_type');             // aadhar_front | aadhar_back | pancard_front | selfie
                $table->string('file_path');                 // relative path in storage/app/public
                $table->string('original_name')->nullable(); // original uploaded filename
                $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
                $table->timestamps();

                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_kyc');
    }
};
