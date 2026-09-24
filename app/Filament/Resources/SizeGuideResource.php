<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SizeGuideResource\Pages;
use App\Models\SizeGuide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SizeGuideResource extends Resource
{
    protected static ?string $model = SizeGuide::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Section::make('Basic Info')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('type')
                            ->options(['table' => 'Table', 'image' => 'Image', 'both' => 'Table & Image'])
                            ->default('table')
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('measurement_unit')
                            ->options(['cm' => 'cm', 'in' => 'in', 'kg' => 'kg', 'g' => 'g', 'hrs' => 'hrs'])
                            ->default('cm')
                            ->required(),
                        Forms\Components\Toggle::make('is_active')->default(true),
                        Forms\Components\Textarea::make('description')->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Size Chart')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TagsInput::make('table_headers')
                            ->label('Column Headers')
                            ->placeholder('e.g. Diameter, Height, Burn Time')
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('table_rows')
                            ->label('Rows (one row per line, comma-separated)')
                            ->placeholder("7cm, 8cm, 40hrs\n9cm, 10cm, 55hrs")
                            ->rows(5)
                            ->dehydrated(false),
                    ])
                    ->afterStateHydrated(function (Forms\Set $set, $record) {
                        if (! $record) {
                            return;
                        }

                        $data = $record->table_data ?? [];
                        $set('table_headers', $data['headers'] ?? []);
                        $rows = $data['rows'] ?? [];
                        $set('table_rows', collect($rows)->map(fn ($row) => implode(', ', $row))->implode("\n"));
                    }),

                Forms\Components\Section::make('Image')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->image()
                            ->directory('size-guides')
                            ->disk('public'),
                    ])
                    ->hidden(fn (Forms\Get $get) => ! in_array($get('type'), ['image', 'both'])),

                Forms\Components\Select::make('products')
                    ->relationship('products', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Parses the TagsInput headers + comma-separated row textarea into the
     * table_data JSON shape {"headers": [...], "rows": [[...], ...]}.
     * Shared by CreateSizeGuide and EditSizeGuide before save.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function buildTableData(array $data): array
    {
        $headers = $data['table_headers'] ?? [];
        $rowsText = $data['table_rows'] ?? '';

        $rows = collect(explode("\n", (string) $rowsText))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->map(fn ($line) => array_map('trim', explode(',', $line)))
            ->values()
            ->toArray();

        if (filled($headers) || filled($rows)) {
            $data['table_data'] = ['headers' => $headers, 'rows' => $rows];
        }

        unset($data['table_headers'], $data['table_rows']);

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('measurement_unit'),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->label('Products'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSizeGuides::route('/'),
            'create' => Pages\CreateSizeGuide::route('/create'),
            'edit' => Pages\EditSizeGuide::route('/{record}/edit'),
        ];
    }
}
