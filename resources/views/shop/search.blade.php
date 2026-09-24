<x-layouts.app>
<x-slot:title>Search: {{ $term }} | Himashva</x-slot:title>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="font-display text-3xl text-brand-900 mb-2">Search Results</h1>
    <p class="text-sm text-brand-500 mb-6">{{ $products->total() }} results for "{{ $term }}"</p>

    @if ($products->count())
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    @else
        <div class="text-center py-20">
            <p class="text-4xl mb-3">🔍</p>
            <p class="text-brand-600">No products matched your search.</p>
        </div>
    @endif
</div>
</x-layouts.app>
