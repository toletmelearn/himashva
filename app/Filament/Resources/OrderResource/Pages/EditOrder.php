<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Mail\OrderDelivered;
use App\Mail\OrderShipped;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $original = $this->record->getOriginal('order_status');
        $new = $this->data['order_status'] ?? $original;

        if ($original !== $new) {
            $this->record->statusHistory()->create([
                'from_status' => $original,
                'to_status' => $new,
                'changed_by' => Filament::auth()->id(),
            ]);

            if ($new === 'delivered') {
                $this->data['delivered_at'] = now();
            }

            if ($new === 'cancelled') {
                $this->data['cancelled_at'] = now();

                if ($original !== 'cancelled') {
                    app(OrderService::class)->restockCancelledOrder($this->record, Filament::auth()->id());
                }
            }

            if (settings('send_order_emails', true)) {
                if ($new === 'shipped') {
                    Mail::to($this->record->email)->send(new OrderShipped($this->record));
                }

                if ($new === 'delivered') {
                    Mail::to($this->record->email)->send(new OrderDelivered($this->record));
                }
            }
        }
    }
}
