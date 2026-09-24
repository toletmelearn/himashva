<?php

namespace App\Exports;

use App\Models\NewsletterSubscriber;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NewsletterExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return NewsletterSubscriber::query()->get()->map(fn (NewsletterSubscriber $s) => [
            $s->email,
            $s->name,
            $s->is_active ? 'Active' : 'Inactive',
            $s->subscribed_at?->format('Y-m-d H:i'),
        ]);
    }

    public function headings(): array
    {
        return ['Email', 'Name', 'Status', 'Subscribed At'];
    }
}
