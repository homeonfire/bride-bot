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
    Schema::create('questionnaires', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id')->unique(); // ID пользователя в Telegram
        $table->string('name')->nullable();
        $table->integer('age')->nullable();
        $table->integer('height')->nullable();
        $table->boolean('has_kids')->default(false);
        $table->string('wants_kids')->nullable();
        $table->string('location')->nullable();
        $table->string('occupation')->nullable();
        $table->string('hobbies')->nullable();
        $table->text('about_me')->nullable();
        $table->text('man_qualities')->nullable();
        $table->json('photos')->nullable(); // Будем хранить массив file_id из Telegram
        $table->string('phone')->nullable();
        $table->string('whatsapp')->nullable();
        $table->string('telegram_username')->nullable();
        $table->string('instagram')->nullable();
        $table->text('en_text')->nullable(); // ДОБАВИЛИ ЭТУ СТРОКУ
        $table->boolean('is_published')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
