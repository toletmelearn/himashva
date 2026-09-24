<x-layouts.app>
<x-slot:title>My Wishlist | Himashva</x-slot:title>

<div class="max-w-5xl mx-auto px-4 py-8">
    @include('account.partials.nav')

    <h1 class="font-display text-3xl text-brand-900 mb-6">My Wishlist</h1>

    @if ($items->count())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach ($items as $item)
                <x-product-card :product="$item->product" />
            @endforeach
        </div>
    @else
        <div class="text-center py-16">
            <p class="text-4xl mb-3">♥</p>
            <p class="text-brand-600">Your wishlist is empty.</p>
        </div>
    @endif
</div>
</x-layouts.app>
