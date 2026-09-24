<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnResource\Pages;
use App\Filament\Resources\ReturnResource\RelationManagers\ItemsRelationManager;
use App\Models\ReturnRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReturnResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?string $navigationLabel = 'Returns';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = ReturnRequest::where('status', 'requested')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Returns originate from the customer's "Request Return" action on the
     * storefront, not from the admin panel.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Return Request')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('order.order_number')->label('Order')->disabled(),
                        Forms\Components\TextInput::make('user.name')->label('Customer')->disabled(),
                        Forms\Components\Select::make('reason_category')
                            ->options([
                                'defective' => 'Defective', 'wrong_item' => 'Wrong Item',
                                'not_as_described' => 'Not as Described', 'changed_mind' => 'Changed Mind',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->disabled(),
                        Forms\Components\Textarea::make('reason_text')->columnSpanFull()->disabled(),
                    ]),
                Forms\Components\Section::make('Processing')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected',
                                'received' => 'Received', 'refunded' => 'Refunded',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('refund_amount')->numeric()->prefix('₹'),
                        Forms\Components\Textarea::make('admin_notes')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')->label('Order')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Customer')->searchable(),
                Tables\Columns\TextColumn::make('reason_category')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'refunded' => 'success', 'rejected' => 'danger',
                    'requested' => 'gray', default => 'info',
                }),
                Tables\Columns\TextColumn::make('refund_amount')->money('INR'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => 'Requested', 'approved' => 'Approved', 'rejected' => 'Rejected',
                        'received' => 'Received', 'refunded' => 'Refunded',
                    ]),
                SelectFilter::make('reason_category')
                    ->options([
                        'defective' => 'Defective', 'wrong_item' => 'Wrong Item',
                        'not_as_described' => 'Not as Described', 'changed_mind' => 'Changed Mind',
                        'other' => 'Other',
                    ]),
                SelectFilter::make('order_id')
                    ->relationship('order', 'order_number')
                    ->searchable()
                    ->label('Order'),
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
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturns::route('/'),
            'edit' => Pages\EditReturn::route('/{record}/edit'),
        ];
    }
}
