<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('label');
            $table->decimal('step', 10, 2);
            $table->decimal('min', 10, 2);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_decimal_stock')->default(false);
            $table->string('short_suffix')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};

