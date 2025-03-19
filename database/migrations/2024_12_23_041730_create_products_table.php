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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable(); // Mô tả sản phẩm
            $table->integer('price')->unsigned();
            $table->integer('stock'); // Số lượng tồn kho
            $table->string('image', 255)->nullable();
            $table->foreignId('seller_id')->constrained('users'); // Người bán
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
