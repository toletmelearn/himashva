<div x-data="quickViewModal()" @quick-view.window="open($event.detail.slug)" x-cloak>
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="close()" @keydown.escape.window="close()"
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">

        <div x-show="isOpen"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto relative">

            <button @click="close()" class="absolute top-3 right-3 z-10 bg-black/5 hover:bg-black/10 rounded-full w-9 h-9 flex items-center justify-center" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12"/></svg>
            </button>

            <div x-show="loading" class="p-16 text-center text-brand-400">Loading...</div>

            <div x-show="!loading && product" class="grid md:grid-cols-2 gap-0">
                <div class="p-8 bg-brand-50 rounded-t-2xl md:rounded-l-2xl md:rounded-tr-none flex items-center justify-center">
                    <template x-if="product?.images?.[0]?.path">
                        <img :src="product.images[0].path" :alt="product?.name" class="max-h-80 object-contain rounded-lg">
                    </template>
                    <template x-if="!product?.images?.[0]?.path">
                        <span class="text-7xl">🕯️</span>
                    </template>
                </div>

                <div class="p-8">
                    <div class="text-xs text-brand-400 uppercase tracking-wide mb-1" x-text="product?.category"></div>
                    <h3 class="font-display text-xl text-brand-900 mb-3" x-text="product?.name"></h3>

                    <div class="mb-4">
                        <template x-if="product?.sale_price">
                            <div>
                                <span class="text-xl font-semibold text-brand-800" x-text="'₹' + parseFloat(product.sale_price).toFixed(2)"></span>
                                <span class="text-brand-400 line-through ml-2" x-text="'₹' + parseFloat(product.price).toFixed(2)"></span>
                            </div>
                        </template>
                        <template x-if="!product?.sale_price">
                            <span class="text-xl font-semibold text-brand-800" x-text="'₹' + parseFloat(product?.price || 0).toFixed(2)"></span>
                        </template>
                    </div>

                    <p class="text-sm text-brand-600 leading-relaxed mb-6" x-text="product?.short_description"></p>

                    <template x-if="product?.variants?.length > 0">
                        <div class="mb-4">
                            <label class="text-xs font-semibold text-brand-900 block mb-1">Variant</label>
                            <select x-model="selectedVariant" class="w-full border border-brand-200 rounded-lg px-3 py-2 text-sm">
                                <option value="">Select...</option>
                                <template x-for="v in product.variants" :key="v.id">
                                    <option :value="v.id" x-text="v.name + ' — ₹' + parseFloat(v.price).toFixed(2)"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <div class="flex gap-3 mt-4">
                        <template x-if="product?.stock > 0">
                            <button @click="addToCart()" class="flex-1 bg-brand-700 hover:bg-brand-800 text-white py-2.5 rounded-full text-sm font-semibold active:scale-95 transition-transform">
                                Add to Cart
                            </button>
                        </template>
                        <template x-if="product?.stock <= 0">
                            <span class="flex-1 text-center text-red-500 font-semibold py-2.5">Out of Stock</span>
                        </template>
                        <a :href="product?.url" class="px-5 py-2.5 border border-brand-200 rounded-full text-sm text-brand-600 hover:border-brand-700 hover:text-brand-700 transition text-center">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function quickViewModal() {
    return {
        isOpen: false,
        loading: false,
        product: null,
        selectedVariant: '',
        async open(slug) {
            this.isOpen = true;
            this.loading = true;
            this.selectedVariant = '';
            try {
                const res = await fetch('/api/product/' + slug);
                this.product = await res.json();
            } catch (e) {
                console.error(e);
            }
            this.loading = false;
        },
        close() {
            this.isOpen = false;
            this.product = null;
        },
        async addToCart() {
            const body = { product_id: this.product.id, quantity: 1 };
            if (this.selectedVariant) body.variant_id = parseInt(this.selectedVariant);

            const res = await fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            if (data.success) {
                const counter = document.getElementById('cart-count-badge');
                if (counter) counter.textContent = data.count;
                window.dispatchEvent(new CustomEvent('toast', { detail: 'Added to cart!' }));
                this.close();
            } else {
                window.dispatchEvent(new CustomEvent('toast', { detail: data.message || 'Could not add to cart' }));
            }
        },
    };
}
</script>
