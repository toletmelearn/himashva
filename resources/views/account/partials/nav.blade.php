<div class="flex gap-4 overflow-x-auto text-sm mb-6 border-b border-brand-200 pb-2">
    <a href="{{ route('account.dashboard') }}" class="whitespace-nowrap {{ request()->routeIs('account.dashboard') ? 'text-brand-800 font-semibold' : 'text-brand-500' }}">Dashboard</a>
    <a href="{{ route('account.orders') }}" class="whitespace-nowrap {{ request()->routeIs('account.orders*') ? 'text-brand-800 font-semibold' : 'text-brand-500' }}">Orders</a>
    <a href="{{ route('account.addresses') }}" class="whitespace-nowrap {{ request()->routeIs('account.addresses') ? 'text-brand-800 font-semibold' : 'text-brand-500' }}">Addresses</a>
    <a href="{{ route('account.wishlist') }}" class="whitespace-nowrap {{ request()->routeIs('account.wishlist') ? 'text-brand-800 font-semibold' : 'text-brand-500' }}">Wishlist</a>
    <a href="{{ route('account.reviews') }}" class="whitespace-nowrap {{ request()->routeIs('account.reviews') ? 'text-brand-800 font-semibold' : 'text-brand-500' }}">My Reviews</a>
    <a href="{{ route('account.profile') }}" class="whitespace-nowrap {{ request()->routeIs('account.profile') ? 'text-brand-800 font-semibold' : 'text-brand-500' }}">Profile</a>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="whitespace-nowrap text-red-500">Logout</button></form>
</div>
