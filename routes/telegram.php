<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Questionnaire;
use App\Telegram\Conversations\RegisterBrideConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

$bot->onCommand('start', function (Nutgram $bot) {
    // Проверяем, есть ли у пользователя завершенная анкета
    $questionnaire = Questionnaire::where('user_id', $bot->userId())->first();

    if ($questionnaire && $questionnaire->is_published) {
        // Анкета есть — выдаем главное меню
        $bot->sendMessage(
            "Моя драгоценная, твоя анкета уже заполнена и опубликована! 🤍\nВыбери, как тебе комфортнее двигаться дальше:",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('👑 Подобрать мне мужчину', callback_data: 'menu_match'))
                ->addRow(InlineKeyboardButton::make('👀 Показать анкеты мужчин', callback_data: 'menu_show_men'))
                ->addRow(InlineKeyboardButton::make('💎 Консультация с Софией', url: 'https://t.me/твой_юзернейм'))
                ->addRow(InlineKeyboardButton::make('❓ Задать вопрос', url: 'https://t.me/твой_юзернейм'))
        );
        return; // Останавливаем выполнение, чтобы диалог регистрации не начался
    }

    // Если анкеты нет (или она не дописана) — запускаем процесс регистрации
    RegisterBrideConversation::begin($bot);
})->description('Начать или открыть меню');

// Твои обработчики menu_match и menu_show_men остаются здесь ниже...
$bot->onCallbackQueryData('menu_match', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    $bot->sendMessage(
        "Хочешь, чтобы мы подобрали тебе мужчину персонально? 🤍\nЖми кнопку и мне прилетит твой запрос, чтобы мы могли начать подбор ✨",
        reply_markup: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('✅ Хочу личный подбор', url: 'https://t.me/твой_юзернейм?text=София, выбирай мне мужчину. Я заслужила лучшего 😄✨'))
    );
});

$bot->onCallbackQueryData('menu_show_men', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    $bot->sendMessage("Моя драгоценная 🤍\nСейчас мужская база в активном наборе. Хочешь попасть на первый заезд нашего “конного клуба”? 🐎😄\nСтавь уведомление, как только первые анкеты мужчин будут готовы, я сообщу тебе 👑");
});