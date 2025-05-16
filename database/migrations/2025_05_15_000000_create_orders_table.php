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
        // Only create the table if it doesn't already exist
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->date('order_date');
            $table->string('pickup_id')->nullable();
            $table->string('pickup_city')->nullable();
            $table->string('pickup_state')->nullable();
            $table->string('return_id')->nullable();
            $table->string('return_city')->nullable();
            $table->string('return_state')->nullable();
            $table->decimal('invoice_amount', 10, 2)->nullable();
            $table->string('item_name')->nullable();
            $table->decimal('cod_amount', 10, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->text('buyer_address')->nullable();
            $table->string('buyer_phone', 20)->nullable();
            $table->string('buyer_pincode', 20)->nullable();
            $table->enum('order_status', ['SUCCESS', 'FAILED', 'PENDING'])->default('PENDING');
            $table->integer('response_code')->nullable();
            $table->string('response_message')->nullable();
            $table->text('raw_request')->nullable();
            $table->text('raw_response')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
