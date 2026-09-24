<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayResource\Pages;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static array $methodOptions = [
        'upi' => 'UPI',
        'card' => 'Card',
        'netbanking' => 'Netbanking',
        'wallet' => 'Wallet',
        'emi' => 'EMI',
        'cod' => 'Cash on Delivery',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Section::make('Gateway Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('e.g. razorpay, payu, cashfree, instamojo, cod, stripe'),
                        Forms\Components\TextInput::make('display_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('driver')->required()->maxLength(255)
                            ->helperText('Driver key used by PaymentGatewayManager, e.g. razorpay / cod'),
                        Forms\Components\TextInput::make('icon')->maxLength(255),
                        Forms\Components\Textarea::make('description')->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Credentials')
                    ->columns(1)
                    ->hidden(fn (Forms\Get $get) => $get('driver') === 'cod')
                    ->schema([
                        Forms\Components\KeyValue::make('credentials')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->helperText('e.g. key_id / key_secret. Stored encrypted.'),
                    ]),

                Forms\Components\Section::make('Configuration')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')->default(false),
                        Forms\Components\Toggle::make('is_test_mode')->default(true),
                        Forms\Components\CheckboxList::make('supported_methods')
                            ->options(self::$methodOptions)
                            ->columns(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('min_order_amount')->numeric()->prefix('₹'),
                        Forms\Components\TextInput::make('max_order_amount')->numeric()->prefix('₹'),
                        Forms\Components\TextInput::make('display_order')->numeric()->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')->searchable(),
                Tables\Columns\TextColumn::make('driver')->badge(),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('is_test_mode')
                    ->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Test' : 'Live')
                    ->color(fn (bool $state) => $state ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('supported_methods')
                    ->badge()
                    ->separator(','),
                Tables\Columns\TextColumn::make('display_order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentGateways::route('/'),
            'create' => Pages\CreatePaymentGateway::route('/create'),
            'edit' => Pages\EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}
