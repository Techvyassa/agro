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
        Schema::create('superadmin_item_masters', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('category_name');
            $table->string('unit');
            $table->integer('moq');
            $table->string('sku_name_code');
            $table->integer('category_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superadmin_item_masters');
    }
};
