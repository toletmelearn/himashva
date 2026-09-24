<?php

namespace App\Filament\Resources\ReturnResource\Pages;

use App\Filament\Resources\ReturnResource;
use App\Mail\ReturnStatusUpdate;
use App\Services\InventoryService;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditReturn extends EditRecord
{
    protected static string $resource = ReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $original = $this->record->getOriginal('status');
        $new = $this->data['status'] ?? $original;

        if ($original !== $new && settings('send_order_emails', true) && $this->record->order?->email) {
            $this->record->setAttribute('status', $new);
            Mail::to($this->record->order->email)->send(new ReturnStatusUpdate($this->record));
        }

        if ($original !== 'received' && $new === 'received') {
            $inventory = app(InventoryService::class);
            $actorId = Filament::auth()->id();

            $this->record->items()->with(['orderItem.product', 'orderItem.variant'])->get()
                ->each(function ($returnItem) use ($inventory, $actorId) {
                    $product = $returnItem->orderItem?->product;

                    if (! $product) {
                        return;
                    }

                    $inventory->recordMovement(
                        $product,
                        'return',
                        $returnItem->quantity,
                        'Return #'.$this->record->id.' received',
                        $actorId,
                        $this->record,
                        $returnItem->orderItem->variant,
                    );
                });
        }
    }
}
