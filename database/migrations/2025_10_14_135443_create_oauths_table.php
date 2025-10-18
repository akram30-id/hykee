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
        Schema::create('oauths', function (Blueprint $table) {
            $table->id();
            $table->string('client_id', 50)->nullable()->index();
            $table->string('client_secret')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('available_token')->nullable();
            $table->dateTime('token_expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauths');
    }
};
