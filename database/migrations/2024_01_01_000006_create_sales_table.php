<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('total', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->enum('payment_method', ['especes', 'mobile_money', 'carte']);
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
