<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TbTicket extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_ticket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id');
            $table->integer('ticket_no')->nullable(false);
            $table->tinyInteger('is_called')->default(0);
            $table->timestamps();

            // Define foreign key with correct references
            // $table->foreign('service_id')->references('id')->on('tb_service')->onDelete('cascade');

            // Add index to the foreign key column
            // $table->index('service_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_ticket');
    }
}
