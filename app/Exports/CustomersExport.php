<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $userIds = []) {}

    public function collection()
    {
        $query = User::query()->where('is_admin', false);

        if (! empty($this->userIds)) {
            $query->whereIn('id', $this->userIds);
        }

        return $query->get()->map(fn (User $user) => [
            $user->name,
            $user->email,
            $user->phone,
            $user->gender,
            $user->order_count,
            $user->total_spent,
            $user->last_login_at?->format('Y-m-d H:i'),
            $user->created_at->format('Y-m-d H:i'),
        ]);
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Phone', 'Gender', 'Order Count', 'Total Spent', 'Last Login', 'Registered'];
    }
}
