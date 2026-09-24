<x-layouts.app>
<x-slot:title>{{ $page->meta_title ?: $page->title }} | Himashva</x-slot:title>
<x-slot:description>{{ $page->meta_description }}</x-slot:description>

<div class="max-w-3xl mx-auto px-4 py-12">
    <h1 class="font-display text-4xl text-brand-900 mb-6">{{ $page->title }}</h1>
    <div class="prose prose-brand max-w-none text-brand-700 leading-relaxed">
        {!! $page->content !!}
    </div>
</div>
</x-layouts.app>
