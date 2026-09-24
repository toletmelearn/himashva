<?php

namespace App\Filament\Resources;

use App\Exports\CustomersExport;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', false);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create'),
                Forms\Components\DatePicker::make('date_of_birth'),
                Forms\Components\Select::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other']),
                Forms\Components\TextInput::make('source'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('orders_count')->counts('orders')->label('Orders'),
                Tables\Columns\TextColumn::make('total_spent')->label('Total Spent')->money('INR')
                    ->state(fn (User $record) => $record->total_spent),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('last_login_at')->dateTime()->label('Last Login'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Registered')->sortable(),
            ])
            ->filters([
                Filter::make('city')
                    ->form([Forms\Components\TextInput::make('city')])
                    ->query(fn ($query, array $data) => $query->when($data['city'], fn ($q, $v) => $q->whereHas('addresses', fn ($qq) => $qq->where('city', 'like', "%{$v}%")))),
                Filter::make('state')
                    ->form([Forms\Components\TextInput::make('state')])
                    ->query(fn ($query, array $data) => $query->when($data['state'], fn ($q, $v) => $q->whereHas('addresses', fn ($qq) => $qq->where('state', 'like', "%{$v}%")))),
                Filter::make('order_count_range')
                    ->form([
                        Forms\Components\TextInput::make('min_orders')->numeric(),
                        Forms\Components\TextInput::make('max_orders')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_orders'], fn ($q, $v) => $q->has('orders', '>=', $v))
                            ->when($data['max_orders'], fn ($q, $v) => $q->has('orders', '<=', $v));
                    }),
                Filter::make('total_spent_range')
                    ->form([
                        Forms\Components\TextInput::make('min_spent')->numeric(),
                        Forms\Components\TextInput::make('max_spent')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_spent'], fn ($q, $v) => $q->whereRaw(
                                '(select coalesce(sum(total), 0) from orders where orders.user_id = users.id and orders.payment_status = "paid") >= ?',
                                [$v]
                            ))
                            ->when($data['max_spent'], fn ($q, $v) => $q->whereRaw(
                                '(select coalesce(sum(total), 0) from orders where orders.user_id = users.id and orders.payment_status = "paid") <= ?',
                                [$v]
                            ));
                    }),
                Filter::make('registered_range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    }),
                SelectFilter::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other']),
                Filter::make('source')
                    ->form([Forms\Components\TextInput::make('source')])
                    ->query(fn ($query, array $data) => $query->when($data['source'], fn ($q, $v) => $q->where('source', 'like', "%{$v}%"))),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export to Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn ($records) => Excel::download(new CustomersExport($records->pluck('id')->toArray()), 'customers.xlsx')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
            AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
