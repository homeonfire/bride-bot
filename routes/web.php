<?php

use App\Models\Questionnaire;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

// Главная страница (можно сделать лендинг, пока редиректим на каталог)
Route::get('/', function () {
    return redirect('/catalog');
});

// Страница с каталогом невест (РУССКИЙ)
Route::get('/catalog', function () {
    $brides = App\Models\Questionnaire::where('is_published', true)->latest()->get();
    return view('catalog', compact('brides'));
});

// Страница с каталогом невест (АНГЛИЙСКИЙ)
Route::get('/catalog/en', function () {
    $brides = App\Models\Questionnaire::where('is_published', true)->latest()->get();
    return view('catalog_en', compact('brides'));
});

// МАГИЯ: Превращаем Telegram file_id в реальную картинку
Route::get('/photo/{fileId}', function ($fileId, Nutgram $bot) {
    // Кешируем ссылку на 1 час, чтобы не спамить API Telegram
    $url = cache()->remember('tg_photo_' . $fileId, 3600, function () use ($bot, $fileId) {
        try {
            $file = $bot->getFile($fileId);
            return $file ? $bot->downloadUrl($file) : null;
        } catch (\Exception $e) {
            return null;
        }
    });

    if (!$url) {
        return abort(404);
    }

    // Перенаправляем браузер на саму картинку
    return redirect($url);
})->name('photo');