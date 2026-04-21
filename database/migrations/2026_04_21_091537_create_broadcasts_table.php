<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('broadcasts', function (Blueprint $table) {
        $table->id();
        $table->string('title')->comment('Внутреннее название');
        $table->text('message')->comment('Текст (с HTML тегами)');
        $table->string('image_path')->nullable()->comment('Локальный путь к картинке'); // <-- НОВОЕ ПОЛЕ
        $table->string('photo_file_id')->nullable()->comment('Кэш ID картинки Telegram');
        $table->string('status')->default('draft')->comment('draft, sending, completed');
        $table->integer('sent_count')->default(0);
        $table->integer('error_count')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
