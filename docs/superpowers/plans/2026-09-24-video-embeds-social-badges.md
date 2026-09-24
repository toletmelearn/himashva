# Video Embeds + Social Media Icons + JSON-LD SEO + Product Badges Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let the admin attach YouTube/Instagram video links to a product (shown as click-through thumbnail cards), finish wiring the social media links that already exist in Site Settings into the storefront footer, add Schema.org JSON-LD structured data to product/home/category pages, and auto-display Sale/New/Bestseller/Few-Left badges on product cards — all with zero developer dependency after this ships.

**Architecture:** New `ProductVideo` model + migration + Filament relation manager mirror the existing `ProductImage`/`ImagesRelationManager` pattern exactly. Social icons reuse the site's existing `footer-social-mock` emoji-link pattern (already renders Instagram/Facebook/Pinterest/WhatsApp) rather than introducing a parallel SVG-icon component — it's already there, just incomplete. JSON-LD ships as a small blade component included on three existing views. Badges are a pure view-layer addition to the existing `product-card` component using fields already on `Product`.

**Tech Stack:** Laravel 11 / PHP 8.3, Filament (admin panel), Blade, PHPUnit feature tests, MySQL (via XAMPP).

**Spec:** User-provided feature spec (pasted in conversation, 2026-09-24) — this plan implements Features 1, 2, 3, 4 from it, adjusted per investigation below.

## Global Constraints

- All 177 existing tests must keep passing; run `php artisan test` after every task.
- Never run `migrate:fresh`, `db:wipe`, or any destructive DB command.
- Follow CLAUDE.md conventions: `make:migration`/`make:model`/`make:test --phpunit` via artisan, PHP 8 constructor promotion where applicable, explicit return types, curly braces always.
- Run `vendor/bin/pint --dirty --format agent` after PHP edits, before considering a task done.
- Do not create new base directories; `RelationManagers/` and `components/` already exist, use them.

## Investigation findings that changed scope vs. the original spec

- **Social media settings already exist.** `app/Filament/Pages/ManageSiteSettings.php` has a "Social" tab with `social_instagram`, `social_facebook`, `social_pinterest`, `social_youtube`, `social_twitter`. Only `social_linkedin` is missing. **Do not rebuild this page or add a new `social-icons` blade component** — `resources/views/partials/footer.blade.php:17-22` already renders `footer-social-mock` emoji links for Instagram/Facebook/Pinterest/WhatsApp. The minimal safe change is: add `social_linkedin` to the settings page, and add the *missing* YouTube/Twitter/LinkedIn links to that existing footer block, matching its established pattern. No header change, no new component, no JSON-LD `sameAs` duplication concerns.
- **`Product` already has** `is_bestseller`, `is_new`, `is_active`, `low_stock_threshold`, `sale_price`, `price`, `stock`, `created_at` — everything Feature 4 needs, no model changes required.
- **Route name is `shop`, not `shop.index`.** Category page route is `category.show`.
- The product detail page already renders its own breadcrumb nav (`resources/views/product/show.blade.php:6-9`) — a separate visual `<x-breadcrumbs>` component would duplicate it. This plan adds only the invisible JSON-LD `BreadcrumbList` script, not a new visual breadcrumb component.
- Product image convention uses a plain `string` column for `image_type` (not a DB enum) validated via a Filament `Select`. `ProductVideo.platform` follows the same convention.

---

### Task 1: `product_videos` table, `ProductVideo` model, `Product::videos()` relation

**Files:**
- Create: `database/migrations/2026_09_24_000001_create_product_videos_table.php`
- Create: `app/Models/ProductVideo.php`
- Modify: `app/Models/Product.php` (add `videos()` relation near `images()`, around line 71)
- Create: `database/factories/ProductVideoFactory.php`
- Test: `tests/Feature/ProductVideoTest.php`

**Interfaces:**
- Produces: `ProductVideo::class` with fillable `product_id, platform, video_url, video_id, title, thumbnail_url, sort_order, is_active`; casts `is_active => boolean`; relation `product(): BelongsTo`; accessor `thumbnail` (string, never null) via `getThumbnailAttribute()`.
- Produces: `Product::videos(): HasMany` — active videos only, ordered by `sort_order`, matching the `images()` relation's shape at `app/Models/Product.php:65-69`.

- [ ] **Step 1: Create the migration**

Run: `php artisan make:migration create_product_videos_table --no-interaction`

Replace its contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('video_url');
            $table->string('video_id')->nullable();
            $table->string('title')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_videos');
    }
};
```

- [ ] **Step 2: Run the migration**

Run: `php artisan migrate --no-interaction`
Expected: `product_videos` table created, no errors.

- [ ] **Step 3: Create the model**

Run: `php artisan make:model ProductVideo --no-interaction`

Replace `app/Models/ProductVideo.php` with:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'platform', 'video_url', 'video_id',
        'title', 'thumbnail_url', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $video) {
            if ($video->platform === 'youtube') {
                if (preg_match('/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $video->video_url, $matches)) {
                    $video->video_id = $matches[1];
                }
                if ($video->video_id && ! $video->thumbnail_url) {
                    $video->thumbnail_url = "https://img.youtube.com/vi/{$video->video_id}/hqdefault.jpg";
                }
            } elseif ($video->platform === 'instagram') {
                if (preg_match('/instagram\.com\/(?:reel|p)\/([a-zA-Z0-9_-]+)/', $video->video_url, $matches)) {
                    $video->video_id = $matches[1];
                }
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getThumbnailAttribute(): string
    {
        if ($this->thumbnail_url) {
            return $this->thumbnail_url;
        }

        if ($this->platform === 'youtube' && $this->video_id) {
            return "https://img.youtube.com/vi/{$this->video_id}/hqdefault.jpg";
        }

        return asset('images/instagram-video-placeholder.png');
    }
}
```

- [ ] **Step 4: Add the `videos()` relation to `Product`**

In `app/Models/Product.php`, immediately after the `images()` method (ends around line 69), add:

```php
    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->where('is_active', true)->orderBy('sort_order');
    }
```

(`HasMany` is already imported at the top of the file — confirm the `use Illuminate\Database\Eloquent\Relations\HasMany;` line exists; it does, per `images()`'s own return type.)

- [ ] **Step 5: Create the factory**

Run: `php artisan make:factory ProductVideoFactory --no-interaction`

Replace `database/factories/ProductVideoFactory.php` with:

```php
<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVideo>
 */
class ProductVideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v='.$this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'title' => $this->faker->sentence(3),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 6: Write the model test**

Run: `php artisan make:test ProductVideoTest --phpunit --no-interaction`

Replace `tests/Feature/ProductVideoTest.php` with:

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_youtube_video_id_is_auto_extracted_from_url(): void
    {
        $product = Product::factory()->create();

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $this->assertSame('dQw4w9WgXcQ', $video->video_id);
    }

    public function test_youtube_thumbnail_is_auto_generated_when_not_set(): void
    {
        $product = Product::factory()->create();

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'youtube',
            'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        ]);

        $this->assertSame('https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $video->thumbnail);
    }

    public function test_instagram_video_id_is_auto_extracted_from_url(): void
    {
        $product = Product::factory()->create();

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'instagram',
            'video_url' => 'https://www.instagram.com/reel/Cabc123XYZ/',
        ]);

        $this->assertSame('Cabc123XYZ', $video->video_id);
    }

    public function test_product_videos_relation_excludes_inactive_and_orders_by_sort_order(): void
    {
        $product = Product::factory()->create();
        ProductVideo::factory()->for($product)->create(['sort_order' => 2, 'title' => 'Second']);
        ProductVideo::factory()->for($product)->create(['sort_order' => 1, 'title' => 'First']);
        ProductVideo::factory()->for($product)->create(['is_active' => false, 'title' => 'Hidden']);

        $titles = $product->fresh()->videos->pluck('title')->all();

        $this->assertSame(['First', 'Second'], $titles);
    }
}
```

- [ ] **Step 7: Run the tests**

Run: `php artisan test tests/Feature/ProductVideoTest.php --compact`
Expected: 4 passed.

- [ ] **Step 8: Format and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add database/migrations/2026_09_24_000001_create_product_videos_table.php app/Models/ProductVideo.php app/Models/Product.php database/factories/ProductVideoFactory.php tests/Feature/ProductVideoTest.php
git commit -m "feat: add ProductVideo model with auto ID/thumbnail extraction"
```

(Skip `git commit` if this repo is not under git — confirm with `git status` first; the environment reported "Is a git repository: false" for the working directory, so instead just verify the files are saved.)

---

### Task 2: Admin — Videos relation manager on Product

**Files:**
- Create: `app/Filament/Resources/ProductResource/RelationManagers/VideosRelationManager.php`
- Modify: `app/Filament/Resources/ProductResource.php:216-222` (`getRelations()`)
- Modify: `app/Http/Controllers/ProductController.php:16` (eager-load `videos`)
- Test: `tests/Feature/ProductVideoAdminTest.php`

**Interfaces:**
- Consumes: `Product::videos()` from Task 1.
- Produces: nothing new consumed by later tasks (storefront reads `$product->videos` directly).

- [ ] **Step 1: Create the relation manager**

Create `app/Filament/Resources/ProductResource/RelationManagers/VideosRelationManager.php`:

```php
<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VideosRelationManager extends RelationManager
{
    protected static string $relationship = 'videos';

    protected static ?string $title = 'Videos';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('platform')
                ->options(['youtube' => 'YouTube', 'instagram' => 'Instagram'])
                ->required()
                ->reactive(),
            Forms\Components\TextInput::make('video_url')
                ->label('Video URL')
                ->required()
                ->url()
                ->helperText('Paste the full YouTube or Instagram video/reel URL')
                ->placeholder(fn (callable $get) => match ($get('platform')) {
                    'youtube' => 'https://www.youtube.com/watch?v=...',
                    'instagram' => 'https://www.instagram.com/reel/...',
                    default => 'Select a platform first',
                }),
            Forms\Components\TextInput::make('title')
                ->placeholder('e.g. Unboxing Video, Burn Test, How to Use')
                ->maxLength(100),
            Forms\Components\TextInput::make('thumbnail_url')
                ->label('Custom Thumbnail URL (optional)')
                ->url()
                ->helperText('Leave blank for auto-generated thumbnail (YouTube only). For Instagram, upload a screenshot.'),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Preview')
                    ->width(80)
                    ->height(45),
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'youtube' => 'danger',
                        'instagram' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')->default('—'),
                Tables\Columns\TextColumn::make('video_url')->limit(40),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
```

- [ ] **Step 2: Register the relation manager**

In `app/Filament/Resources/ProductResource.php`, add the import near the top (alongside the existing `RelationManagers\...` usages already in the file) and update `getRelations()` (currently at line 216-222):

```php
    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
            VariantsRelationManager::class,
            RelationManagers\VideosRelationManager::class,
        ];
    }
```

Check the file's existing `use` statements first — `ImagesRelationManager` and `VariantsRelationManager` are likely already imported directly (not via the `RelationManagers` namespace prefix); add `VideosRelationManager` the same way they are, for consistency (either add a matching `use ...VideosRelationManager;` line, or reference it via whatever style the two existing entries use).

- [ ] **Step 3: Eager-load videos on the product page**

In `app/Http/Controllers/ProductController.php:16`, change:

```php
$product = Product::visible()->with(['images', 'variants', 'attributes_', 'category', 'sizeGuides'])
```

to:

```php
$product = Product::visible()->with(['images', 'variants', 'attributes_', 'category', 'sizeGuides', 'videos'])
```

- [ ] **Step 4: Write the admin test**

Run: `php artisan make:test ProductVideoAdminTest --phpunit --no-interaction`

Replace `tests/Feature/ProductVideoAdminTest.php` with (mirror whatever auth setup an existing Filament resource test in this repo uses — check `tests/Feature` for an existing `*ResourceTest.php` pattern before writing; if none exists, create the admin user directly):

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVideoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_youtube_video_to_product(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create();

        $this->actingAs($admin);

        $video = ProductVideo::create([
            'product_id' => $product->id,
            'platform' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'title' => 'Unboxing Video',
        ]);

        $this->assertDatabaseHas('product_videos', [
            'id' => $video->id,
            'product_id' => $product->id,
            'title' => 'Unboxing Video',
        ]);
    }
}
```

Before writing this test, run `grep -rln "assignRole" tests/Feature | head -3` to confirm the exact role name/spatie setup this project uses (e.g. `'admin'` vs `'Admin'`), and adjust the test to match — do not guess if the grep shows a different convention.

- [ ] **Step 5: Run the tests**

Run: `php artisan test tests/Feature/ProductVideoAdminTest.php --compact`
Expected: passed.

- [ ] **Step 6: Format and commit**

Run: `vendor/bin/pint --dirty --format agent`, then commit if in a git repo.

---

### Task 3: Instagram placeholder image + storefront video section

**Files:**
- Create: `public/images/instagram-video-placeholder.png`
- Modify: `resources/views/product/show.blade.php` (add Videos section)
- Test: `tests/Feature/ProductVideoStorefrontTest.php`

**Interfaces:**
- Consumes: `$product->videos` (from Task 1/2), each with `->thumbnail`, `->video_url`, `->title`, `->platform`.

- [ ] **Step 1: Generate the Instagram placeholder image**

Run this one-off script (matches how `public/images/no-image.png` was produced — a generated placeholder, not committed as a reusable script):

```bash
php -r '
$w = 400; $h = 225;
$im = imagecreatetruecolor($w, $h);
$c1 = imagecolorallocate($im, 240, 148, 51);
$c2 = imagecolorallocate($im, 188, 24, 136);
imagefilledrectangle($im, 0, 0, $w, $h, $c2);
for ($x = 0; $x < $w; $x++) {
    $ratio = $x / $w;
    $r = (int) (240 * (1 - $ratio) + 188 * $ratio);
    $g = (int) (148 * (1 - $ratio) + 24 * $ratio);
    $b = (int) (51 * (1 - $ratio) + 136 * $ratio);
    $col = imagecolorallocate($im, $r, $g, $b);
    imageline($im, $x, 0, $x, $h, $col);
}
$white = imagecolorallocate($im, 255, 255, 255);
imagefilledellipse($im, $w/2, $h/2 - 15, 60, 60, $white);
$pink = imagecolorallocate($im, 220, 39, 67);
imagefilledpolygon($im, [$w/2 - 10, $h/2 - 35, $w/2 - 10, $h/2 + 5, $w/2 + 20, $h/2 - 15], 3, $pink);
$font = 5;
$text = "Watch on Instagram";
$tw = imagefontwidth($font) * strlen($text);
imagestring($im, $font, ($w - $tw) / 2, $h/2 + 45, $text, $white);
imagepng($im, "public/images/instagram-video-placeholder.png");
imagedestroy($im);
echo "done\n";
'
```

Expected: `public/images/instagram-video-placeholder.png` exists, `done` printed.

- [ ] **Step 2: Add the Videos section to the product page**

In `resources/views/product/show.blade.php`, find the closing of the main gallery/details `<div class="grid md:grid-cols-2 gap-10" ...>` block and add this section immediately after it (before whatever section currently follows, e.g. related products or reviews):

```blade
@if ($product->videos->isNotEmpty())
<section class="mt-10" data-aos="fade-up">
    <h2 class="font-display text-xl text-brand-900 mb-4">Watch Our Videos</h2>
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($product->videos as $video)
        <a href="{{ $video->video_url }}" target="_blank" rel="noopener noreferrer" class="flex-shrink-0 w-64 group">
            <div style="position: relative; border-radius: 12px; overflow: hidden; aspect-ratio: 16/9; background: #1a1a1a;">
                <img src="{{ $video->thumbnail }}" alt="{{ $video->title ?? $product->name }}"
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;"
                     class="group-hover:scale-105">
                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.2); transition: background 0.3s;"
                     class="group-hover:bg-black/40">
                    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 24 24" fill="#2C2018" style="width: 20px; height: 20px; margin-left: 2px;">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                </div>
                <div style="position: absolute; top: 8px; right: 8px; padding: 3px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 600; color: white;
                    {{ $video->platform === 'youtube' ? 'background: #FF0000;' : 'background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);' }}">
                    {{ $video->platform === 'youtube' ? '▶ YouTube' : '📸 Instagram' }}
                </div>
            </div>
            @if ($video->title)
            <p class="text-sm text-brand-700 mt-2 font-medium group-hover:text-brand-900 transition">{{ $video->title }}</p>
            @endif
        </a>
        @endforeach
    </div>
</section>
@endif
```

- [ ] **Step 3: Write the storefront test**

Run: `php artisan make:test ProductVideoStorefrontTest --phpunit --no-interaction`

Replace `tests/Feature/ProductVideoStorefrontTest.php` with:

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVideoStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_video_section_when_videos_exist(): void
    {
        $product = Product::factory()->create(['status' => 'active']);
        ProductVideo::factory()->for($product)->create([
            'title' => 'Unboxing Video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('Watch Our Videos');
        $response->assertSee('Unboxing Video');
        $response->assertSee('https://www.youtube.com/watch?v=dQw4w9WgXcQ', false);
    }

    public function test_product_page_hides_video_section_when_no_videos(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertDontSee('Watch Our Videos');
    }
}
```

- [ ] **Step 4: Run the tests**

Run: `php artisan test tests/Feature/ProductVideoStorefrontTest.php --compact`
Expected: 2 passed.

If `test_product_page_shows_video_section_when_videos_exist` fails on `status`, check `Product::factory()`'s definition (Task investigation showed it defaults `is_active => true` but no `status` field) — add `'status' => 'active'` to the factory call as shown, since `Product::visible()` filters on `status`.

- [ ] **Step 5: Run the full suite, format, commit**

Run: `php artisan test --compact`
Expected: all tests (177 + new ones) pass.

Run: `vendor/bin/pint --dirty --format agent`, then commit if in a git repo.

---

### Task 4: `social_linkedin` setting + finish footer social icons

**Files:**
- Modify: `app/Filament/Pages/ManageSiteSettings.php` (Social tab schema + `save()` groups array)
- Modify: `resources/views/partials/footer.blade.php:17-22`
- Test: `tests/Feature/FooterSocialLinksTest.php`

**Interfaces:**
- Consumes: `settings(string $key)` helper (already exists, `app/helpers.php`).

- [ ] **Step 1: Add `social_linkedin` to the Social settings tab**

In `app/Filament/Pages/ManageSiteSettings.php`, in the `Tabs\Tab::make('Social')` schema (currently 5 fields), add a 6th field:

```php
                        Forms\Components\Tabs\Tab::make('Social')
                            ->schema([
                                Forms\Components\TextInput::make('social_instagram'),
                                Forms\Components\TextInput::make('social_facebook'),
                                Forms\Components\TextInput::make('social_pinterest'),
                                Forms\Components\TextInput::make('social_youtube'),
                                Forms\Components\TextInput::make('social_twitter'),
                                Forms\Components\TextInput::make('social_linkedin'),
                            ]),
```

And in `save()`'s `$groups` array, add `'social_linkedin' => 'social',` next to the other `social_*` entries.

- [ ] **Step 2: Complete the footer's social icon block**

In `resources/views/partials/footer.blade.php`, replace lines 17-22:

```blade
                <div class="footer-social-mock">
                    @if (settings('social_instagram'))<a href="{{ settings('social_instagram') }}" aria-label="Instagram">📷</a>@endif
                    @if (settings('social_facebook'))<a href="{{ settings('social_facebook') }}" aria-label="Facebook">📘</a>@endif
                    @if (settings('social_pinterest'))<a href="{{ settings('social_pinterest') }}" aria-label="Pinterest">📌</a>@endif
                    @if (settings('whatsapp_number'))<a href="https://wa.me/{{ settings('whatsapp_number') }}" target="_blank" rel="noopener" aria-label="WhatsApp">💬</a>@endif
                </div>
```

with:

```blade
                <div class="footer-social-mock">
                    @if (settings('social_instagram'))<a href="{{ settings('social_instagram') }}" aria-label="Instagram">📷</a>@endif
                    @if (settings('social_facebook'))<a href="{{ settings('social_facebook') }}" aria-label="Facebook">📘</a>@endif
                    @if (settings('social_youtube'))<a href="{{ settings('social_youtube') }}" aria-label="YouTube">▶️</a>@endif
                    @if (settings('social_twitter'))<a href="{{ settings('social_twitter') }}" aria-label="X (Twitter)">✖️</a>@endif
                    @if (settings('social_pinterest'))<a href="{{ settings('social_pinterest') }}" aria-label="Pinterest">📌</a>@endif
                    @if (settings('social_linkedin'))<a href="{{ settings('social_linkedin') }}" aria-label="LinkedIn">💼</a>@endif
                    @if (settings('whatsapp_number'))<a href="https://wa.me/{{ settings('whatsapp_number') }}" target="_blank" rel="noopener" aria-label="WhatsApp">💬</a>@endif
                </div>
```

All links open in the same tab except WhatsApp (matching the existing convention — none of the existing four had `target="_blank"` except WhatsApp; keep it that way rather than inventing a new pattern).

- [ ] **Step 3: Write the test**

Run: `php artisan make:test FooterSocialLinksTest --phpunit --no-interaction`

Replace `tests/Feature/FooterSocialLinksTest.php` with:

```php
<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSocialLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_shows_only_configured_social_links(): void
    {
        SiteSetting::create(['key' => 'social_youtube', 'value' => 'https://www.youtube.com/@himashva', 'group' => 'social']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://www.youtube.com/@himashva', false);
        $response->assertDontSee('aria-label="LinkedIn"', false);
    }

    public function test_footer_shows_linkedin_link_when_configured(): void
    {
        SiteSetting::create(['key' => 'social_linkedin', 'value' => 'https://www.linkedin.com/company/himashva/', 'group' => 'social']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://www.linkedin.com/company/himashva/', false);
    }
}
```

Before running, check `route('home')`'s controller doesn't require seed data that would break a fresh `RefreshDatabase` run (it renders `home.blade.php` which iterates `$heroBanners` — confirm the home controller handles an empty banners collection gracefully; it should, since `@if ($heroBanners->count())` guards it).

- [ ] **Step 4: Run the tests**

Run: `php artisan test tests/Feature/FooterSocialLinksTest.php --compact`
Expected: 2 passed.

- [ ] **Step 5: Run full suite, format, commit**

Run: `php artisan test --compact`, then `vendor/bin/pint --dirty --format agent`, then commit if in a git repo.

---

### Task 5: JSON-LD structured data (Product, Organization, BreadcrumbList)

**Files:**
- Create: `resources/views/components/json-ld.blade.php`
- Modify: `resources/views/product/show.blade.php` (add `<x-json-ld>` calls)
- Modify: `resources/views/home.blade.php` (add organization schema)
- Modify: `resources/views/shop/index.blade.php` (add breadcrumb schema)
- Test: `tests/Feature/JsonLdSchemaTest.php`

**Interfaces:**
- Consumes: `$product` (with `images`, `category`, `brand` relations already eager-loaded via `Product::visible()->with([...])` from Task 2's controller change), `settings()` helper.

- [ ] **Step 1: Create the JSON-LD component**

Create `resources/views/components/json-ld.blade.php`:

```blade
@props(['type', 'data' => []])

@if ($type === 'product' && isset($data['product']))
@php $p = $data['product']; @endphp
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ e($p->name) }}",
    "description": "{{ e($p->short_description ?? $p->meta_description ?? '') }}",
    "image": [
        @foreach ($p->images->take(3) as $img)
            "{{ $img->image_path !== 'placeholder.jpg' ? asset('storage/' . $img->image_path) : '' }}"{{ !$loop->last ? ',' : '' }}
        @endforeach
    ],
    "sku": "{{ e($p->sku) }}",
    "brand": {
        "@type": "Brand",
        "name": "{{ e($p->brand?->name ?? settings('site_name', 'Himashva')) }}"
    },
    "offers": {
        "@type": "Offer",
        "price": "{{ $p->sale_price ?: $p->price }}",
        "priceCurrency": "INR",
        "availability": "{{ $p->status === 'active' && $p->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "seller": {
            "@type": "Organization",
            "name": "{{ e(settings('site_name', 'Himashva')) }}"
        }
    }
    @if ($p->review_count > 0)
    ,"aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "{{ $p->avg_rating }}",
        "reviewCount": "{{ $p->review_count }}"
    }
    @endif
}
</script>
@endif

@if ($type === 'breadcrumb' && isset($data['items']))
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        @foreach ($data['items'] as $i => $item)
        {
            "@type": "ListItem",
            "position": {{ $i + 1 }},
            "name": "{{ e($item['name']) }}",
            "item": "{{ $item['url'] }}"
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endif

@if ($type === 'organization')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "{{ e(settings('site_name', 'Himashva')) }}",
    "url": "{{ config('app.url') }}",
    "logo": "{{ asset('images/logo.png') }}",
    "sameAs": [
        @php
            $links = collect(['social_facebook', 'social_instagram', 'social_youtube', 'social_twitter', 'social_pinterest', 'social_linkedin'])
                ->map(fn ($key) => settings($key))
                ->filter()
                ->values();
        @endphp
        @foreach ($links as $i => $url)
            "{{ $url }}"{{ $i < $links->count() - 1 ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endif
```

Note this deliberately drops the `contactPoint` block from the original spec draft — `settings('contact_phone')` doesn't exist as a key in this project (`ManageSiteSettings` uses `contact_email`/`contact_phone`... actually check: the General tab does have `contact_phone`, so it's fine to keep). Re-add it if desired; kept out here only to keep the component minimal and testable — no functional requirement was lost since it's optional per Schema.org.

- [ ] **Step 2: Add Product + Breadcrumb JSON-LD to the product page**

In `resources/views/product/show.blade.php`, immediately after the `<x-slot:description>` line (before the `<div class="max-w-7xl ...">`), add:

```blade
<x-json-ld type="product" :data="['product' => $product]" />
<x-json-ld type="breadcrumb" :data="['items' => [
    ['name' => 'Home', 'url' => url('/')],
    ['name' => $product->category?->name ?? 'Shop', 'url' => $product->category ? route('category.show', $product->category->slug) : route('shop')],
    ['name' => $product->name, 'url' => route('product.show', $product->slug)],
]]" />
```

- [ ] **Step 3: Add Organization JSON-LD to the homepage**

In `resources/views/home.blade.php`, immediately after the `<x-slot:title>` line, add:

```blade
<x-json-ld type="organization" />
```

- [ ] **Step 4: Add Breadcrumb JSON-LD to the shop/category listing**

Check `resources/views/shop/index.blade.php`'s top (it's the `shop` route view). Immediately after its opening `<x-layouts.app>`/slot lines, add:

```blade
<x-json-ld type="breadcrumb" :data="['items' => [
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'Shop', 'url' => route('shop')],
]]" />
```

- [ ] **Step 5: Write the tests**

Run: `php artisan make:test JsonLdSchemaTest --phpunit --no-interaction`

Replace `tests/Feature/JsonLdSchemaTest.php` with:

```php
<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JsonLdSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_contains_json_ld_product_schema(): void
    {
        $product = Product::factory()->create(['status' => 'active']);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('"@type": "BreadcrumbList"', false);
    }

    public function test_homepage_contains_json_ld_organization_schema(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('"@type": "Organization"', false);
    }

    public function test_shop_page_contains_json_ld_breadcrumb_schema(): void
    {
        $response = $this->get(route('shop'));

        $response->assertOk();
        $response->assertSee('"@type": "BreadcrumbList"', false);
    }
}
```

- [ ] **Step 6: Run the tests**

Run: `php artisan test tests/Feature/JsonLdSchemaTest.php --compact`
Expected: 3 passed. If the shop/home routes need additional factory data to render (categories, banners), check their controllers and add minimal fixtures (e.g. `\App\Models\Category::factory()->create()`) rather than guessing — read the controller first.

- [ ] **Step 7: Run full suite, format, commit**

Run: `php artisan test --compact`, then `vendor/bin/pint --dirty --format agent`, then commit if in a git repo.

---

### Task 6: Auto product badges (Sale / New / Bestseller / Few Left)

**Files:**
- Modify: `resources/views/components/product-card.blade.php`
- Test: `tests/Feature/ProductBadgeTest.php`

**Interfaces:**
- Consumes: `$product->sale_price`, `$product->price`, `$product->created_at`, `$product->is_new`, `$product->is_bestseller`, `$product->stock`, `$product->low_stock_threshold` — all existing fields, no model changes.

- [ ] **Step 1: Add the badge block to product-card**

In `resources/views/components/product-card.blade.php`, inside `<div class="product-image-wrap">`, right before the existing `@if ($product->discount_percentage > 0)` block (line 9), add:

```blade
        <div style="position: absolute; top: 8px; left: 8px; display: flex; flex-direction: column; gap: 4px; z-index: 2;">
            @if ($product->sale_price && $product->sale_price < $product->price)
                @php $discount = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
                <span style="background: #EF4444; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">-{{ $discount }}% OFF</span>
            @endif
            @if ($product->is_new || $product->created_at->gt(now()->subDays(14)))
                <span style="background: #10B981; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">NEW</span>
            @endif
            @if ($product->is_bestseller)
                <span style="background: #F59E0B; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">BESTSELLER</span>
            @endif
            @if ($product->stock > 0 && $product->stock <= ($product->low_stock_threshold ?? 5))
                <span style="background: #8B5CF6; color: white; font-size: 0.6rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">FEW LEFT</span>
            @endif
        </div>
```

Leave the existing `discount-badge-mock` block (line 9-11) as-is — it duplicates the new sale badge visually since `$product->discount_percentage` doesn't currently resolve to anything (no such accessor exists on `Product`, confirmed via repo-wide grep), so it silently renders nothing today. Do not touch it; it's pre-existing behavior outside this task's scope per CLAUDE.md scope-control rules. Report it as a found-but-unfixed issue.

- [ ] **Step 2: Write the test**

Run: `php artisan make:test ProductBadgeTest --phpunit --no-interaction`

Replace `tests/Feature/ProductBadgeTest.php` with:

```php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_shows_sale_and_bestseller_badges(): void
    {
        Product::factory()->create([
            'status' => 'active',
            'price' => 1000,
            'sale_price' => 800,
            'is_bestseller' => true,
            'created_at' => now()->subDays(60),
        ]);

        $response = $this->get(route('shop'));

        $response->assertOk();
        $response->assertSee('-20% OFF', false);
        $response->assertSee('BESTSELLER', false);
        $response->assertDontSee('NEW', false);
    }

    public function test_shop_page_shows_new_badge_for_recent_product(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'created_at' => now()->subDays(2)]);

        $response = $this->get(route('shop'));

        $response->assertOk();
        $response->assertSee('NEW', false);
    }

    public function test_shop_page_shows_few_left_badge_when_stock_at_threshold(): void
    {
        Product::factory()->create([
            'status' => 'active',
            'stock' => 3,
            'low_stock_threshold' => 5,
            'created_at' => now()->subDays(60),
        ]);

        $response = $this->get(route('shop'));

        $response->assertOk();
        $response->assertSee('FEW LEFT', false);
    }
}
```

Before writing, run `grep -n "product-card" resources/views/shop/index.blade.php` to confirm the shop listing actually renders `<x-product-card>` (it should, as the primary storefront grid) — adjust the route/view under test if it uses a different component or route for the grid.

- [ ] **Step 3: Run the tests**

Run: `php artisan test tests/Feature/ProductBadgeTest.php --compact`
Expected: 3 passed.

- [ ] **Step 4: Run full suite, format, commit**

Run: `php artisan test --compact`
Expected: all tests pass (177 original + ~15 new).

Run: `vendor/bin/pint --dirty --format agent`, then commit if in a git repo.

---

## Final Verification

- [ ] `php artisan test --compact` — full suite green.
- [ ] `php artisan route:list --name=product.show` and manually hit a product with videos seeded via tinker (read-only check, no `migrate:fresh`) to confirm the video section, JSON-LD `<script>` tags, and badges all render.
- [ ] Admin panel → Products → edit any product → confirm "Videos" relation manager tab appears and a video can be created (video_id/thumbnail auto-populate on save).
- [ ] Admin panel → Site Settings → Social tab → confirm `social_linkedin` field is present and saves.
- [ ] Footer on any storefront page → confirm social icons only show for platforms with a saved URL.
- [ ] Report final test count and full list of files changed/created.
