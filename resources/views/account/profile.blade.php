<x-layouts.app>
<x-slot:title>My Profile | Himashva</x-slot:title>

<div class="max-w-2xl mx-auto px-4 py-8">
    @include('account.partials.nav')

    <h1 class="font-display text-3xl text-brand-900 mb-6">My Profile</h1>

    <form action="{{ route('account.profile.update') }}" method="POST" class="bg-white border border-brand-200 rounded-xl p-6 grid sm:grid-cols-2 gap-4">
        @csrf @method('PUT')
        <input name="name" aria-label="Name" value="{{ old('name', $user->name) }}" placeholder="Name" required class="border border-brand-300 rounded px-3 py-2 text-sm sm:col-span-2">
        <input name="email" type="email" aria-label="Email" value="{{ old('email', $user->email) }}" placeholder="Email" required class="border border-brand-300 rounded px-3 py-2 text-sm">
        <input name="phone" aria-label="Phone" value="{{ old('phone', $user->phone) }}" placeholder="Phone" class="border border-brand-300 rounded px-3 py-2 text-sm">
        <input name="date_of_birth" type="date" aria-label="Date of birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" class="border border-brand-300 rounded px-3 py-2 text-sm">
        <select name="gender" aria-label="Gender" class="border border-brand-300 rounded px-3 py-2 text-sm">
            <option value="">Gender</option>
            <option value="male" {{ $user->gender === 'male' ? 'selected' : '' }}>Male</option>
            <option value="female" {{ $user->gender === 'female' ? 'selected' : '' }}>Female</option>
            <option value="other" {{ $user->gender === 'other' ? 'selected' : '' }}>Other</option>
        </select>
        <input name="password" type="password" aria-label="New Password (optional)" placeholder="New Password (optional)" class="border border-brand-300 rounded px-3 py-2 text-sm">
        <input name="password_confirmation" type="password" aria-label="Confirm New Password" placeholder="Confirm New Password" class="border border-brand-300 rounded px-3 py-2 text-sm">
        <button class="bg-brand-700 text-white px-4 py-2 rounded-full text-sm sm:col-span-2">Save Changes</button>
    </form>
</div>
</x-layouts.app>
