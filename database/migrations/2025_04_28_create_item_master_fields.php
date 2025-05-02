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
        // Rename the 'name' column to 'item_name' and add the new fields
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('name', 'item_name');
            $table->float('length')->nullable()->after('item_name');
            $table->float('width')->nullable()->after('length');
            $table->float('height')->nullable()->after('width');
            $table->float('weight')->nullable()->after('height');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('item_name', 'name');
            $table->dropColumn(['length', 'width', 'height', 'weight']);
        });
    }
};
