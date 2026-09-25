<x-layouts.app>
<x-slot:title>Compare Products | Himashva</x-slot:title>

    <div class="max-w-6xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-semibold text-brand-900 mb-6">Compare Products</h1>

        @if ($products->count() < 2)
            <div class="bg-brand-50 border border-brand-200 rounded-xl p-8 text-center text-brand-600">
                <p class="mb-4">Add at least 2 products to compare them side by side.</p>
                <a href="{{ route('shop') }}" class="inline-block bg-brand-700 text-white px-6 py-2 rounded-full">Continue Shopping</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <tbody>
                        <tr>
                            <th class="text-left p-3 text-brand-500 w-40">Image</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">
                                    @if ($product->images->first() && $product->images->first()->image_path !== 'placeholder.jpg')
                                        <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" alt="{{ $product->name }}" class="w-24 h-24 object-cover mx-auto rounded-lg" loading="lazy">
                                    @else
                                        <div class="w-24 h-24 mx-auto flex items-center justify-center bg-brand-50 rounded-lg text-2xl">🕯️</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">Name</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">
                                    <a href="{{ route('product.show', $product->slug) }}" class="font-medium text-brand-900 hover:underline">{{ $product->name }}</a>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">Price</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">
                                    @if ($product->sale_price)
                                        <span class="font-semibold">₹{{ number_format($product->sale_price, 2) }}</span>
                                        <span class="text-brand-400 line-through text-xs">₹{{ number_format($product->price, 2) }}</span>
                                    @else
                                        <span class="font-semibold">₹{{ number_format($product->price, 2) }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">Category</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">{{ $product->category?->name ?? '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">Brand</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">{{ $product->brand?->name ?? '—' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">Rating</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">{{ $product->avg_rating ? number_format($product->avg_rating, 1) . ' ★' : 'No reviews' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">Stock Status</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">
                                    {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">SKU</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">{{ $product->sku }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="text-left p-3 text-brand-500">Weight</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center border-b border-brand-100">{{ $product->weight_grams ? $product->weight_grams . 'g' : '—' }}</td>
                            @endforeach
                        </tr>

                        @foreach ($attributeNames as $attributeName)
                            <tr>
                                <th class="text-left p-3 text-brand-500">{{ $attributeName }}</th>
                                @foreach ($products as $product)
                                    <td class="p-3 text-center border-b border-brand-100">
                                        {{ $product->attributes_->firstWhere('attribute_name', $attributeName)?->attribute_value ?? '—' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        <tr>
                            <th class="text-left p-3"></th>
                            @foreach ($products as $product)
                                <td class="p-3 text-center">
                                    <div class="flex flex-col gap-2">
                                        <button
                                            onclick="fetch('{{ route('cart.add') }}', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}, body: JSON.stringify({product_id: {{ $product->id }}, quantity: 1})})"
                                            class="bg-brand-700 text-white text-xs px-3 py-2 rounded-full">Add to Cart</button>
                                        <button
                                            onclick="fetch('{{ route('compare.remove', $product->id) }}', {method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(() => location.reload())"
                                            class="border border-brand-300 text-brand-700 text-xs px-3 py-2 rounded-full">Remove</button>
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <button
                onclick="fetch('{{ route('compare.clear') }}', {method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(() => location.href='{{ route('shop') }}')"
                class="mt-6 text-sm text-brand-500 underline">Clear All</button>
        @endif
    </div>
</x-layouts.app>
