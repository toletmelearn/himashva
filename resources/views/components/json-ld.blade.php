@props(['type', 'data' => []])

@if ($type === 'product' && isset($data['product']))
@php $p = $data['product']; @endphp
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
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
    "@@context": "https://schema.org",
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
    "@@context": "https://schema.org",
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
