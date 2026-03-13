<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            // Разрешаем этим полям быть пустыми при первом сохранении анкеты
            $table->string('phone')->nullable()->change();
            $table->string('whatsapp')->nullable()->change();
            $table->string('telegram_username')->nullable()->change();
            $table->string('instagram')->nullable()->change();
            $table->json('photos')->nullable()->change(); // Заодно и фото
        });
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->string('phone')->nullable(false)->change();
            $table->string('whatsapp')->nullable(false)->change();
            $table->string('telegram_username')->nullable(false)->change();
            $table->string('instagram')->nullable(false)->change();
            $table->json('photos')->nullable(false)->change();
        });
    }
};