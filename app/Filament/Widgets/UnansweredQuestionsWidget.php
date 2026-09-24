<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UnansweredQuestionResource;
use App\Models\UnansweredQuestion;
use Filament\Widgets\Widget;

class UnansweredQuestionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.unanswered-questions-widget';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 1;

    public function getViewData(): array
    {
        return [
            'count' => UnansweredQuestion::pending()->count(),
            'url' => UnansweredQuestionResource::getUrl(),
        ];
    }

    public static function canView(): bool
    {
        return UnansweredQuestion::pending()->count() > 0;
    }
}
