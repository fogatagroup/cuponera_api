<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid');
            $table->enum('status',[
                'created',
                'reversed',
                'completed',
                'cancelled',
            ])->default('created');
            $table->string('currency',3);
            $table->string('sender_name',50)->nullable();
            $table->string('receive_name',50)->nullable();
            $table->decimal('amount');
            $table->decimal('final_amount')->nullable();
            $table->text('internal_notes',500)->nullable();
            $table->string('extra_info',500)->nullable();
            $table->date('complete_at')->nullable();
            $table->string('merchant_reference',250)->nullable();
            $table->string('channel_reference',250)->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
