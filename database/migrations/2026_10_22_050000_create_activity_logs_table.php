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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admin_name');
            $table->string('admin_email')->nullable();
            $table->string('action', 50); // VIEW, INSERT, UPDATE, DELETE
            $table->string('module', 100); // Dashboard, Buku Kas, Warga & Kavling, Legalitas Sertifikat, dll.
            $table->text('description');
            $table->string('method', 10); // GET, POST, PUT, PATCH, DELETE
            $table->string('url', 500);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
