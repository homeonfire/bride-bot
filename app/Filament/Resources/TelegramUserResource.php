<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramUserResource\Pages;
use App\Filament\Resources\TelegramUserResource\RelationManagers;
use App\Models\TelegramUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TelegramUserResource extends Resource
{
    protected static ?string $model = TelegramUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; // Иконка группы людей
    protected static ?string $navigationLabel = 'Пользователи бота'; // Название в левом меню
    protected static ?string $pluralModelLabel = 'Пользователи бота';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('telegram_id')
                    ->label('ID Telegram')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Прячем по умолчанию, чтобы не засорять экран
                    
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Имя')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Фамилия')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('username')
                    ->label('Юзернейм')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ? "@{$state}" : '-'), // Красиво подставляем @
                    
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean(), // Будет красивая галочка или крестик
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Когда зашел')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Сортируем: новые пользователи всегда сверху
            ->filters([
                // Здесь в будущем можно добавить фильтры (например, показать только Premium)
            ])
            ->actions([
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
            'index' => Pages\ListTelegramUsers::route('/'),
            'create' => Pages\CreateTelegramUser::route('/create'),
            'edit' => Pages\EditTelegramUser::route('/{record}/edit'),
        ];
    }
}
