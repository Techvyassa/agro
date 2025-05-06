<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoNoToPickingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the pickings table exists
        if (Schema::hasTable('pickings')) {
            // Check if the so_no column doesn't exist yet
            if (!Schema::hasColumn('pickings', 'so_no')) {
                Schema::table('pickings', function (Blueprint $table) {
                    $table->string('so_no')->after('box')->nullable();
                });
            }

            // Check if the items column doesn't exist yet
            if (!Schema::hasColumn('pickings', 'items')) {
                Schema::table('pickings', function (Blueprint $table) {
                    $table->json('items')->after('so_no')->nullable();
                });
            }

            // Check if the dimension column doesn't exist yet
            if (!Schema::hasColumn('pickings', 'dimension')) {
                Schema::table('pickings', function (Blueprint $table) {
                    $table->string('dimension')->after('items')->nullable();
                });
            }

            // Check if the weight column doesn't exist yet
            if (!Schema::hasColumn('pickings', 'weight')) {
                Schema::table('pickings', function (Blueprint $table) {
                    $table->string('weight')->after('dimension')->nullable();
                });
            }
        } else {
            // Create the pickings table if it doesn't exist
            Schema::create('pickings', function (Blueprint $table) {
                $table->id();
                $table->string('box');
                $table->string('so_no');
                $table->json('items');
                $table->string('dimension')->nullable();
                $table->string('weight')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Don't drop any columns in down method for safety
    }
}
