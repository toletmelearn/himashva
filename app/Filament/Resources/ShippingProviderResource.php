<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingProviderResource\Pages;
use App\Models\ShippingProvider;
use App\Services\Shipping\ShippingManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ShippingProviderResource extends Resource
{
    protected static ?string $model = ShippingProvider::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static array $featureOptions = [
        'tracking' => 'Tracking',
        'label_generation' => 'Label Generation',
        'pincode_check' => 'Pincode Check',
        'rate_calculation' => 'Rate Calculation',
        'auto_shipment' => 'Auto Shipment',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Section::make('Provider Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('e.g. shiprocket, delhivery, dtdc, bluedart, manual'),
                        Forms\Components\TextInput::make('display_name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('driver')->required()->maxLength(255),
                        Forms\Components\Textarea::make('description')->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Credentials')
                    ->hidden(fn (Forms\Get $get) => $get('driver') === 'manual')
                    ->schema([
                        Forms\Components\KeyValue::make('credentials')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->helperText('e.g. email / password. Stored encrypted.'),
                    ]),

                Forms\Components\Section::make('Settings')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('settings.pickup_pincode')->label('Pickup Pincode'),
                        Forms\Components\TextInput::make('settings.pickup_address')->label('Pickup Address'),
                        Forms\Components\TextInput::make('settings.default_weight_grams')->numeric()->label('Default Weight (g)'),
                        Forms\Components\TextInput::make('settings.default_length_cm')->numeric()->label('Default Length (cm)'),
                        Forms\Components\TextInput::make('settings.default_breadth_cm')->numeric()->label('Default Breadth (cm)'),
                        Forms\Components\TextInput::make('settings.default_height_cm')->numeric()->label('Default Height (cm)'),
                        Forms\Components\TextInput::make('settings.flat_rate')->numeric()->prefix('₹'),
                        Forms\Components\TextInput::make('settings.free_above')->numeric()->prefix('₹'),
                    ]),

                Forms\Components\Section::make('Configuration')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')->default(false),
                        Forms\Components\Toggle::make('is_test_mode')->default(true),
                        Forms\Components\CheckboxList::make('supported_features')
                            ->options(self::$featureOptions)
                            ->columns(3)
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('display_order')->sortable(),
            ])
            ->defaultSort('display_order')
            ->actions([
                Action::make('test_connection')
                    ->label('Test Connection')
                    ->icon('heroicon-o-signal')
                    ->action(function (ShippingProvider $record) {
                        $ok = app(ShippingManager::class)->driver($record->driver)->authenticate($record);

                        Notification::make()
                            ->title($ok ? 'Connection successful' : 'Connection failed')
                            ->status($ok ? 'success' : 'danger')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingProviders::route('/'),
            'create' => Pages\CreateShippingProvider::route('/create'),
            'edit' => Pages\EditShippingProvider::route('/{record}/edit'),
        ];
    }
}
