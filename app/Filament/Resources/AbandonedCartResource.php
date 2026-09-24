<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbandonedCartResource\Pages;
use App\Models\AbandonedCart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AbandonedCartResource extends Resource
{
    protected static ?string $model = AbandonedCart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->state(fn (AbandonedCart $record) => count($record->cart_data ?? [])),
                Tables\Columns\TextColumn::make('total')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'active' => 'warning',
                    'reminded' => 'info',
                    'recovered' => 'success',
                    'expired' => 'gray',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('reminder_count')->label('Reminders'),
                Tables\Columns\TextColumn::make('recoveredOrder.order_number')
                    ->label('Recovered Order')
                    ->placeholder('—')
                    ->url(fn (AbandonedCart $record) => $record->recovered_order_id
                        ? OrderResource::getUrl('edit', ['record' => $record->recovered_order_id])
                        : null),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'reminded' => 'Reminded',
                        'recovered' => 'Recovered',
                        'expired' => 'Expired',
                    ]),
                TernaryFilter::make('has_email')
                    ->label('Has Email')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email'),
                        false: fn ($query) => $query->whereNull('email'),
                    ),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAbandonedCarts::route('/'),
            'view' => Pages\ViewAbandonedCart::route('/{record}'),
        ];
    }
}
