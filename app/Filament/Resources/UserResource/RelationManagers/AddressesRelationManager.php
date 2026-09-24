<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('label')->required(),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('phone')->required(),
                Forms\Components\TextInput::make('address_line_1')->required()->columnSpanFull(),
                Forms\Components\TextInput::make('address_line_2')->columnSpanFull(),
                Forms\Components\TextInput::make('city')->required(),
                Forms\Components\TextInput::make('state')->required(),
                Forms\Components\TextInput::make('postal_code')->required(),
                Forms\Components\TextInput::make('country')->required()->default('India'),
                Forms\Components\Toggle::make('is_default'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('label'),
                Tables\Columns\TextColumn::make('city'),
                Tables\Columns\TextColumn::make('state'),
                Tables\Columns\TextColumn::make('postal_code'),
                Tables\Columns\IconColumn::make('is_default')->boolean(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
