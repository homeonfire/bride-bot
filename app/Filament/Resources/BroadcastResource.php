<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BroadcastResource\Pages;
use App\Models\Broadcast;
use App\Jobs\SendTelegramBroadcast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class BroadcastResource extends Resource
{
    protected static ?string $model = Broadcast::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Рассылки';
    protected static ?string $pluralModelLabel = 'Рассылки';
    protected static ?string $modelLabel = 'Рассылку';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Название рассылки (для себя)')
                    ->required(),
                    
                FileUpload::make('image_path')
                    ->label('Картинка (необязательно)')
                    ->image()
                    ->directory('broadcasts'), 
                    
                Textarea::make('message')
                    ->label('Текст сообщения')
                    ->required()
                    ->rows(10)
                    ->helperText('Поддерживается HTML: <b>жирный</b>, <i>курсив</i>, <u>подчеркнутый</u>, <a href="https://...">ссылка</a>'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Название')->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sending' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Черновик',
                        'sending' => 'Отправляется...',
                        'completed' => 'Завершено',
                        default => $state,
                    }),
                TextColumn::make('sent_count')->label('Доставлено')->color('success'),
                TextColumn::make('error_count')->label('Ошибок')->color('danger'),
                TextColumn::make('created_at')->label('Дата создания')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // === КНОПКА ТЕСТА ===
                Action::make('test_send')
                    ->label('Тест')
                    ->icon('heroicon-o-beaker')
                    ->color('info')
                    ->form([
                        TextInput::make('tg_id')
                            ->label('Ваш Telegram ID')
                            ->required()
                            ->helperText('Найдите свой ID в боте @getmyid_bot'),
                    ])
                    ->action(function ($record, array $data, Nutgram $bot) {
                        try {
                            if ($record->photo_file_id) {
                                // Если ID уже есть
                                $bot->sendPhoto($record->photo_file_id, chat_id: $data['tg_id'], caption: $record->message, parse_mode: ParseMode::HTML);
                            } elseif ($record->image_path) {
                                // Загружаем картинку впервые
                                $stream = fopen(Storage::disk('public')->path($record->image_path), 'r');
                                $message = $bot->sendPhoto(photo: $stream, chat_id: $data['tg_id'], caption: $record->message, parse_mode: ParseMode::HTML);
                                
                                // Сохраняем file_id для будущей массовой рассылки!
                                $photos = $message->photo;
                                if (!empty($photos)) {
                                    $record->update(['photo_file_id' => end($photos)->file_id]);
                                }
                            } else {
                                // Только текст
                                $bot->sendMessage($record->message, chat_id: $data['tg_id'], parse_mode: ParseMode::HTML);
                            }
                            Notification::make()->title('Тестовое сообщение отправлено!')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Ошибка: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                // === КНОПКА БОЕВОЙ РАССЫЛКИ ===
                Action::make('send_all')
                    ->label('Запустить')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Запустить рассылку?')
                    ->modalDescription('Сообщение будет отправлено всем пользователям. Отменить процесс будет нельзя.')
                    ->modalSubmitActionLabel('Да, отправить всем')
                    ->action(function ($record) {
                        SendTelegramBroadcast::dispatch($record);
                        Notification::make()->title('Рассылка запущена в фоновом режиме!')->success()->send();
                    })
                    ->hidden(fn ($record) => $record->status !== 'draft'), // Скрываем, если уже отправлялась
                    
                Tables\Actions\EditAction::make()
                    ->hidden(fn ($record) => $record->status !== 'draft'), // Запрещаем редактировать запущенные
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBroadcasts::route('/'),
            'create' => Pages\CreateBroadcast::route('/create'),
            'edit' => Pages\EditBroadcast::route('/{record}/edit'),
        ];
    }
}