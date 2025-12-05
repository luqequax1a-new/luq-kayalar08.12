<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id')->unsigned()->index();
            $table->integer('sender_id')->unsigned()->index();
            $table->string('sender_type');
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
