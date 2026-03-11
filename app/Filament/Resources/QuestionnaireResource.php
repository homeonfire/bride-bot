<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionnaireResource\Pages;
use App\Models\Questionnaire;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionnaireResource extends Resource
{
    protected static ?string $model = Questionnaire::class;

    // Иконка в боковом меню админки
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    
    // Название в меню
    protected static ?string $navigationLabel = 'Анкеты невест';
    protected static ?string $modelLabel = 'Анкету';
    protected static ?string $pluralModelLabel = 'Анкеты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('user_id')
                            ->label('Telegram ID')
                            ->required()
                            ->readOnly(), // ID пользователя менять не стоит
                        Forms\Components\TextInput::make('name')
                            ->label('Имя')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('age')
                            ->label('Возраст')
                            ->numeric(),
                        Forms\Components\TextInput::make('height')
                            ->label('Рост (см)')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Section::make('Семья и локация')
                    ->schema([
                        Forms\Components\Toggle::make('has_kids')
                            ->label('Есть дети?'),
                        Forms\Components\TextInput::make('wants_kids')
                            ->label('Хочет детей?')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('Город / Страна')
                            ->maxLength(255)->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('О себе')
                    ->schema([
                        Forms\Components\TextInput::make('occupation')
                            ->label('Профессия')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hobbies')
                            ->label('Хобби')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('about_me')
                            ->label('Кратко о себе')
                            ->rows(3)->columnSpanFull(),
                        Forms\Components\Textarea::make('man_qualities')
                            ->label('Качества мужчины')
                            ->rows(3)->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Контакты (Скрыто от всех)')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel(),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('WhatsApp'),
                        Forms\Components\TextInput::make('telegram_username')
                            ->label('Telegram (@username)'),
                        Forms\Components\TextInput::make('instagram')
                            ->label('Instagram'),
                    ])->columns(2),

                Forms\Components\Section::make('Статус и Медиа')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Опубликована в канале?')
                            ->onColor('success')
                            ->offColor('danger'),
                        Forms\Components\TagsInput::make('photos')
                            ->label('ID Фотографий (из Telegram)')
                            ->placeholder('Тут хранятся ID фоток')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Сгенерированные переводы (JSON)')
                    ->schema([
                        Forms\Components\KeyValue::make('en_text')
                            ->label('Английская версия (от ИИ)')
                            ->keyLabel('Поле')
                            ->valueLabel('Перевод')
                            ->columnSpanFull(),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('age')
                    ->label('Возраст')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Локация')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telegram_username')
                    ->label('Telegram')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Опубликована')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата регистрации')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Фильтр: показывать только опубликованные или неопубликованные
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Статус публикации'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionnaires::route('/'),
            'create' => Pages\CreateQuestionnaire::route('/create'),
            'edit' => Pages\EditQuestionnaire::route('/{record}/edit'),
        ];
    }
}