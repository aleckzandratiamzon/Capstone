<?php

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ContactUsFormController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationMailer;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Category;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// ----------------------------- Start Backlog-----------------------//
Route::group(['middleware' => 'prevent-back-history'],function(){


Route::get('/', function () {
    $featured = Product::where('featured', true)->get();
    $best_seller = Product::where('best_seller', true)->get();
    $category=Category::all();
    return view('welcome', compact('featured', 'best_seller', 'category'));
})->name('home');

//->middleware('redirect.authenticated');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

 Route::get('/profile', [App\Http\Controllers\AuthController::class, 'profile'])->name('profile');

require __DIR__.'/auth.php';



// ----------------------------- LOGIN AND RESITER -----------------------//

    Route::controller(AuthController::class)->group(function () {
        Route::get('register', 'register')->name('register');
        Route::post('register', 'registerSave')->name('register.save');

        Route::get('login', 'login')->name('login');
        Route::post('login', 'loginAction')->name('login.action');

        Route::get('logout', 'logout')->middleware('auth')->name('logout');
    });

// ----------------------------- SHOP -----------------------//
Route::controller(ShopController::class)->group(function () {
    Route::get('shop', 'shop')->name('shop');
    Route::get('about_us', 'aboutUs')->name('about_us');
    Route::get( 'product-details/{id}', 'show')->name('product-details');
 });

// ----------------------------- Contact Us-----------------------//
Route::get('/contact', [ContactUsFormController::class, 'createForm'])->name('contact');
Route::post('/contact', [ContactUsFormController::class, 'ContactUsForm'])->name('contact.store');
Route::get('/index', [ContactUsFormController::class, 'index'])->name('contacts.index');
Route::get('/contacts/{id}', [ContactUsFormController::class, 'show'])->name('contact.show');
Route::delete('/contacts/{id}', [ContactUsFormController::class, 'destroy'])->name('contact.destroy');


// ----------------------------- ADMIN ACCESS -----------------------//
Route::middleware(['auth', 'role:Admin', 'prevent-back-history'])->group(function () {
    // ----------------------------- ADMIN DASHBOARD -----------------------//
    Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // ----------------------------- Category -----------------------//
    Route::controller(CategoryController::class)->prefix('categories')->group(function () {
        Route::get('', 'index') ->name('category');
        Route::get('create', 'create')->name('category.create');
        Route::post('store', 'store')->name('category.store');
        Route::get('show/{id}', 'show')->name('category.show');
        Route::get('edit/{id}', 'edit')->name('category.edit');
        Route::put('edit/{id}', 'update')->name('category.update');
        Route::delete('destroy/{id}', 'destroy')->name('category.destroy');
    });

    // ----------------------------- User management -----------------------//
    Route::controller(UserManagementController::class)->prefix('usermanagement')->group(function () {
        Route::get('', 'index')->name('usermanagement');
        Route::get('create', 'create')->name('usermanagement.create');
        Route::post('store', 'store')->name('usermanagement.store');
        Route::get('show/{id}', 'show')->name('usermanagement.show');
        Route::get('edit/{id}', 'edit')->name('usermanagement.edit');
        Route::put('edit/{id}', 'update')->name('usermanagement.update');
        Route::delete('destroy/{id}', 'destroy')->name('usermanagement.destroy');
    });

    // ----------------------------- ACTIVITY-LOGS -----------------------//
    Route::get('activity/log', [UserManagementController::class, 'activity'])->name('activity/log');
});

 // ----------------------------- SELLER ACCESS -----------------------//
Route::middleware(['auth', 'role:Seller', 'prevent-back-history'])->group(function () {
     // ----------------------------- SELLER DASHBOARD -----------------------//
    Route::get('seller_dashboard', [DashboardController::class, 'sellerDashboard'])->name('seller_dashboard');
     // ----------------------------- ORDER -----------------------//
    Route::get('orders', [OrderController::class, 'index'])->name('showOrder');
    // ----------------------------- PRODUCT -----------------------//
    Route::controller(ProductController::class)->prefix('products')->group(function () {
        Route::get('', 'index')->name('products');
        Route::get('create', 'create')->name('products.create');
        Route::post('store', 'store')->name('products.store');
        Route::get('show/{id}', 'show')->name('products.show');
        Route::get('edit/{id}', 'edit')->name('products.edit');
        Route::put('edit/{id}', 'update')->name('products.update');
        Route::delete('destroy/{id}', 'destroy')->name('products.destroy');
    });
});

// ----------------------------- AUTHENTICATED USER ACCESS -----------------------//
Route::middleware(['auth.guest', 'prevent-back-history'])->group(function () {

    Route::controller(ShopController::class)->group(function () {
        Route::get( 'my_account', 'my_account')->name('my_account');
        Route::get( 'thankyou', 'thankYou')->name('thank-you');
        Route::get( 'customize/{id}', 'customize')->name('customize');
    });

    // ----------------------------- CART -----------------------//
    Route::controller(CartController::class)->group(function () {
        Route::get( 'cart', 'cart')->name('cart');
        Route::post('cart/{id}', 'add_to_cart')->name('add_to_cart');
        Route::get( 'checkout', 'checkout')->name('checkout');
        Route::delete('destroy/{id}', 'destroy')->name('remove_product');
    });

    // ----------------------------- ORDER -----------------------//
    Route::controller(OrderController::class)->group(function () {
        Route::post( 'order', 'placeOrder')->name('order');
        Route::get( 'shipped/{id}', 'shipped')->name('order_shipped');
        Route::get( 'delivered/{id}', 'delivered')->name('order_delivered');
    });

    // ----------------------------- STRIPE PAYMENT -----------------------//
    Route::controller(StripePaymentController::class)->group(function(){
        Route::post('stripe', 'stripePost')->name('stripe.post');
    });
});


// ----------------------------- OTP -----------------------//
Route::post('reset_password', [AuthController::class,'resetPassword']);
Route::get('forgot-password', function () {
    if(Session::has('current_user')){
        return redirect('dashboard');
    }else{
        return view('forgot-password');
    }
})->middleware('guest')->name('password.request');

Route::get('re-new-password', function (){

    return view('new-password')->with('failed','Invalid OTP code');
});
Route::post('/new-password', [AuthController::class,'findUserToChangePass']);
route::get('test-mail',function(){
    // Inside your function/method
    Session::put('reset_otp_code', random_int(000000,999999));
    Mail::to('gawanggamat1111@gmail.com')->send(new SendVerificationMailer());
});
Route::get('/new-password', [AuthController::class, 'newPassword'])->name('new-password');


// ----------------------------- End Of Route Back Log -----------------------//
});

