<x-layouts.app>
<x-slot:title>{{ $product->meta_title ?: $product->name }} | Himashva</x-slot:title>
<x-slot:description>{{ $product->meta_description ?: $product->short_description }}</x-slot:description>

<div class="max-w-7xl mx-auto px-4 py-8">
    <nav class="text-xs text-brand-500 mb-6">
        <a href="{{ route('home') }}" class="hover:underline">Home</a> /
        <a href="{{ route('category.show', $product->category->slug) }}" class="hover:underline">{{ $product->category->name }}</a> /
        {{ $product->name }}
    </nav>

    @php
        // Real admin-uploaded photos show first; the AI-generated catalog placeholder falls back last.
        $galleryImages = $product->images->sortBy(fn ($image) => $image->image_type === 'real' ? 0 : 1)->values();
    @endphp
    <div class="grid md:grid-cols-2 gap-10" x-data="{
        storageUrl: '{{ rtrim(asset('storage'), '/') }}/',
        images: {{ $galleryImages->map->only(['image_path', 'image_type'])->toJson() }},
        active: 0,
        variants: {{ $product->variants->where('is_active', true)->values()->toJson() }},
        selectedVariant: null,
        quantity: 1,
        get price() {
            if (this.selectedVariant) return this.selectedVariant.sale_price || this.selectedVariant.price;
            return {{ $product->sale_price ?? $product->price }};
        },
        zoomScale: 1,
        zoomOriginX: 50,
        zoomOriginY: 50,
        zoom(e) {
            if (window.matchMedia('(hover: none)').matches) return;
            const rect = e.currentTarget.getBoundingClientRect();
            this.zoomOriginX = ((e.clientX - rect.left) / rect.width) * 100;
            this.zoomOriginY = ((e.clientY - rect.top) / rect.height) * 100;
            this.zoomScale = 2.5;
        },
        resetZoom() {
            this.zoomScale = 1;
            this.zoomOriginX = 50;
            this.zoomOriginY = 50;
        },
    }">
        <div>
            <div class="clip-reveal aspect-square bg-gradient-to-br from-brand-100 to-brand-200 rounded-2xl overflow-hidden mb-4 flex items-center justify-center relative"
                style="cursor: crosshair;"
                @mousemove="zoom($event)" @mouseleave="resetZoom()">
                <template x-if="images.length && images[active].image_path !== 'placeholder.jpg'">
                    <img :src="storageUrl + images[active].image_path" class="w-full h-full object-cover"
                        :style="'transform: scale(' + zoomScale + '); transform-origin: ' + zoomOriginX + '% ' + zoomOriginY + '%; transition: transform 0.1s ease;'">
                </template>
                <template x-if="!images.length || images[active].image_path === 'placeholder.jpg'">
                    <span class="text-7xl">🕯️</span>
                </template>
                <span x-show="images.length" x-text="images[active].image_type === 'real' ? 'Real Photo' : 'AI Generated'"
                    class="absolute top-3 left-3 text-xs font-semibold px-2 py-1 rounded-full bg-white/80 text-brand-700"></span>
            </div>
            <div class="flex gap-2" x-show="images.length > 1">
                <template x-for="(img, i) in images" :key="i">
                    <button @click="active = i" class="w-16 h-16 rounded-lg overflow-hidden border-2 relative" :class="active === i ? 'border-brand-700' : 'border-transparent'">
                        <img :src="storageUrl + img.image_path" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        <div>
            <h1 data-aos="fade-up" class="font-display text-3xl text-brand-900 mb-2">{{ $product->name }}</h1>
            <p class="text-xs text-brand-400 mb-4">SKU: {{ $product->sku }}</p>

            <div data-aos="fade-up" data-aos-delay="100" class="flex items-baseline gap-3 mb-4">
                <span class="text-2xl font-semibold text-brand-800" x-text="'₹' + Number(price).toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                @if ($product->sale_price)
                    <span class="text-lg text-brand-400 line-through">₹{{ number_format($product->price, 2) }}</span>
                    <span class="bg-red-100 text-red-600 text-xs font-semibold px-2 py-1 rounded-full">-{{ $product->discount_percentage }}%</span>
                @endif
            </div>

            <p class="text-brand-600 mb-6">{{ $product->short_description }}</p>

            @if ($product->status === 'active' && $product->stock > 0)
                @php
                    $now = now();
                    $cutoffHour = 14;
                    $processingDays = 1;

                    $shipsOn = $now->copy();
                    if ($now->hour >= $cutoffHour) {
                        $shipsOn->addDay();
                    }
                    while ($shipsOn->isWeekend()) {
                        $shipsOn->addDay();
                    }
                    $shipsOn->addDays($processingDays);
                    while ($shipsOn->isWeekend()) {
                        $shipsOn->addDay();
                    }

                    $deliveryMin = $shipsOn->copy()->addDays(4);
                    $deliveryMax = $shipsOn->copy()->addDays(7);

                    $hoursLeft = $cutoffHour - $now->hour;
                    $orderWithinText = $hoursLeft > 0 && $now->hour < $cutoffHour
                        ? "Order within {$hoursLeft} hour".($hoursLeft > 1 ? 's' : '')
                        : 'Order now';
                @endphp
                <div class="flex items-center gap-2 mb-6 px-4 py-3 rounded-lg text-sm" style="background:#F0FDF4;border:1px solid #BBF7D0;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#16A34A" class="w-5 h-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    <div>
                        <span class="font-semibold" style="color:#16A34A;">{{ $orderWithinText }}</span>
                        <span class="text-brand-600">to get it by</span>
                        <span class="font-semibold text-brand-900">{{ $deliveryMin->format('M d') }} – {{ $deliveryMax->format('M d') }}</span>
                    </div>
                </div>
            @endif

            <template x-if="variants.length">
                <div class="mb-6">
                    <label class="text-sm font-medium text-brand-800 block mb-2">Choose an option:</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="variant in variants" :key="variant.id">
                            <button @click="selectedVariant = variant"
                                class="px-4 py-2 rounded-full border text-sm"
                                :class="selectedVariant && selectedVariant.id === variant.id ? 'bg-brand-700 text-white border-brand-700' : 'border-brand-300 text-brand-700'"
                                x-text="variant.name"></button>
                        </template>
                    </div>
                </div>
            </template>

            @if ($sizeGuide)
                <div x-data="{ showSizeGuide: false }" class="mb-4">
                    <button type="button" @click="showSizeGuide = true" class="text-sm font-medium text-brand-700 underline">
                        📏 Size Guide
                    </button>

                    <div x-show="showSizeGuide" x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                        @click.self="showSizeGuide = false">
                        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[85vh] overflow-y-auto p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-brand-900">{{ $sizeGuide->name }}</h3>
                                <button @click="showSizeGuide = false" class="text-brand-500">✕</button>
                            </div>

                            @if ($sizeGuide->description)
                                <p class="text-sm text-brand-600 mb-4">{{ $sizeGuide->description }}</p>
                            @endif

                            @if (in_array($sizeGuide->type, ['table', 'both']) && !empty($sizeGuide->table_data['headers'] ?? []))
                                <div class="overflow-x-auto mb-4">
                                    <table class="w-full text-sm border border-brand-200">
                                        <thead class="bg-brand-50">
                                            <tr>
                                                @foreach ($sizeGuide->table_data['headers'] as $header)
                                                    <th class="px-3 py-2 text-left border-b border-brand-200">{{ $header }} @if($sizeGuide->measurement_unit) ({{ $sizeGuide->measurement_unit }}) @endif</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sizeGuide->table_data['rows'] ?? [] as $row)
                                                <tr class="border-b border-brand-100">
                                                    @foreach ($row as $cell)
                                                        <td class="px-3 py-2">{{ $cell }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if (in_array($sizeGuide->type, ['image', 'both']) && $sizeGuide->image_path)
                                <img src="{{ asset('storage/' . $sizeGuide->image_path) }}" alt="{{ $sizeGuide->name }}" class="w-full rounded-lg">
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-4 mb-6">
                <div class="flex items-center border border-brand-300 rounded-full">
                    <button @click="quantity = Math.max(1, quantity - 1)" class="w-9 h-9 text-brand-700">−</button>
                    <span x-text="quantity" class="w-8 text-center"></span>
                    <button @click="quantity++" class="w-9 h-9 text-brand-700">+</button>
                </div>

                <button
                    @click="fetch('{{ route('cart.add') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ product_id: {{ $product->id }}, variant_id: selectedVariant ? selectedVariant.id : null, quantity: quantity })
                    }).then(r => r.json()).then(d => {
                        document.getElementById('cart-count-badge').textContent = d.count;
                        window.dispatchEvent(new CustomEvent('toast', { detail: 'Added to cart!' }));
                    })"
                    class="flex-1 bg-brand-700 hover:bg-brand-800 text-white font-medium py-3 rounded-full active:scale-95 transition-transform">
                    Add to Cart
                </button>

                @auth
                    <button
                        @click.prevent="fetch('{{ route('wishlist.toggle') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ product_id: {{ $product->id }} })
                        })"
                        class="w-12 h-12 rounded-full border border-brand-300 flex items-center justify-center {{ $inWishlist ? 'text-red-500' : 'text-brand-700' }}">
                        ♥
                    </button>
                @endauth
            </div>

            <div class="flex items-center gap-3 mb-8">
                <a href="https://wa.me/?text={{ urlencode($product->name.' — ₹'.number_format($product->sale_price ?: $product->price, 2).' — Check it out: '.route('product.show', $product->slug)) }}"
                    target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-semibold text-white transition-colors"
                    style="background:#25D366;" onmouseover="this.style.background='#128C7E'" onmouseout="this.style.background='#25D366'">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Share on WhatsApp
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" target="_blank" class="text-xs text-brand-500 hover:underline">Share on Facebook</a>
            </div>

            <div x-data="{ tab: 'description' }">
                <div class="flex gap-6 border-b border-brand-200 text-sm font-medium">
                    <button @click="tab = 'description'" :class="tab === 'description' ? 'text-brand-800 border-b-2 border-brand-700' : 'text-brand-400'" class="pb-3">Description</button>
                    <button @click="tab = 'info'" :class="tab === 'info' ? 'text-brand-800 border-b-2 border-brand-700' : 'text-brand-400'" class="pb-3">Additional Info</button>
                    <button @click="tab = 'reviews'" :class="tab === 'reviews' ? 'text-brand-800 border-b-2 border-brand-700' : 'text-brand-400'" class="pb-3">Reviews ({{ $product->review_count }})</button>
                </div>

                <div x-show="tab === 'description'" class="py-4 text-sm text-brand-700 leading-relaxed">
                    {!! $product->description !!}
                </div>

                <div x-show="tab === 'info'" x-cloak class="py-4 text-sm text-brand-700 space-y-1">
                    @forelse ($product->attributes_ as $attr)
                        <p><strong>{{ $attr->attribute_name }}:</strong> {{ $attr->attribute_value }}</p>
                    @empty
                        <p>SKU: {{ $product->sku }}</p>
                        @if ($product->weight_grams)<p>Weight: {{ $product->weight_grams }}g</p>@endif
                    @endforelse
                </div>

                <div x-show="tab === 'reviews'" x-cloak class="py-4 space-y-4" x-data="{ lightbox: false, lightboxType: 'image', lightboxSrc: '' }">
                    @forelse ($reviews as $review)
                        <div class="border-b border-brand-100 pb-3"
                             x-data="{ helpful: {{ $review->helpful_count }}, unhelpful: {{ $review->unhelpful_count }}, voted: false }">
                            <div class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                            <p class="text-sm font-medium text-brand-900">{{ $review->title }}</p>
                            <p class="text-sm text-brand-600">{{ $review->comment }}</p>
                            <p class="text-xs text-brand-400 mt-1">
                                {{ $review->user->name }}
                                @if ($review->is_verified_purchase)
                                    <span class="text-green-600 font-medium">✓ Verified Purchase</span>
                                @endif
                            </p>

                            @php $approvedMedia = $review->media->where('is_approved', true); @endphp
                            @if ($approvedMedia->isNotEmpty())
                                <div class="flex gap-2 mt-2 overflow-x-auto pb-1">
                                    @foreach ($approvedMedia as $media)
                                        <button type="button"
                                                @click="lightbox = true; lightboxType = '{{ $media->type }}'; lightboxSrc = '{{ $media->url }}'"
                                                class="relative shrink-0 w-20 h-20 rounded-lg overflow-hidden border border-brand-200">
                                            @if ($media->type === 'image')
                                                <img src="{{ $media->url }}" alt="Review photo" class="w-full h-full object-cover">
                                            @else
                                                <video src="{{ $media->url }}" class="w-full h-full object-cover"></video>
                                                <span class="absolute inset-0 flex items-center justify-center bg-black/30">
                                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M6 4l10 6-10 6V4z"/></svg>
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center gap-3 mt-2 text-xs text-brand-500">
                                <span>Was this helpful?</span>
                                <button type="button" :disabled="voted"
                                        @click="fetch('{{ route('reviews.vote', $review) }}', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                            body: JSON.stringify({ vote: 'up' })
                                        }).then(r => r.json()).then(data => { helpful = data.helpful_count; unhelpful = data.unhelpful_count; voted = true; })"
                                        class="hover:underline disabled:opacity-50">👍 <span x-text="helpful"></span></button>
                                <button type="button" :disabled="voted"
                                        @click="fetch('{{ route('reviews.vote', $review) }}', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                            body: JSON.stringify({ vote: 'down' })
                                        }).then(r => r.json()).then(data => { helpful = data.helpful_count; unhelpful = data.unhelpful_count; voted = true; })"
                                        class="hover:underline disabled:opacity-50">👎 <span x-text="unhelpful"></span></button>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-brand-500">No reviews yet.</p>
                    @endforelse

                    <div x-show="lightbox" x-cloak @click.self="lightbox = false" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
                        <div class="max-w-2xl w-full">
                            <template x-if="lightboxType === 'image'">
                                <img :src="lightboxSrc" class="w-full h-auto rounded-lg">
                            </template>
                            <template x-if="lightboxType === 'video'">
                                <video :src="lightboxSrc" controls autoplay class="w-full h-auto rounded-lg"></video>
                            </template>
                            <button type="button" @click="lightbox = false" class="mt-3 text-white text-sm underline">Close</button>
                        </div>
                    </div>

                    @auth
                        <form action="{{ route('account.reviews.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-2"
                              x-data="{ previews: [], onFiles(e) {
                                  this.previews = [];
                                  Array.from(e.target.files).slice(0, 5).forEach(file => {
                                      const reader = new FileReader();
                                      reader.onload = ev => this.previews.push({ url: ev.target.result, isVideo: file.type.startsWith('video/') });
                                      reader.readAsDataURL(file);
                                  });
                              } }">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <select name="rating" required class="border border-brand-300 rounded px-2 py-1 text-sm">
                                @for ($i = 5; $i >= 1; $i--)<option value="{{ $i }}">{{ $i }} Stars</option>@endfor
                            </select>
                            <input type="text" name="title" placeholder="Review title" class="w-full border border-brand-300 rounded px-2 py-1 text-sm">
                            <textarea name="comment" placeholder="Your review..." class="w-full border border-brand-300 rounded px-2 py-1 text-sm" rows="3"></textarea>

                            <label class="inline-flex items-center gap-2 border border-brand-300 rounded-full px-4 py-2 text-sm text-brand-700 cursor-pointer w-fit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Add Photos/Video
                                <input type="file" name="media[]" multiple accept="image/*,video/mp4,video/quicktime" class="hidden" @change="onFiles">
                            </label>
                            <p class="text-xs text-brand-400">Share photos of your candle! (Max 5 files, 5MB each)</p>

                            <div class="flex gap-2" x-show="previews.length">
                                <template x-for="(p, i) in previews" :key="i">
                                    <div class="w-16 h-16 rounded-lg overflow-hidden border border-brand-200">
                                        <img x-show="!p.isVideo" :src="p.url" class="w-full h-full object-cover">
                                        <video x-show="p.isVideo" :src="p.url" class="w-full h-full object-cover"></video>
                                    </div>
                                </template>
                            </div>

                            <button class="bg-brand-700 text-white text-sm px-4 py-2 rounded-full">Submit Review</button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </div>

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

    <x-recently-viewed :exclude-product-id="$product->id" />

    @if ($frequentlyBought->isNotEmpty())
        <section class="mt-16" data-aos="fade-up">
            <h2 class="font-display text-2xl text-brand-900 mb-6">Frequently Bought Together</h2>
            <div class="stagger-in grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach ($frequentlyBought as $fbt)
                    <x-product-card :product="$fbt" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($relatedProducts->count())
        <section class="mt-16">
            <h2 class="font-display text-2xl text-brand-900 mb-6">You May Also Like</h2>
            <div class="stagger-in grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach ($relatedProducts as $rp)
                    <x-product-card :product="$rp" />
                @endforeach
            </div>
        </section>
    @endif
</div>

<div x-data="{ show: false }"
    @scroll.window="show = window.scrollY > 500"
    x-show="show" x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full"
    x-transition:enter-end="translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-y-0"
    x-transition:leave-end="translate-y-full"
    class="md:hidden fixed bottom-0 left-0 right-0 z-40 glass-effect border-t border-brand-200 py-3 px-4">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <span class="font-medium text-sm truncate">{{ $product->name }}</span>
            <span class="font-bold text-brand-700 shrink-0">₹{{ number_format($product->sale_price ?? $product->price, 2) }}</span>
        </div>
        <button
            @click="fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
            }).then(r => r.json()).then(d => {
                document.getElementById('cart-count-badge').textContent = d.count;
                window.dispatchEvent(new CustomEvent('toast', { detail: 'Added to cart!' }));
            })"
            class="bg-brand-700 hover:bg-brand-800 text-white px-8 py-2.5 rounded-full text-sm font-semibold active:scale-95 transition-transform shrink-0">
            Add to Cart
        </button>
    </div>
</div>
</x-layouts.app>
