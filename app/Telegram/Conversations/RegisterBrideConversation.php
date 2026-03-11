<?php

namespace App\Telegram\Conversations;

use App\Models\Questionnaire;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Input\InputMediaPhoto;
use Illuminate\Support\Facades\Http;

class RegisterBrideConversation extends Conversation
{
    public array $data = [];

    // --- БЕЗОПАСНЫЙ ОБРАБОТЧИК КНОПОК ---
    private function safeAnswerCallback(Nutgram $bot)
    {
        if ($bot->isCallbackQuery()) {
            try {
                $bot->answerCallbackQuery();
            } catch (\Exception $e) {}
        }
    }

    // --- ГЕНЕРАТОР ПРОГРЕСС-БАРА ---
    private function getProgress(int $currentStep, int $totalSteps = 14): string
    {
        $filled = str_repeat('⬛', $currentStep);
        $empty = str_repeat('⬜', $totalSteps - $currentStep);
        return "Шаг {$currentStep} из {$totalSteps}\n{$filled}{$empty}\n\n";
    }

    public function start(Nutgram $bot)
    {
        $bot->sendMessage(
            "Привет, моя драгоценная! 🤍 Это София Моше\n".
            "Я психолог, бизнес-коуч и по совместительству сваха, которая мечтает гулять на ваших свадьбах 😄\n".
            "Сейчас я собираю анкеты подруг в закрытый канал и знакомлю вас с достойными мужчинами из Америки (и не только), которые настроены на отношения.\n".
            "Проверим судьбу? Может, это твой знак — и именно тут ты найдёшь своего мужчину ✨\n".
            "Готова собрать свою анкету?",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✅ Да', callback_data: 'yes'))
                ->addRow(InlineKeyboardButton::make('❌ Нет', callback_data: 'no'))
        );
        $this->next('stepConsent');
    }

    public function stepConsent(Nutgram $bot)
    {
        $this->safeAnswerCallback($bot);

        if ($bot->callbackQuery()?->data === 'no') {
            $bot->sendMessage('Тогда я не смогу оформить анкету 🤍 Если передумаешь, нажми /start снова.');
            return $this->end();
        }

        // Формируем текст с HTML-тегом <a> для ссылки
        // ВАЖНО: Вместо ССЫЛКА_НА_ТВОЙ_ГУГЛ_ДОК вставь реальную ссылку от клиентки!
        $text = "Немного официальной части 🤍\n\nЧтобы продолжить, мне нужно твоё <a href=\"https://docs.google.com/document/d/10Bbp5ci36911Fzyk34NXYf9MklAIj7kKA5RkVzAxp4g/\">согласие на обработку твоих персональных данных</a>";

        $bot->sendMessage($text,
            parse_mode: 'HTML', // Обязательно указываем Telegram, что тут есть HTML-теги
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✅ Согласна', callback_data: 'agreed'))
                ->addRow(InlineKeyboardButton::make('❌ Не согласна', callback_data: 'disagreed'))
        );
        
        $this->next('askName');
    }

    public function askName(Nutgram $bot)
    {
        $this->safeAnswerCallback($bot);

        if ($bot->callbackQuery()?->data === 'disagreed') {
            $bot->sendMessage('Тогда я не смогу оформить анкету 🤍 Если передумаешь, нажми /start снова.');
            return $this->end();
        }

        $bot->sendMessage($this->getProgress(1) . 'Окей, моя драгоценная, Как тебя зовут? 💎');
        $this->next('askAge');
    }

    public function askAge(Nutgram $bot)
    {
        $this->data['name'] = $bot->message()?->text;
        $bot->sendMessage($this->getProgress(2) . 'Сколько тебе лет?');
        $this->next('askHeight');
    }

    public function askHeight(Nutgram $bot)
    {
        $this->data['age'] = (int)$bot->message()?->text;
        $bot->sendMessage($this->getProgress(3) . 'Твой рост (в см):');
        $this->next('askKids');
    }

    public function askKids(Nutgram $bot)
    {
        $this->data['height'] = (int)$bot->message()?->text;
        $bot->sendMessage($this->getProgress(4) . 'Есть ли дети?', 
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✅ Да', callback_data: 'Да'))
                ->addRow(InlineKeyboardButton::make('❌ Нет', callback_data: 'Нет'))
        );
        $this->next('askWantsKids');
    }

    public function askWantsKids(Nutgram $bot)
    {
        $this->safeAnswerCallback($bot);
        $this->data['has_kids'] = $bot->callbackQuery()?->data === 'Да';

        $bot->sendMessage($this->getProgress(5) . 'Хочешь детей?', 
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✅ Да', callback_data: 'Да'))
                ->addRow(InlineKeyboardButton::make('🤍 Ещё не решила', callback_data: 'Ещё не решила'))
                ->addRow(InlineKeyboardButton::make('❌ Нет', callback_data: 'Нет'))
        );
        $this->next('askLocation');
    }

    public function askLocation(Nutgram $bot)
    {
        $this->safeAnswerCallback($bot);
        $this->data['wants_kids'] = $bot->callbackQuery()?->data;

        $bot->sendMessage($this->getProgress(6) . 'Где ты сейчас живёшь? (город + страна)');
        $this->next('askOccupation');
    }

    public function askOccupation(Nutgram $bot)
    {
        $this->data['location'] = $bot->message()?->text;
        $bot->sendMessage($this->getProgress(7) . 'Профессия / чем занимаешься:');
        $this->next('askHobbies');
    }

    public function askHobbies(Nutgram $bot)
    {
        $this->data['occupation'] = $bot->message()?->text;
        $bot->sendMessage($this->getProgress(8) . 'Хобби (3 слова):');
        $this->next('askAbout');
    }

    public function askAbout(Nutgram $bot)
    {
        $this->data['hobbies'] = $bot->message()?->text;
        $bot->sendMessage($this->getProgress(9) . 'Кратко о себе (3-4 предложения):');
        $this->next('askManQualities');
    }

    public function askManQualities(Nutgram $bot)
    {
        $this->data['about_me'] = $bot->message()?->text;
        $bot->sendMessage($this->getProgress(10) . 'Какими качествами должен обладать твой мужчина?');
        $this->next('saveDataAndAskPhotos');
    }

    public function saveDataAndAskPhotos(Nutgram $bot)
    {
        $this->data['man_qualities'] = $bot->message()?->text;
        $this->data['photos'] = [];

        Questionnaire::updateOrCreate(
            ['user_id' => $bot->userId()],
            $this->data
        );

        $bot->sendMessage($this->getProgress(11) . "Осталось чуть-чуть 🤍\nЗагрузи до 6 фото, чтобы твой будущий мужчина увидел тебя и обомлел от твоей красоты ✨\n\nПрисылай фото по одному. Когда закончишь, напиши 'Готово'");
        
        $this->next('collectPhotos');
    }

    public function collectPhotos(Nutgram $bot)
    {
        if (mb_strtolower($bot->message()?->text) === 'готово') {
            $bot->sendMessage($this->getProgress(12) . "Твой номер телефона (в международном формате, например +1... / +7...)\n*Контакт и соц сети не будут в открытом доступе, они хранятся у меня. Я передам их мужчине с твоего разрешения");
            $this->next('askWhatsApp');
            return;
        }

        if ($bot->message()?->photo) {
            $photos = $bot->message()->photo;
            $largestPhoto = end($photos);

            $currentPhotos = $this->data['photos'] ?? [];

            if (count($currentPhotos) < 6) {
                $currentPhotos[] = $largestPhoto->file_id;
                $this->data['photos'] = $currentPhotos;

                $questionnaire = Questionnaire::where('user_id', $bot->userId())->first();
                if ($questionnaire) {
                    $questionnaire->photos = $currentPhotos;
                    $questionnaire->save();
                }

                $count = count($currentPhotos);
                if ($count >= 6) {
                    $bot->sendMessage($this->getProgress(12) . "Отлично, все 6 фото загружены! 📸\nТвой номер телефона (в международном формате):");
                    $this->next('askWhatsApp');
                } else {
                    $bot->sendMessage("Приняла ({$count}/6)! Пришли еще или напиши 'Готово'");
                    $this->next('collectPhotos');
                }
            }
        } else {
            $bot->sendMessage('Пожалуйста, отправь картинку или напиши "Готово" 🤍');
            $this->next('collectPhotos');
        }
    }

    public function askWhatsApp(Nutgram $bot)
    {
        $this->data['phone'] = $bot->message()?->text;
        $bot->sendMessage($this->getProgress(13) . 'Твой WhatsApp:');
        $this->next('askTelegramUser');
    }

    public function askTelegramUser(Nutgram $bot)
    {
        $this->data['whatsapp'] = $bot->message()?->text;
        $bot->sendMessage($this->getProgress(14) . 'Твой Telegram (username @... или ссылка):');
        $this->next('askInstagram');
    }

    public function askInstagram(Nutgram $bot)
    {
        $this->data['telegram_username'] = $bot->message()?->text;
        $bot->sendMessage('Финальный шаг! ✨ Твой Instagram @....:');
        $this->next('finalizeProfile');
    }

    public function finalizeProfile(Nutgram $bot)
    {
        $this->data['instagram'] = $bot->message()?->text;

        $questionnaire = Questionnaire::updateOrCreate(
            ['user_id' => $bot->userId()],
            $this->data
        );

        $bot->sendMessage("Готово, моя драгоценная 🤍\nСейчас оформлю твою карточку на русском и английском и публикую в канале, пусть мужчины увидят, какая ты 👇✨");

        $ruText = $this->buildProfileText($questionnaire, 'ru');
        $waitMsg = $bot->sendMessage("⏳ Перевожу твою анкету через нейросеть, подожди пару секунд...");

        $enData = $this->translateWithAI($questionnaire);
        $questionnaire->en_text = $enData;
        $questionnaire->save();

        if ($waitMsg) {
            try { $bot->deleteMessage($bot->chatId(), $waitMsg->message_id); } catch (\Exception $e) {}
        }

        $photos = $questionnaire->photos ?? [];

        if (!empty($photos)) {
            $media = [];
            foreach ($photos as $photoId) {
                $media[] = InputMediaPhoto::make($photoId);
            }
            $bot->sendMediaGroup($media);
        }

        $bot->sendMessage("Твоя анкета готова 🤍 (RU)\n\n" . $ruText);

        $enText = $this->buildProfileText($questionnaire, 'en');
        $bot->sendMessage("Your profile is ready 🤍 (EN)\n\n" . $enText, 
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✅ Опубликовать в канал', callback_data: 'publish_profile'))
                ->addRow(InlineKeyboardButton::make('✏️ Изменить ответы', callback_data: 'restart_profile'))
        );

        $this->next('handlePublish');
    }

    public function handlePublish(Nutgram $bot)
    {
        $this->safeAnswerCallback($bot);
        $action = $bot->callbackQuery()?->data;

        if ($action === 'restart_profile') {
            $bot->sendMessage('Хорошо, давай заполним заново! 🤍');
            $this->start($bot);
            return;
        }

        if ($action === 'publish_profile') {
            $questionnaire = Questionnaire::where('user_id', $bot->userId())->first();
            $questionnaire->is_published = true;
            $questionnaire->save();

            $channelId = env('TELEGRAM_CHANNEL_ID');
            if ($channelId) {
                $ruText = $this->buildProfileText($questionnaire, 'ru');
                $enText = $this->buildProfileText($questionnaire, 'en');
                $photos = $questionnaire->photos ?? [];

                if (!empty($photos)) {
                    $media = [];
                    foreach ($photos as $photoId) {
                        $media[] = InputMediaPhoto::make($photoId);
                    }
                    $bot->sendMediaGroup($media, chat_id: $channelId);
                }
                $bot->sendMessage("✨ НОВАЯ АНКЕТА ✨\n\n" . $ruText, chat_id: $channelId);
                $bot->sendMessage("🇬🇧 ENGLISH VERSION 🇬🇧\n\n" . $enText, chat_id: $channelId);
            }

            $bot->sendMessage(
                "Готово, моя драгоценная 🤍\n".
                "Твою анкету я уже опубликовала на канале ✨\n".
                "Дальше всё просто: мужчины смотрят анкеты, выбирают ❤️ и как только по тебе будет интерес, я дам тебе знать ✨\n\n".
                "А пока можешь выбрать, как тебе комфортнее двигаться дальше:",
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('👑 Подобрать мне мужчину', callback_data: 'menu_match'))
                    ->addRow(InlineKeyboardButton::make('👀 Показать анкеты мужчин', callback_data: 'menu_show_men'))
                    ->addRow(InlineKeyboardButton::make('💎 Консультация с Софией', url: 'https://t.me/твой_юзернейм'))
                    ->addRow(InlineKeyboardButton::make('❓ Задать вопрос', url: 'https://t.me/твой_юзернейм'))
            );
            $this->end();
        }
    }

    private function translateWithAI(Questionnaire $q): array
    {
        $apiKey = env('OPENROUTER_API_KEY');
        if (!$apiKey) return [];

        $dataToTranslate = [
            'location' => $q->location,
            'occupation' => $q->occupation,
            'hobbies' => $q->hobbies,
            'about_me' => $q->about_me,
            'man_qualities' => $q->man_qualities,
            'wants_kids' => $q->wants_kids,
        ];

        try {
            // Увеличиваем таймаут до 90 секунд
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'qwen/qwen3-vl-30b-a3b-thinking',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Ты переводчик. Переведи значения этого JSON с русского на английский. Верни ТОЛЬКО валидный JSON с теми же ключами, без лишнего текста и без форматирования markdown.'
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
                return json_decode(trim($text), true) ?? [];
            }
        } catch (\Exception $e) {
            // Если OpenRouter завис или отвалился, логируем ошибку, 
            // но НЕ РОНЯЕМ бота. Возвращаем пустой массив.
            \Illuminate\Support\Facades\Log::error('AI Translation failed: ' . $e->getMessage());
        }

        // Возвращаем пустой массив, если что-то пошло не так
        return [];
    }

    private function buildProfileText(Questionnaire $q, string $lang): string
    {
        $kidsRu = $q->has_kids ? 'Да' : 'Нет';
        $kidsEn = $q->has_kids ? 'Yes' : 'No';

        if ($lang === 'ru') {
            return "АНКЕТА #{$q->id}\n" .
                   "Имя: {$q->name}\nВозраст: {$q->age}\nРост: {$q->height} см\n" .
                   "Дети: {$kidsRu}\nХочет детей: {$q->wants_kids}\nГород/страна: {$q->location}\n" .
                   "Профессия: {$q->occupation}\nХобби: {$q->hobbies}\n" .
                   "О себе: {$q->about_me}\nИщу мужчину: {$q->man_qualities}";
        }

        $en = $q->en_text ?? [];
        return "PROFILE #{$q->id}\n" .
               "Name: {$q->name}\nAge: {$q->age}\nHeight: {$q->height} cm\n" .
               "Kids: {$kidsEn}\nWants kids: " . ($en['wants_kids'] ?? $q->wants_kids) . "\n" .
               "City/Country: " . ($en['location'] ?? $q->location) . "\n" .
               "Occupation: " . ($en['occupation'] ?? $q->occupation) . "\n" .
               "Hobbies: " . ($en['hobbies'] ?? $q->hobbies) . "\n" .
               "About: " . ($en['about_me'] ?? $q->about_me) . "\n" .
               "Looking for: " . ($en['man_qualities'] ?? $q->man_qualities);
    }
}