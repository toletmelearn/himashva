<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\OrderInvoiceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GuestAccountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderTrackController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewVoteController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Public storefront
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category.show');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/search', [ShopController::class, 'search'])->name('search');

Route::get('/api/product/{product:slug}', [ProductController::class, 'quickView'])->name('api.product.show');
Route::get('/api/search', [ShopController::class, 'searchAutocomplete'])->name('api.search');
Route::get('/api/recent-purchases', [HomeController::class, 'recentPurchases'])->name('api.recent-purchases');

// Cart (works for guests and logged-in)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');
Route::post('/cart/dismiss-coupon', [CartController::class, 'dismissCoupon'])->name('cart.dismissCoupon');

// Wishlist
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Review helpfulness voting (guests and logged-in)
Route::post('/reviews/{review}/vote', [ReviewVoteController::class, 'store'])->name('reviews.vote');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');
Route::post('/checkout/save-email', [CheckoutController::class, 'saveEmail'])->name('checkout.saveEmail');
Route::get('/order/success/{orderNumber}', [CheckoutController::class, 'success'])->name('order.success');
Route::post('/guest/create-account', [GuestAccountController::class, 'store'])->name('guest.create-account');

// Payment
Route::post('/payment/razorpay/create', [PaymentController::class, 'createRazorpayOrder'])->name('payment.razorpay.create');
Route::post('/payment/razorpay/verify', [PaymentController::class, 'verifyPayment'])->name('payment.razorpay.verify');

// Track order
Route::get('/track-order', [OrderTrackController::class, 'form'])->name('track.form');
Route::post('/track-order', [OrderTrackController::class, 'track'])->name('track.submit');

// Newsletter
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Contact
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Chatbot
Route::post('/chatbot/message', [ChatbotController::class, 'respond'])->name('chatbot.respond');

// Static pages
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

// Pincode check
Route::post('/check-pincode', [ShippingController::class, 'checkPincode'])->name('check.pincode');

// Compare
Route::post('/compare/add/{product}', [CompareController::class, 'add'])->name('compare.add');
Route::delete('/compare/remove/{product}', [CompareController::class, 'remove'])->name('compare.remove');
Route::delete('/compare/clear', [CompareController::class, 'clear'])->name('compare.clear');
Route::get('/compare', [CompareController::class, 'show'])->name('compare.show');

Route::get('/dashboard', function () {
    return auth()->user()->isAdmin()
        ? redirect('/admin')
        : redirect()->route('account.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin extras (invoice)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/orders/{order}/invoice', OrderInvoiceController::class)->name('orders.invoice');
});

// Customer account
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderNumber}', [AccountController::class, 'orderDetail'])->name('orders.show');
    Route::post('/orders/{orderNumber}/return', [ReturnController::class, 'store'])->name('orders.return');
    Route::post('/orders/{orderNumber}/cancel', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::get('/reviews', [AccountController::class, 'reviews'])->name('reviews');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

require __DIR__.'/auth.php';
