<?php

namespace App\Filament\Resources;

use App\Exports\OrdersExport;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\StatusHistoryRelationManager;
use App\Models\Order;
use App\Services\Shipping\ShippingManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('order_status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * True for super_admin/legacy admins and users with the broad
     * orders.manage permission. False for staff who only have
     * orders.update_status — their form fields other than order_status
     * are locked read-only (see form() below).
     */
    protected static function canManageFullOrder(): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->hasRole('super_admin') || $user->can('orders.manage'));
    }

    public static function form(Form $form): Form
    {
        $lockedForStaff = fn () => ! static::canManageFullOrder();

        return $form
            ->schema([
                Forms\Components\Section::make('Customer & Address')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('order_number')->disabled(),
                        Forms\Components\TextInput::make('name')->required()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('email')->email()->required()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('phone')->required()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('address_line_1')->required()->columnSpanFull()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('address_line_2')->columnSpanFull()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('city')->required()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('state')->required()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('postal_code')->required()->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('country')->required()->disabled($lockedForStaff),
                    ]),
                Forms\Components\Section::make('Payment')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('payment_method')->disabled(),
                        Forms\Components\Select::make('payment_status')
                            ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'])
                            ->required()
                            ->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('payment_id')->disabled(),
                    ]),
                Forms\Components\Section::make('Order Status & Tracking')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('order_status')
                            ->options([
                                'pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing',
                                'shipped' => 'Shipped', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled', 'returned' => 'Returned', 'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('tracking_number')->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('tracking_url')->disabled($lockedForStaff),
                        Forms\Components\TextInput::make('shipping_partner')->disabled($lockedForStaff),
                        Forms\Components\DatePicker::make('estimated_delivery')->disabled($lockedForStaff),
                        Forms\Components\Textarea::make('cancellation_reason')->columnSpanFull()->disabled($lockedForStaff),
                        Forms\Components\Textarea::make('admin_notes')->columnSpanFull()->disabled($lockedForStaff),
                    ]),
                Forms\Components\Section::make('Totals')
                    ->columns(5)
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')->numeric()->prefix('₹')->disabled(),
                        Forms\Components\TextInput::make('discount_amount')->numeric()->prefix('₹')->disabled(),
                        Forms\Components\TextInput::make('shipping_amount')->numeric()->prefix('₹')->disabled(),
                        Forms\Components\TextInput::make('tax_amount')->label('GST')->numeric()->prefix('₹')->disabled(),
                        Forms\Components\TextInput::make('total')->numeric()->prefix('₹')->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('total')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('payment_status')->badge()->color(fn (string $state) => match ($state) {
                    'paid' => 'success', 'pending' => 'warning', 'failed' => 'danger', 'refunded' => 'gray',
                }),
                Tables\Columns\TextColumn::make('order_status')->badge()->color(fn (string $state) => match ($state) {
                    'delivered' => 'success', 'cancelled', 'returned', 'refunded' => 'danger',
                    'pending' => 'gray', default => 'info',
                }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('order_status')
                    ->options([
                        'pending' => 'Pending', 'confirmed' => 'Confirmed', 'processing' => 'Processing',
                        'shipped' => 'Shipped', 'out_for_delivery' => 'Out for Delivery', 'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled', 'returned' => 'Returned', 'refunded' => 'Refunded',
                    ]),
                SelectFilter::make('payment_status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded']),
                SelectFilter::make('payment_method')
                    ->options(['cod' => 'Cash on Delivery', 'razorpay' => 'Razorpay', 'upi' => 'UPI']),
                Filter::make('shipping_partner')
                    ->form([Forms\Components\TextInput::make('shipping_partner')])
                    ->query(fn ($query, array $data) => $query->when($data['shipping_partner'], fn ($q, $v) => $q->where('shipping_partner', 'like', "%{$v}%"))),
                SelectFilter::make('coupon_id')
                    ->relationship('coupon', 'code')
                    ->label('Coupon'),
                Filter::make('total_range')
                    ->form([
                        Forms\Components\TextInput::make('total_from')->numeric(),
                        Forms\Components\TextInput::make('total_to')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['total_from'], fn ($q, $v) => $q->where('total', '>=', $v))
                            ->when($data['total_to'], fn ($q, $v) => $q->where('total', '<=', $v));
                    }),
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
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Order $record) => route('admin.orders.invoice', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('create_shipment')
                    ->label('Create Shipment')
                    ->icon('heroicon-o-truck')
                    ->visible(fn (Order $record) => blank($record->tracking_number))
                    ->action(function (Order $record) {
                        $manager = app(ShippingManager::class);
                        $provider = $manager->activeProvider();

                        if (! $provider) {
                            Notification::make()->title('No active shipping provider')->status('danger')->send();

                            return;
                        }

                        $result = $manager->driver($provider->driver)->createShipment($record, $provider);

                        if ($result->success) {
                            $record->update([
                                'tracking_number' => $result->awbNumber ?? $result->shipmentId,
                                'tracking_url' => $result->trackingUrl,
                                'shipping_partner' => $provider->display_name,
                            ]);
                            Notification::make()->title('Shipment created')->status('success')->send();
                        } else {
                            Notification::make()->title('Shipment creation failed')->status('danger')->send();
                        }
                    }),
                Tables\Actions\Action::make('track_shipment')
                    ->label('Track Shipment')
                    ->icon('heroicon-o-map-pin')
                    ->visible(fn (Order $record) => filled($record->tracking_number))
                    ->action(function (Order $record) {
                        $manager = app(ShippingManager::class);
                        $provider = $manager->activeProvider();

                        if (! $provider) {
                            return;
                        }

                        $tracking = $manager->driver($provider->driver)->getTracking($record->tracking_number, $provider);

                        Notification::make()
                            ->title('Tracking status: '.($tracking->status ?? 'Unknown'))
                            ->send();
                    }),
                Tables\Actions\Action::make('print_label')
                    ->label('Print Label')
                    ->icon('heroicon-o-printer')
                    ->visible(fn (Order $record) => filled($record->tracking_number))
                    ->url(function (Order $record) {
                        $manager = app(ShippingManager::class);
                        $provider = $manager->activeProvider();

                        if (! $provider) {
                            return null;
                        }

                        return $manager->driver($provider->driver)->getLabel($record->tracking_number, $provider);
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export to Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn ($records) => Excel::download(new OrdersExport($records->pluck('id')->toArray()), 'orders.xlsx')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
            StatusHistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
