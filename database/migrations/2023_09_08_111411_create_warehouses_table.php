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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('fullname')->nullable();
            $table->integer('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('top_invoice')->nullable();
            $table->string('middle_invoice')->nullable();
            $table->string('bottom_invoice')->nullable();
            $table->string('note_invoice')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
