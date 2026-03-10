<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id('id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('referral_id')->constrained('referrals');
            $table->string('plan_id');
            $table->string('status');
            $table->timestamp('activated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
