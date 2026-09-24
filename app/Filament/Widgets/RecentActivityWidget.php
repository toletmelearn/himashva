<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    protected static bool $isLazy = false;

    /**
     * @return array{activities: array<int, array{icon: string, color: string, text: string, time: string}>}
     */
    protected function getViewData(): array
    {
        $activities = AuditLog::query()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (AuditLog $log) => [
                'icon' => match ($log->action) {
                    'created' => 'heroicon-o-plus',
                    'updated' => 'heroicon-o-pencil',
                    'deleted' => 'heroicon-o-trash',
                    default => 'heroicon-o-information-circle',
                },
                'color' => match ($log->action) {
                    'created' => '#10B981',
                    'updated' => '#3B82F6',
                    'deleted' => '#EF4444',
                    default => '#9C8E80',
                },
                'text' => sprintf(
                    '%s %s %s #%d',
                    $log->user?->name ?? 'System',
                    $log->action,
                    class_basename($log->auditable_type),
                    $log->auditable_id
                ),
                'time' => $log->created_at->diffForHumans(),
            ]);

        return [
            'activities' => $activities->toArray(),
        ];
    }
}
