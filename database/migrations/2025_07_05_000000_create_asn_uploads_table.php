<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asn_uploads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->string('invoice_number')->nullable();
            $table->timestamp('asn_timestamp')->nullable();
            $table->string('sr')->nullable();
            $table->text('description')->nullable();
            $table->string('part_no')->nullable();
            $table->string('model')->nullable();
            $table->string('pcs')->nullable();
            $table->string('status')->default('pending');
            $table->integer('inward_qty')->nullable();
            $table->integer('transfer_qty')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asn_uploads');
    }
}; 