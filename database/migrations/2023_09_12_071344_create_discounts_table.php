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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->integer('discount')->default(0);
            $table->integer('get_more')->default(0);
            $table->integer('inv_condition')->default(0);
            $table->timestamps();
            // key
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
        Schema::dropIfExists('discounts');
    }
};
