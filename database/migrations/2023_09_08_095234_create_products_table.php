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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->integer('buy_price')->default(0);
            $table->integer('sale_price')->default(0);
            $table->string('color')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('guarantee')->default(0);
            $table->unsignedBigInteger('group');
            $table->integer('active')->default(1);
            $table->timestamps();
            // key
            $table->foreign('group')->references('id')->on('product_groups')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
