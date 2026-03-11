<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    // 1. Разрешаем массовое заполнение для этих полей
    protected $fillable = [
        'user_id',
        'name',
        'age',
        'height',
        'has_kids',
        'wants_kids',
        'location',
        'occupation',
        'hobbies',
        'about_me',
        'man_qualities',
        'photos',
        'phone',
        'whatsapp',
        'telegram_username',
        'instagram',
        'is_published',
    ];

    // 2. Указываем, что колонка photos в базе (JSON) должна автоматически превращаться в массив в PHP
    protected $casts = [
        'photos' => 'array',
        'en_text' => 'array', // Добавили эту строку!
    ];
}