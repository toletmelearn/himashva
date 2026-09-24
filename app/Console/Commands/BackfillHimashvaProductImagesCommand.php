<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

#[Signature('himashva:backfill-product-images {--file=database/data/himashva_products.json}')]
#[Description('Download the primary image for HIMASHVA products imported without one (PHP curl on this box lacks a CA bundle, so this shells out to the system curl instead)')]
class BackfillHimashvaProductImagesCommand extends Command
{
    public function handle(): int
    {
        $path = base_path($this->option('file'));

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $items = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $storageRoot = storage_path('app/public/products');

        if (! is_dir($storageRoot)) {
            mkdir($storageRoot, 0755, recursive: true);
        }

        $downloaded = 0;
        $failed = 0;

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $url = $item['productImageUrl'] ?? null;

            if ($name === '' || ! $url) {
                continue;
            }

            $product = Product::query()->where('name', $name)->first();

            if (! $product || $product->images()->exists()) {
                continue;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
            $relativePath = "products/{$product->slug}.{$extension}";
            $absolutePath = storage_path("app/public/{$relativePath}");

            $result = Process::run(['curl', '-sf', '-o', $absolutePath, $url]);

            if (! $result->successful() || ! is_file($absolutePath) || filesize($absolutePath) === 0) {
                $failed++;
                $this->warn("Failed: {$product->name}");

                continue;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $relativePath,
                'image_type' => 'real',
                'alt_text' => $product->name,
                'sort_order' => 0,
                'is_primary' => true,
            ]);

            $downloaded++;
        }

        $this->info("Downloaded {$downloaded} image(s), failed {$failed}.");

        return self::SUCCESS;
    }
}
