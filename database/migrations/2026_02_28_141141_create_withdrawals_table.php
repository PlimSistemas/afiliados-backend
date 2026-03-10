<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id('id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['pix', 'invoice_credit']);
            $table->enum('status', ['pending', 'approved', 'processing', 'paid', 'rejected'])->default('pending');
            $table->string('pix_key')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('rejection_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
