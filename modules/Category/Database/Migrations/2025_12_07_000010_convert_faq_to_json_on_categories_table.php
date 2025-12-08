<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('categories')->orderBy('id')->chunkById(100, function ($categories) {
            foreach ($categories as $category) {
                $raw = $category->faq;

                if ($raw === null) {
                    continue;
                }

                $trimmed = trim((string) $raw);

                if ($trimmed === '') {
                    DB::table('categories')->where('id', $category->id)->update(['faq' => null]);
                    continue;
                }

                $decoded = json_decode($trimmed, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $payload = $decoded;
                } else {
                    $payload = [
                        [
                            'q' => 'Genel Bilgi',
                            'a' => $trimmed,
                        ],
                    ];
                }

                DB::table('categories')->where('id', $category->id)->update([
                    'faq' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ]);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->json('faq')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->longText('faq')->nullable()->change();
        });
    }
};
