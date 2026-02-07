<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->integer('quantity')->default(0);
            $table->string('image')->nullable();
            $table->integer('max_discount')->default(0);
            $table->integer('stock_alert')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
