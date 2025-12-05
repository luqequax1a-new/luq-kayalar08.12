<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('message_id')->unsigned()->index();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('size')->unsigned();
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('ticket_messages')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
