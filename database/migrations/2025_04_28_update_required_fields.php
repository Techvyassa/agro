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
        // Make non-primary fields nullable to allow item creation with only the required fields
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('category')->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->integer('stock_quantity')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->string('category')->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->change();
            $table->integer('stock_quantity')->nullable(false)->change();
        });
    }
};
