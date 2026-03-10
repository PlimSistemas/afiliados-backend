<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id('id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('referrer_id')->constrained('users');
            $table->string('referred_name');
            $table->string('referred_cpf')->nullable();
            $table->string('referred_phone')->nullable();
            $table->string('plan');
            $table->enum('status', ['awaiting', 'contracted', 'awaiting_payment', 'cashback_released', 'paid', 'rejected'])->default('awaiting');
            $table->decimal('cashback_value', 10, 2);
            $table->timestamp('contracted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
