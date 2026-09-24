<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('himashva:import-products {--file=database/data/himashva_products.json}')]
#[Description('Import the HIMASHVA storefront catalog (database/data/himashva_products.json) into categories/products/product_images')]
class ImportHimashvaProductsCommand extends Command
{
    /**
     * Keyword => category name, checked in order against the product name.
     * First match wins. Keeps existing store categories where they fit and
     * adds a few new ones for non-candle décor items in this catalog.
     *
     * @var array<string, string>
     */
    private const CATEGORY_KEYWORDS = [
        'raksha bandhan' => 'All Festive Candles',
        'rakhi' => 'All Festive Candles',
        'diwali' => 'All Festive Candles',
        'ganesh' => 'All Festive Candles',
        'independence day' => 'All Festive Candles',
        'republic day' => 'All Festive Candles',
        'tricolour' => 'All Festive Candles',
        'halloween' => 'All Festive Candles',
        'christmas' => 'All Festive Candles',
        'birthday' => 'All Festive Candles',
        'valentine' => 'All Festive Candles',
        'wedding' => 'All Festive Candles',
        'anniversary' => 'All Festive Candles',

        'combo pack' => 'Hampers & Combo',
        'gift set' => 'Hampers & Combo',
        'candle set' => 'Hampers & Combo',

        'wax sachet' => 'Wax Sachet',
        'fragrance sachet' => 'Wax Sachet',
        'wax melt' => 'Wax Melts',

        'coconut candle' => 'Coconut Shell Candles',

        'ladoo' => 'Cake & Dessert Candle',
        'mithai' => 'Cake & Dessert Candle',
        'gujiya' => 'Cake & Dessert Candle',
        'chocolate bar' => 'Cake & Dessert Candle',
        'cake candle' => 'Cake & Dessert Candle',
        'noodles shape candle' => 'Cake & Dessert Candle',
        'kaju katli' => 'Cake & Dessert Candle',

        'tulip flower candle' => 'Bouquet Candles',
        'rose flower' => 'Bouquet Candles',
        'sunflower' => 'Bouquet Candles',
        'daisy flower' => 'Bouquet Candles',
        'lotus candle' => 'Bouquet Candles',
        'chrysanthemum' => 'Bouquet Candles',
        'floral pillar candle' => 'Pillar Candles',
        'floral bloom' => 'Bouquet Candles',

        'pillar candle' => 'Pillar Candles',

        'astronaut' => 'Figure Candle',
        'buddha candle' => 'Figure Candle',
        'mermaid' => 'Figure Candle',
        'pinecone' => 'Figure Candle',
        'skull' => 'Figure Candle',
        'couple candle' => 'Figure Candle',
        'shell shaped candle' => 'Figure Candle',
        'conch shell shaped decorative candle' => 'Figure Candle',
        'volcano' => 'Figure Candle',
        'crystal rock lava' => 'Figure Candle',
        'swan candle' => 'Figure Candle',
        'starfish' => 'Figure Candle',
        'chess' => 'Figure Candle',

        'candle' => 'Pillar Candles',

        'tea light holder' => 'Candle Holders & Tealights',
        'tealight holder' => 'Candle Holders & Tealights',
        'incense holder' => 'Candle Holders & Tealights',
        'dhoop' => 'Candle Holders & Tealights',

        'tray' => 'Decorative Trays',

        'vase' => 'Vases & Planters',
        'planter' => 'Vases & Planters',

        'figurine' => 'Figurines & Showpieces',
        'showpiece' => 'Figurines & Showpieces',
        'statue' => 'Figurines & Showpieces',
        'name plate' => 'Figurines & Showpieces',

        'jar' => 'Jars & Organisers',
        'trinket box' => 'Jars & Organisers',
        'ring holder' => 'Jars & Organisers',
        'organiser' => 'Jars & Organisers',
        'organizer bowl' => 'Jars & Organisers',

        'bowl' => 'Bowls & Serveware',

        'pooja' => 'Pooja & Religious Décor',
        'kalash' => 'Pooja & Religious Décor',
        'swastik' => 'Pooja & Religious Décor',
        'tulsi vrindavan' => 'Pooja & Religious Décor',

        'keychain' => 'Keychains & Fridge Magnets',
        'fridge magnet' => 'Keychains & Fridge Magnets',

        'diy' => 'DIY Craft Kits',
        'craft kit' => 'DIY Craft Kits',
        'unpainted' => 'DIY Craft Kits',
        'paint your own' => 'DIY Craft Kits',

        'frame' => 'Home Décor',
        'chess game' => 'Home Décor',
    ];

    public function handle(): int
    {
        $path = base_path($this->option('file'));

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $items = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $this->info(sprintf('Importing %d products...', count($items)));

        $categoryCache = [];
        $imported = 0;
        $skipped = 0;

        foreach ($items as $index => $item) {
            $name = trim((string) ($item['name'] ?? ''));

            if ($name === '' || Product::query()->where('name', $name)->exists()) {
                $skipped++;

                continue;
            }

            $categoryName = $this->resolveCategory($name);
            $categoryId = $categoryCache[$categoryName] ??= Category::query()->firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'is_active' => true]
            )->id;

            $slug = $this->uniqueSlug($name);
            $mrp = (float) ($item['mrp'] ?? 0);
            $sellingPrice = (float) ($item['sellingPrice'] ?? $mrp);
            $stock = (int) ($item['currentStock'] ?? 0);
            $deactivated = (bool) ($item['deactivated'] ?? false);
            $descriptionHtml = (string) ($item['productDescription'] ?? '');
            $additionalAttributes = json_decode((string) ($item['additionalAttributes'] ?? '{}'), true) ?? [];

            $product = Product::create([
                'category_id' => $categoryId,
                'name' => $name,
                'slug' => $slug,
                'short_description' => Str::limit(trim(strip_tags($descriptionHtml)), 200),
                'description' => $descriptionHtml,
                'sku' => 'HIM-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'price' => $mrp,
                'sale_price' => $sellingPrice < $mrp ? $sellingPrice : null,
                'stock' => $stock,
                'is_active' => ! $deactivated,
                'is_bestseller' => (bool) ($additionalAttributes['isBestSeller'] ?? false),
                'status' => $deactivated ? 'draft' : ($stock > 0 ? 'active' : 'out_of_stock'),
                'meta_title' => Str::limit($name, 60, ''),
                'meta_description' => Str::limit(trim(strip_tags($descriptionHtml)), 160),
            ]);

            $imagePath = $this->downloadImage($item['productImageUrl'] ?? null, $slug);

            if ($imagePath) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'image_type' => 'real',
                    'alt_text' => $name,
                    'sort_order' => 0,
                    'is_primary' => true,
                ]);
            }

            $imported++;
        }

        $this->info("Imported {$imported} product(s), skipped {$skipped} duplicate(s).");

        return self::SUCCESS;
    }

    private function resolveCategory(string $name): string
    {
        $haystack = Str::lower($name);

        foreach (self::CATEGORY_KEYWORDS as $keyword => $category) {
            if (str_contains($haystack, $keyword)) {
                return $category;
            }
        }

        return 'Home Décor';
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function downloadImage(?string $url, string $slug): ?string
    {
        if (! $url) {
            return null;
        }

        try {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
            $path = "products/{$slug}.{$extension}";

            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }
}
