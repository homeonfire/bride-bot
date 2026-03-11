<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Models\Questionnaire;
use App\Telegram\Conversations\RegisterBrideConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

// --- КОМАНДА /START ---
$bot->onCommand('start', function (Nutgram $bot) {
    $questionnaire = Questionnaire::where('user_id', $bot->userId())->first();

    if ($questionnaire && $questionnaire->is_published) {
        $questionText = urlencode("София, привет 🤍 У меня вопрос");
        // При старте всегда отправляем новое сообщение
        $bot->sendMessage(
            "Моя драгоценная, твоя анкета уже заполнена и опубликована! 🤍\nВыбери, как тебе комфортнее двигаться дальше:",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('👑 Подобрать мне мужчину', callback_data: 'menu_match'))
                ->addRow(InlineKeyboardButton::make('👀 Показать анкеты мужчин', callback_data: 'menu_show_men'))
                ->addRow(InlineKeyboardButton::make('Консультация с Софией 💎', callback_data: 'menu_consult'))
                ->addRow(InlineKeyboardButton::make('Задать вопрос ❓', url: "https://t.me/mosheinlove_1?text={$questionText}"))
        );
        return;
    }

    RegisterBrideConversation::begin($bot);
})->description('Начать заполнение анкеты');

// --- ОБРАБОТЧИК КНОПКИ "НАЗАД" ---
$bot->onCallbackQueryData('back_to_main', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    
    $questionText = urlencode("София, привет 🤍 У меня вопрос");
    
    // Редактируем текущее сообщение, возвращая главное меню
    $bot->editMessageText(
        "Выбери, как тебе комфортнее двигаться дальше:",
        reply_markup: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('👑 Подобрать мне мужчину', callback_data: 'menu_match'))
            ->addRow(InlineKeyboardButton::make('👀 Показать анкеты мужчин', callback_data: 'menu_show_men'))
            ->addRow(InlineKeyboardButton::make('Консультация с Софией 💎', callback_data: 'menu_consult'))
            ->addRow(InlineKeyboardButton::make('Задать вопрос ❓', url: "https://t.me/mosheinlove_1?text={$questionText}"))
    );
});

// --- ВЕТКА: ПОДОБРАТЬ МУЖЧИНУ ---
$bot->onCallbackQueryData('menu_match', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    $q = Questionnaire::where('user_id', $bot->userId())->first();
    
    $text = "София, выбирай мне мужчину.\nЯ заслужила лучшего 😄✨\n\nМои данные:\n{$q->name}, {$q->age} лет\nТелефон: {$q->phone}";
    $encodedText = urlencode($text);

    $bot->editMessageText(
        "Хочешь, чтобы мы подобрали тебе мужчину персонально? 🤍\nЖми кнопку и мне прилетит твой запрос, чтобы мы могли начать подбор ✨",
        reply_markup: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('✅ Хочу личный подбор', url: "https://t.me/mosheinlove_1?text={$encodedText}"))
            ->addRow(InlineKeyboardButton::make('🔙 Назад', callback_data: 'back_to_main'))
    );
});

// --- ВЕТКА: ПОКАЗАТЬ АНКЕТЫ МУЖЧИН ---
$bot->onCallbackQueryData('menu_show_men', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    
    $bot->editMessageText(
        "Моя драгоценная 🤍\nСейчас мужская база в активном наборе.\nХочешь попасть на первый заезд нашего “конного клуба”? 🐎😄\nСтавь уведомление, как только первые анкеты мужчин будут готовы,я сообщу тебе тут 👑",
        reply_markup: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('🔙 Назад', callback_data: 'back_to_main'))
    );
});

// --- ВЕТКА: КОНСУЛЬТАЦИЯ ---
$bot->onCallbackQueryData('menu_consult', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    $q = Questionnaire::where('user_id', $bot->userId())->first();
    
    $text = "София, привет 🤍 Хочу к тебе консультацию, чтобы найти своего мужчину 👑🐎\n\nМои данные:\n{$q->name}, {$q->age} лет\nГород: {$q->location}\nТелефон: {$q->phone}";
    $encodedText = urlencode($text);

    $msg = "Консультация с Софией\n".
           "Помогаю тебе найти своего мужчину: составить сильную анкету знакомств и “упаковать” тебя так, чтобы откликались достойные и успешные.\n\n".
           "Что входит:\n".
           "— Список 200 актуальных площадок и “секретных локаций” по всему миру, где знакомятся с достойными мужчинами\n".
           "(приложения / сайты / комьюнити / ивенты)\n".
           "— Список сайтов знакомств + готовые шаблоны: 10 стартовых сообщений\n".
           "— Типичные ошибки, из-за которых мужчины сливаются\n".
           "— Мои практические советы и ответы на твои вопросы\n\n".
           "Формат: 60 минут, консультация проходит в Zoom\n".
           "Стоимость: 49 000 ₽";

    $bot->editMessageText($msg,
        reply_markup: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make('Записаться', url: "https://t.me/mosheinlove_1?text={$encodedText}"))
            ->addRow(InlineKeyboardButton::make('🔙 Назад', callback_data: 'back_to_main'))
    );
});