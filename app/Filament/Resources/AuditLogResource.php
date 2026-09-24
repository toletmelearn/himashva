<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Read-only. Nothing writes to audit_logs except the Auditable trait's
 * model observers — this resource exists purely so admins can see the
 * trail, not to edit it.
 */
class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Audit Log';

    protected static ?int $navigationSort = 1;

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
                Forms\Components\TextInput::make('user.name')->label('Actor')->disabled(),
                Forms\Components\TextInput::make('action')->disabled(),
                Forms\Components\TextInput::make('auditable_type')->label('Entity type')->disabled(),
                Forms\Components\TextInput::make('auditable_id')->label('Entity ID')->disabled(),
                Forms\Components\TextInput::make('ip_address')->disabled(),
                Forms\Components\Textarea::make('before')->disabled()->columnSpanFull()
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null),
                Forms\Components\Textarea::make('after')->disabled()->columnSpanFull()
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : null),
                Forms\Components\DateTimePicker::make('created_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Actor')->default('System'),
                Tables\Columns\TextColumn::make('action')->badge()->color(fn (string $state) => match ($state) {
                    'created' => 'success', 'deleted' => 'danger', default => 'info',
                }),
                Tables\Columns\TextColumn::make('auditable_type')->label('Entity')
                    ->formatStateUsing(fn (string $state) => class_basename($state)),
                Tables\Columns\TextColumn::make('auditable_id')->label('ID'),
                Tables\Columns\TextColumn::make('ip_address'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->options(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted']),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->label('Actor'),
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
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
