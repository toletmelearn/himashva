<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartReminder;
use App\Models\AbandonedCart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'himashva:abandoned-cart-reminders';

    protected $description = 'Email recoverable abandoned carts a reminder to complete their purchase';

    public function handle(): int
    {
        if (! settings('abandoned_cart_recovery_enabled', false)) {
            Log::info('Abandoned cart recovery is disabled — skipping reminders.');
            $this->info('Abandoned cart recovery is disabled — skipping.');

            return self::SUCCESS;
        }

        $carts = AbandonedCart::recoverable()
            ->where('created_at', '<', now()->subHour())
            ->where(function ($query) {
                $query->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<', now()->subDay());
            })
            ->get();

        $sent = 0;

        foreach ($carts as $cart) {
            Mail::to($cart->email)->send(new AbandonedCartReminder($cart));

            $cart->increment('reminder_count');
            $cart->update([
                'reminder_sent_at' => now(),
                'status' => $cart->status === 'active' ? 'reminded' : $cart->status,
            ]);

            $sent++;
        }

        Log::info("Sent {$sent} abandoned cart reminder(s).");
        $this->info("Sent {$sent} abandoned cart reminder(s).");

        return self::SUCCESS;
    }
}
