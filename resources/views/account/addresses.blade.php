<x-layouts.app>
<x-slot:title>My Addresses | Himashva</x-slot:title>

<div class="max-w-4xl mx-auto px-4 py-8" x-data="{ showForm: false }">
    @include('account.partials.nav')

    <div class="flex justify-between items-center mb-6">
        <h1 class="font-display text-3xl text-brand-900">My Addresses</h1>
        <button @click="showForm = !showForm" class="bg-brand-700 text-white px-4 py-2 rounded-full text-sm">+ Add Address</button>
    </div>

    <div x-show="showForm" x-cloak class="bg-white border border-brand-200 rounded-xl p-6 mb-6">
        <form action="{{ route('account.addresses.store') }}" method="POST" class="grid sm:grid-cols-2 gap-4">
            @csrf
            <input name="label" aria-label="Label (Home, Office...)" placeholder="Label (Home, Office...)" required class="border border-brand-300 rounded px-3 py-2 text-sm">
            <input name="name" aria-label="Full Name" placeholder="Full Name" required class="border border-brand-300 rounded px-3 py-2 text-sm">
            <input name="phone" aria-label="Phone" placeholder="Phone" required class="border border-brand-300 rounded px-3 py-2 text-sm">
            <input name="postal_code" aria-label="Postal Code" placeholder="Postal Code" required class="border border-brand-300 rounded px-3 py-2 text-sm">
            <input name="address_line_1" aria-label="Address Line 1" placeholder="Address Line 1" required class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
            <input name="address_line_2" aria-label="Address Line 2" placeholder="Address Line 2" class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
            <input name="city" aria-label="City" placeholder="City" required class="border border-brand-300 rounded px-3 py-2 text-sm">
            <input name="state" aria-label="State" placeholder="State" required class="border border-brand-300 rounded px-3 py-2 text-sm">
            <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_default" value="1"> Set as default</label>
            <button class="bg-brand-700 text-white px-4 py-2 rounded-full text-sm sm:col-span-2">Save Address</button>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        @forelse ($addresses as $addr)
            <div class="bg-white border border-brand-200 rounded-xl p-4 text-sm relative">
                @if ($addr->is_default)<span class="absolute top-3 right-3 text-xs bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full">Default</span>@endif
                <p class="font-semibold text-brand-900">{{ $addr->label }}</p>
                <p>{{ $addr->name }} — {{ $addr->phone }}</p>
                <p class="text-brand-600">{{ $addr->address_line_1 }}, {{ $addr->city }}, {{ $addr->state }} {{ $addr->postal_code }}</p>
                <form action="{{ route('account.addresses.destroy', $addr->id) }}" method="POST" class="mt-2">
                    @csrf @method('DELETE')
                    <button class="text-red-500 text-xs">Delete</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-brand-500">No saved addresses yet.</p>
        @endforelse
    </div>
</div>
</x-layouts.app>
