<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_carts', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->text('data');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_carts');
    }
};

