<?php

namespace App\Jobs;

use App\Models\Broadcast;
use App\Models\TelegramUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class SendTelegramBroadcast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // Час на рассылку

    public function __construct(
        public Broadcast $broadcast
    ) {}

    public function handle(Nutgram $bot): void
    {
        $this->broadcast->update(['status' => 'sending']);
        
        $successCount = 0;
        $errorCount = 0;
        $fileId = $this->broadcast->photo_file_id;
        
        // ТВОЙ ТЕЛЕГРАМ ID (Сюда бот пришлет картинку для кэширования)
        $adminTgId = '7346473871'; 

        // === ЭТАП 1: ПРЕДЗАГРУЗКА КАРТИНКИ ===
        // Если картинка есть, а ID еще нет (не нажимали кнопку Тест)
        if ($this->broadcast->image_path && !$fileId) {
            try {
                $stream = fopen(Storage::disk('public')->path($this->broadcast->image_path), 'r');
                $message = $bot->sendPhoto(
                    photo: $stream,
                    chat_id: $adminTgId,
                    caption: "⚙️ [Системное] Картинка загружена на сервера Telegram для рассылки: <b>" . $this->broadcast->title . "</b>",
                    parse_mode: ParseMode::HTML
                );

                $photos = $message->photo;
                if (!empty($photos)) {
                    $fileId = end($photos)->file_id;
                    $this->broadcast->update(['photo_file_id' => $fileId]);
                }
            } catch (\Exception $e) {
                Log::error('Ошибка предзагрузки картинки для рассылки: ' . $e->getMessage());
                // Если не смогли загрузить картинку, отменяем рассылку
                $this->broadcast->update(['status' => 'draft', 'error_count' => 1]);
                return;
            }
        }

        // === ЭТАП 2: МАССОВАЯ РАССЫЛКА ===
        $users = TelegramUser::all();

        foreach ($users as $user) {
            try {
                if ($fileId) {
                    $bot->sendPhoto(
                        photo: $fileId,
                        chat_id: $user->id,
                        caption: $this->broadcast->message,
                        parse_mode: ParseMode::HTML
                    );
                } else {
                    $bot->sendMessage(
                        text: $this->broadcast->message,
                        chat_id: $user->id,
                        parse_mode: ParseMode::HTML
                    );
                }
                $successCount++;
            } catch (\Exception $e) {
                // Если заблокировал бота или удалился
                $errorCount++;
            }

            usleep(50000); // 50 миллисекунд пауза (~20 сообщений в секунду, чтобы не забанил Telegram)
        }

        // === ЭТАП 3: ФИНАЛИЗАЦИЯ ===
        $this->broadcast->update([
            'status' => 'completed',
            'sent_count' => $successCount,
            'error_count' => $errorCount,
        ]);
    }
}