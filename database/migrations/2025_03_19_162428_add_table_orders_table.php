<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->default(0)->change(); // Đặt giá trị mặc định là 0
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->nullable(false)->change(); // Bỏ giá trị mặc định
        });
    }
};
