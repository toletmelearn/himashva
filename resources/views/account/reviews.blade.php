<x-layouts.app>
<x-slot:title>My Reviews | Himashva</x-slot:title>

<div class="max-w-4xl mx-auto px-4 py-8">
    @include('account.partials.nav')

    <h1 class="font-display text-3xl text-brand-900 mb-6">My Reviews</h1>

    @forelse ($reviews as $review)
        <div class="bg-white border border-brand-200 rounded-xl p-4 mb-3">
            <p class="font-medium text-brand-900">{{ $review->product->name }}</p>
            <div class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
            <p class="text-sm text-brand-600">{{ $review->comment }}</p>
            <span class="text-xs {{ $review->is_approved ? 'text-green-600' : 'text-amber-600' }}">
                {{ $review->is_approved ? 'Approved' : 'Pending approval' }}
            </span>
        </div>
    @empty
        <p class="text-sm text-brand-500">You haven't written any reviews yet.</p>
    @endforelse
</div>
</x-layouts.app>
