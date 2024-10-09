<?php

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ContactUsFormController;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationMailer;
use Illuminate\Support\Facades\Session;



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
// Route::group(['middleware' => 'prevent-back-history'],function(){


Route::get('/', function () {
    return view('welcome');
})->name('home');



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';



// ----------------------------- LOGIN AND RESITER -----------------------//
Route::controller(AuthController::class)->group(function () {
    Route::get('register', 'register')->name('register');
    Route::post('register', 'registerSave')->name('register.save');

    Route::get('login', 'login')->name('login');
    Route::post('login', 'loginAction')->name('login.action');

    Route::get('logout', 'logout')->middleware('auth')->name('logout');
});

// ----------------------------- DASHBOARD -----------------------//
Route::get('dashboard', function () {
    return view('dashboard'); // Admin dashboard view
})->name('dashboard');

Route::get('seller_dashboard', function () {
    return view('seller_dashboard'); // Seller dashboard view
})->name('seller_dashboard');
// User Dashboard
Route::get('/', function () {
    return view('welcome'); // User dashboard view
})->name('home');

Route::get('/', [ProductController::class, 'showProduct'])->name('home');

Route::get('/fetch-products', [ProductController::class, 'fetchProducts'])->name('products.fetch');
// ----------------------------- SHOP -----------------------//
Route::controller(ShopController::class)->group(function () {
    Route::get('shop', 'shop')->name('shop');
    Route::get('about_us', 'aboutUs')->name('about_us');
    Route::get( 'cart', 'cart')->name('cart');
    Route::get( 'checkout', 'checkout')->name('checkout');
    Route::get( 'product-details/{id}', 'show')->name('product-details');
    Route::post('cart/{$id}', 'add_to_cart')->name('add_to_cart');

 });
// ----------------------------- PRODUCT -----------------------//
    Route::controller(ProductController::class)->prefix('products')->group(function () {
        //Seller
        Route::get('', 'index')->name('products');
        Route::get('create', 'create')->name('products.create');
        Route::post('store', 'store')->name('products.store');
        Route::get('show/{id}', 'show')->name('products.show');
        Route::get('edit/{id}', 'edit')->name('products.edit');
        Route::put('edit/{id}', 'update')->name('products.update');
        Route::delete('destroy/{id}', 'destroy')->name('products.destroy');
    });

    Route::get('/profile', [App\Http\Controllers\AuthController::class, 'profile'])->name('profile');

// ----------------------------- Category -----------------------//
Route::controller(CategoryController::class)->prefix('categories')->group(function () {
    Route::get('', 'index') ->name('category');
    Route::get('create', 'create')->name('category.create');
    Route::post('store', 'store')->name('category.store');
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
    Mail::to('sarmientojohnchristoper@gmail.com')->send(new SendVerificationMailer());
});
Route::get('/new-password', [AuthController::class, 'newPassword'])->name('new-password');



// ----------------------------- Contact Us-----------------------//
Route::get('/contact', [ContactUsFormController::class, 'createForm'])->name('contact');
Route::post('/contact', [ContactUsFormController::class, 'ContactUsForm'])->name('contact.store');
Route::get('/index', [ContactUsFormController::class, 'index'])->name('contacts.index');
Route::get('/contacts/{id}', [ContactUsFormController::class, 'show'])->name('contact.show');
Route::delete('/contacts/{id}', [ContactUsFormController::class, 'destroy'])->name('contact.destroy');


// ----------------------------- End Of Route Back Log -----------------------//
// });
