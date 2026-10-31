<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Auth: password login, or a one-time code that also registers new users
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'attemptLogin'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/otp/send', [AuthController::class, 'sendOtp'])->name('otp.send');
    Route::get('/otp/verify', [AuthController::class, 'showVerify'])->name('otp.show');
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::get('/register/complete', [AuthController::class, 'showComplete'])->name('register.complete');
    Route::post('/register/complete', [AuthController::class, 'completeRegistration'])->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Cart (no auth required)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/summary', [CartController::class, 'drawer'])->name('cart.drawer');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{itemKey}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{itemKey}', [CartController::class, 'remove'])->name('cart.remove');

// Auth-required routes
Route::middleware('auth')->group(function () {
    // Account
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile/identity', [ProfileController::class, 'updateIdentity'])->name('profile.identity');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::patch('/profile/email', [ProfileController::class, 'updateEmail'])->name('profile.email');
    Route::post('/profile/phone/otp', [ProfileController::class, 'sendPhoneOtp'])->name('profile.phone.otp');
    Route::post('/profile/phone/verify', [ProfileController::class, 'verifyPhone'])->name('profile.phone.verify');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/address', [CheckoutController::class, 'selectAddress'])->name('checkout.address');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::delete('/checkout/coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Saved addresses
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::patch('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/result', [OrderController::class, 'result'])->name('orders.result');

    // Payment
    Route::post('/payment/{order}/pay', [PaymentController::class, 'pay'])->name('payment.pay');
});

// Payment callback (no auth — gateway redirects back here)
Route::get('/payment/callback/{gateway}', [PaymentController::class, 'callback'])->name('payment.callback');
Route::post('/payment/callback/{gateway}', [PaymentController::class, 'callback']);
