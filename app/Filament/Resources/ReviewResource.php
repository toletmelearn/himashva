<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $count = Review::where('is_approved', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')->relationship('product', 'name')->disabled(),
                Forms\Components\Select::make('user_id')->relationship('user', 'name')->disabled(),
                Forms\Components\TextInput::make('rating')->numeric()->disabled(),
                Forms\Components\TextInput::make('title')->disabled(),
                Forms\Components\Textarea::make('comment')->disabled()->columnSpanFull(),
                Forms\Components\Toggle::make('is_approved'),
                Forms\Components\Toggle::make('is_verified_purchase')->disabled(),
                Forms\Components\Textarea::make('admin_reply')->columnSpanFull(),
                Forms\Components\Repeater::make('media')
                    ->relationship('media')
                    ->schema([
                        Forms\Components\Placeholder::make('preview')
                            ->label('')
                            ->content(fn (?Review $record, $state, $get) => new HtmlString(
                                $get('type') === 'video'
                                    ? '<video src="'.Storage::url($get('file_path')).'" controls class="w-32 h-32 object-cover rounded"></video>'
                                    : '<img src="'.Storage::url($get('file_path')).'" class="w-32 h-32 object-cover rounded">'
                            )),
                        Forms\Components\TextInput::make('file_name')->disabled(),
                        Forms\Components\Toggle::make('is_approved')->label('Approved'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('media'))
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->searchable(),
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('rating'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\IconColumn::make('is_approved')->boolean(),
                Tables\Columns\IconColumn::make('is_verified_purchase')->boolean()->label('Verified'),
                Tables\Columns\TextColumn::make('media_count')
                    ->label('Media')
                    ->getStateUsing(function (Review $record) {
                        $photos = $record->media->where('type', 'image')->count();
                        $videos = $record->media->where('type', 'video')->count();

                        if ($photos === 0 && $videos === 0) {
                            return 'No media';
                        }

                        $parts = [];
                        if ($photos > 0) {
                            $parts[] = "{$photos} ".Str::plural('photo', $photos);
                        }
                        if ($videos > 0) {
                            $parts[] = "{$videos} ".Str::plural('video', $videos);
                        }

                        return implode(', ', $parts);
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved'),
                Tables\Filters\SelectFilter::make('rating')
                    ->options([1 => '1 star', 2 => '2 stars', 3 => '3 stars', 4 => '4 stars', 5 => '5 stars']),
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->label('Product'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->action(fn (Review $record) => $record->update(['is_approved' => true]))
                    ->visible(fn (Review $record) => ! $record->is_approved),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
