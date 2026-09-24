<?php

namespace App\Filament\Resources\UnansweredQuestionResource\Pages;

use App\Filament\Resources\UnansweredQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListUnansweredQuestions extends ListRecords
{
    protected static string $resource = UnansweredQuestionResource::class;
}
