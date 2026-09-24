<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('e.g. WELCOME10'),
                Forms\Components\Select::make('type')
                    ->options(['percentage' => 'Percentage', 'fixed' => 'Fixed Amount'])
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('value')
                    ->numeric()
                    ->required()
                    ->suffix(fn (Forms\Get $get) => $get('type') === 'percentage' ? '%' : '₹'),
                Forms\Components\TextInput::make('min_order_amount')->numeric()->prefix('₹'),
                Forms\Components\TextInput::make('max_discount_amount')->numeric()->prefix('₹'),
                Forms\Components\TextInput::make('max_uses')->numeric(),
                Forms\Components\TextInput::make('per_user_limit')->numeric()->default(1),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('value'),
                Tables\Columns\TextColumn::make('used_count'),
                Tables\Columns\TextColumn::make('max_uses'),
                Tables\Columns\TextColumn::make('expires_at')->dateTime(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
                SelectFilter::make('type')
                    ->options(['percentage' => 'Percentage', 'fixed' => 'Fixed Amount']),
                Filter::make('validity')
                    ->form([
                        Forms\Components\Select::make('state')
                            ->options(['active' => 'Currently Active', 'expired' => 'Expired']),
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['state'] ?? null) {
                            'active' => $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now())),
                            'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<', now()),
                            default => $query,
                        };
                    }),
                Filter::make('starts_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('starts_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('starts_at', '<=', $v));
                    }),
                Filter::make('expires_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('expires_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('expires_at', '<=', $v));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
