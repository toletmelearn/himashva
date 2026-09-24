<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LowStockAlertCommand extends Command
{
    protected $signature = 'himashva:low-stock-alert';

    protected $description = 'Email the admin a summary of active products at or below their low-stock threshold';

    public function handle(): int
    {
        $products = Product::query()
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('status', 'active')
            ->orderBy('stock')
            ->get(['name', 'sku', 'stock', 'low_stock_threshold']);

        if ($products->isEmpty()) {
            $this->info('No low-stock products found.');

            return self::SUCCESS;
        }

        $adminEmail = settings('contact_email');

        if (blank($adminEmail)) {
            $this->warn('No contact_email configured in Site Settings — skipping.');

            return self::FAILURE;
        }

        $lines = $products->map(
            fn (Product $product) => "- {$product->name} (SKU: {$product->sku}): {$product->stock} left, threshold {$product->low_stock_threshold}"
        )->implode("\n");

        $body = "The following active products are at or below their low-stock threshold:\n\n{$lines}";

        Mail::raw($body, function ($message) use ($adminEmail, $products) {
            $message->to($adminEmail)->subject("Low Stock Alert: {$products->count()} product(s) need attention");
        });

        $this->info("Low stock alert sent for {$products->count()} product(s).");

        return self::SUCCESS;
    }
}
