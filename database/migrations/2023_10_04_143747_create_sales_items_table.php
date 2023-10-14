<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quality')->default(0);
            $table->integer('get_more')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('commission')->default(0);
            $table->integer('commission_type')->default(0);
            $table->integer('commission_target')->default(0);
            $table->integer('guarantee')->default(0);
            $table->integer('price')->default(0);
            $table->timestamps();
            // key
            $table->foreign('sales_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_items');
    }
};
