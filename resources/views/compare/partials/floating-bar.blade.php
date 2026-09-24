<div
    x-data="{
        count: 0,
        init() {
            try { this.count = parseInt(sessionStorage.getItem('compare_count') || '0'); } catch (e) {}
            window.addEventListener('compare-updated', (e) => {
                this.count = e.detail;
                try { sessionStorage.setItem('compare_count', this.count); } catch (e) {}
            });
        }
    }"
    x-show="count > 0"
    x-cloak
    class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40 bg-white shadow-xl border border-brand-200 rounded-full px-5 py-3 flex items-center gap-4">
    <span class="text-sm text-brand-700">
        <span x-text="count"></span> product(s) to compare
    </span>
    <a href="{{ route('compare.show') }}" class="bg-brand-700 text-white text-xs px-4 py-2 rounded-full">Compare Now</a>
    <button
        onclick="fetch('{{ route('compare.clear') }}', {method:'DELETE', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(() => window.dispatchEvent(new CustomEvent('compare-updated', {detail: 0})))"
        class="text-xs text-brand-500 underline">Clear All</button>
</div>
