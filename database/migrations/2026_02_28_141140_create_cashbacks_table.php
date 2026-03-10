<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbacks', function (Blueprint $table) {
            $table->id('id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('available_balance', 10, 2)->default(0);
            $table->decimal('blocked_balance', 10, 2)->default(0);
            $table->decimal('total_earned', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbacks');
    }
};
