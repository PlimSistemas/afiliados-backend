<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashback_histories', function (Blueprint $table) {
            $table->id('id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('referral_id')->nullable()->constrained('referrals');
            $table->string('referral_name')->nullable();
            $table->decimal('value', 10, 2);
            $table->boolean('is_positive');
            $table->timestamp('date')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashback_histories');
    }
};
