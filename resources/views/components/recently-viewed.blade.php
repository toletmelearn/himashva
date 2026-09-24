@props(['excludeProductId' => null])

@php
    $viewedIds = session('recently_viewed', []);

    if ($excludeProductId) {
        $viewedIds = array_values(array_filter($viewedIds, fn ($id) => $id != $excludeProductId));
    }

    $viewedProducts = $viewedIds
        ? \App\Models\Product::active()->whereIn('id', $viewedIds)->with('images')->get()
            ->sortBy(fn ($p) => array_search($p->id, $viewedIds))
        : collect();
@endphp

@if ($viewedProducts->isNotEmpty())
    <section class="mt-16">
        <h2 class="font-display text-2xl text-brand-900 mb-6">Recently Viewed</h2>
        <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
            @foreach ($viewedProducts as $viewed)
                <div class="flex-shrink-0 w-44">
                    <x-product-card :product="$viewed" />
                </div>
            @endforeach
        </div>
    </section>
@endif
