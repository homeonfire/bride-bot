<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Questionnaire;
use Illuminate\Support\Facades\Http;

class TranslateProfiles extends Command
{
    // Как мы будем вызывать эту команду в терминале
    protected $signature = 'app:translate-profiles';

    // Описание команды
    protected $description = 'Переводит анкеты на английский язык через DeepSeek, если перевода еще нет';

    public function handle()
    {
        $apiKey = env('DEEPSEEK_API_KEY');
        if (!$apiKey) {
            $this->error('Ключ DEEPSEEK_API_KEY не найден в .env!');
            return;
        }

        // Ищем анкеты, где en_text пустой (null) или это пустая строка
        $questionnaires = Questionnaire::whereNull('en_text')
            ->orWhere('en_text', '')
            ->orWhere('en_text', '[]')
            ->get();

        if ($questionnaires->isEmpty()) {
            $this->info('Все анкеты уже переведены! 🎉');
            return;
        }

        $this->info("Найдено анкет без перевода: " . $questionnaires->count());
        $this->newLine();

        $bar = $this->output->createProgressBar($questionnaires->count());
        $bar->start();

        foreach ($questionnaires as $q) {
            $dataToTranslate = [
                'location' => $q->location,
                'occupation' => $q->occupation,
                'hobbies' => $q->hobbies,
                'about_me' => $q->about_me,
                'man_qualities' => $q->man_qualities,
                'wants_kids' => $q->wants_kids,
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('https://api.deepseek.com/chat/completions', [
                    'model' => 'deepseek-chat',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Ты точный переводчик. Переведи значения предоставленного JSON с русского на английский. Верни результат строго в формате JSON с теми же ключами.'
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode($dataToTranslate, JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $text = $response->json('choices.0.message.content');
                    $text = str_replace(['```json', '```'], '', $text);
                    $decoded = json_decode(trim($text), true);

                    if ($decoded) {
                        $q->en_text = $decoded;
                        $q->save();
                    } else {
                        $this->error("\n❌ Ошибка парсинга JSON для анкеты #{$q->id}");
                    }
                } else {
                    $this->error("\n❌ Ошибка API DeepSeek для анкеты #{$q->id}");
                }
            } catch (\Exception $e) {
                $this->error("\n❌ Ошибка соединения: " . $e->getMessage());
            }

            // Обязательная пауза в 1 секунду, чтобы API не забанил нас за спам запросами
            sleep(1);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Готово! Все доступные анкеты обработаны.');
    }
}