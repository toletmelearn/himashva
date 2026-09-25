<x-layouts.app>
<x-slot:title>{{ $title }} | Himashva</x-slot:title>

<x-json-ld type="breadcrumb" :data="['items' => [
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'Shop', 'url' => route('shop')],
]]" />

<div class="max-w-7xl mx-auto px-4 py-8">
    <nav class="text-xs text-brand-500 mb-4">
        <a href="{{ route('home') }}" class="hover:underline">Home</a> /
        @if ($activeCategory)
            <a href="{{ route('shop') }}" class="hover:underline">Shop</a> / {{ $activeCategory->name }}
        @else
            Shop
        @endif
    </nav>

    <h1 class="font-display text-3xl text-brand-900 mb-6">{{ $title }}</h1>

    <div class="flex flex-col md:flex-row gap-8" x-data="{ showFilters: false }">
        <aside class="md:w-64 shrink-0" data-aos="fade-right">
            <button type="button" @click="showFilters = !showFilters"
                class="md:hidden w-full flex items-center justify-between border border-brand-300 rounded-lg px-4 py-3 text-sm font-medium text-brand-800 mb-4">
                <span>Filters &amp; Sort</span>
                <span x-text="showFilters ? '−' : '+'"></span>
            </button>
            <form method="GET" class="space-y-6" :class="{ 'hidden md:block': !showFilters }">
                <div>
                    <h3 class="font-semibold text-brand-800 mb-2 text-sm">Categories</h3>
                    <div class="space-y-1 text-sm">
                        @foreach ($categories as $cat)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                    {{ in_array($cat->id, (array) request('categories', [])) ? 'checked' : '' }}>
                                {{ $cat->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-brand-800 mb-2 text-sm">Discount</h3>
                    <div class="space-y-1 text-sm">
                        @foreach ([
                            '0-20' => '0 - 20%',
                            '21-40' => '21 - 40%',
                            '41-60' => '41 - 60%',
                            '61-80' => '61 - 80%',
                            '81-100' => '81 - 100%',
                        ] as $bucket => $label)
                            <label class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="discount[]" value="{{ $bucket }}"
                                        {{ in_array($bucket, (array) request('discount', [])) ? 'checked' : '' }}>
                                    {{ $label }}
                                </span>
                                <span class="text-brand-400">{{ $discountCounts[$bucket] ?? 0 }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-brand-800 mb-2 text-sm">Price Range</h3>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" aria-label="Minimum price" value="{{ request('min_price') }}" placeholder="Min" class="w-1/2 border border-brand-300 rounded px-2 py-1 text-base sm:text-sm">
                        <input type="number" name="max_price" aria-label="Maximum price" value="{{ request('max_price') }}" placeholder="Max" class="w-1/2 border border-brand-300 rounded px-2 py-1 text-base sm:text-sm">
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-brand-800 mb-2 text-sm">Sort By</h3>
                    <select name="sort" class="w-full border border-brand-300 rounded px-2 py-2 text-sm">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price-low-high" {{ request('sort') === 'price-low-high' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price-high-low" {{ request('sort') === 'price-high-low' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="popularity" {{ request('sort') === 'popularity' ? 'selected' : '' }}>Popularity</option>
                        <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Rating</option>
                        <option value="discount" {{ request('sort') === 'discount' ? 'selected' : '' }}>Highest Discount</option>
                    </select>
                </div>

                <button class="w-full bg-brand-700 hover:bg-brand-800 text-white py-2 rounded-full text-sm font-medium">Apply Filters</button>
            </form>
        </aside>

        <div class="flex-1">
            <p class="text-sm text-brand-500 mb-4" data-aos="fade-up">{{ $products->total() }} products found</p>

            @if ($products->count())
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 stagger-in">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $products->links() }}</div>
            @else
                <div class="text-center py-20">
                    <p class="text-4xl mb-3">🔍</p>
                    <p class="text-brand-600">No products found. Try adjusting your filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>
</x-layouts.app>
