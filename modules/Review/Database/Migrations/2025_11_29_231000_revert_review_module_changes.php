<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('review_votes')) {
            Schema::drop('review_votes');
        }

        if (Schema::hasColumn('reviews', 'verified_purchase')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('verified_purchase');
            });
        }
    }

    public function down()
    {
        // No-op: this migration only reverts prior changes.
    }
};

