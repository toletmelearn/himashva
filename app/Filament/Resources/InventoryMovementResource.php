<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Read-only. Nothing writes to inventory_movements except InventoryService
 * — this resource exists purely so admins can see stock history, not to
 * edit it.
 */
class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?string $navigationLabel = 'Stock History';

    protected static ?int $navigationSort = 3;

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
        return $form
            ->schema([
                Forms\Components\TextInput::make('product.name')->label('Product')->disabled(),
                Forms\Components\TextInput::make('variant.name')->label('Variant')->disabled(),
                Forms\Components\TextInput::make('type')->disabled(),
                Forms\Components\TextInput::make('quantity_delta')->label('Quantity Change')->disabled(),
                Forms\Components\Textarea::make('reason')->disabled()->columnSpanFull(),
                Forms\Components\TextInput::make('actor.name')->label('Actor')->disabled(),
                Forms\Components\TextInput::make('reference_type')->disabled(),
                Forms\Components\TextInput::make('reference_id')->disabled(),
                Forms\Components\DateTimePicker::make('created_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('product.name')->label('Product')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('variant.name')->label('Variant')->default('—'),
                Tables\Columns\TextColumn::make('type')->badge()->color(fn (string $state) => match ($state) {
                    'purchase', 'return' => 'success',
                    'sale' => 'info',
                    'adjustment' => 'warning',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('quantity_delta')
                    ->label('Qty Change')
                    ->color(fn (int $state) => $state >= 0 ? 'success' : 'danger')
                    ->formatStateUsing(fn (int $state) => ($state > 0 ? '+' : '').$state),
                Tables\Columns\TextColumn::make('actor.name')->label('Actor')->default('System'),
                Tables\Columns\TextColumn::make('reason')->limit(40)->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->label('Product'),
                SelectFilter::make('type')
                    ->options([
                        'purchase' => 'Purchase', 'sale' => 'Sale',
                        'adjustment' => 'Adjustment', 'return' => 'Return',
                    ]),
                SelectFilter::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->label('Variant'),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
            'view' => Pages\ViewInventoryMovement::route('/{record}'),
        ];
    }
}
