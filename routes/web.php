<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->to('/login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



// password is abcdefgh
// this route should be commented out
// @@@@@@@@@@@@@@
Route::get("/change-password", "TestController@changePassword");
// @@@@@@@@@@@@@@




Route::middleware('auth')->group(function () {
    Route::resource('product-variant', 'VariantController');
    Route::get("products/search", "ProductController@search")->name("search");
    Route::post("/store-image", "ProductController@storeImage");
    Route::resource('product', 'ProductController');
    Route::resource('blog', 'BlogController');
    Route::resource('blog-category', 'BlogCategoryController');
});
