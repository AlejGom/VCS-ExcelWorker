<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\FilterController;


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

Route::get('/', function () {
    return view('login');
});
// -------------------------------------------------------------------------------
// Users manage
Route::get('/login', [UsersController::class,'showLoginForm'])->name('showLoginForm');
Route::post('/login', [UsersController::class, 'login'])->name('login');

Route::get('/signup', [UsersController::class,'showSignupForm'])->name('showSignupForm');
Route::post('/signup', [UsersController::class, 'signup'])->name('signup');

Route::get('/logout', [UsersController::class,'logout'])->name('logout');

// -------------------------------------------------------------------------------
// Files manage
Route::get('/uploadFile', [FileController::class, 'showUploadForm'])->name('showUploadForm')->middleware('auth');
Route::post('/uploadFile', [FileController::class, 'uploadFile'])->name('uploadFile');

Route::get('/files/{id}', [FileController::class,'readFile'])->name('readFile')->middleware('auth');

Route::get('/deleteFile/{id}', [FileController::class,'deleteFile'])->name('deleteFile')->middleware('auth');

Route::post('/updateCell', [FileController::class,'updateCell'])->name('updateCell')->middleware('auth');

// -------------------------------------------------------------------------------
// Others
Route::get('/mainPage', [FileController::class,'mainPage'])->name('mainPage')->middleware('auth');

Route::get('/increaseRows', [FileController::class,'increaseRowsInView'])->name('increaseRows')->middleware('auth');

Route::get('/goBack', [FileController::class,'goBack'])->name('goBack')->middleware('auth');

Route::get('/downloadFile/{id}', [FileController::class,'downloadFile'])->name('downloadFile')->middleware('auth');

// -------------------------------------------------------------------------------
// Filters

Route::post('/filterData/{id}', [FilterController::class,'filterData'])->name('filterData')->middleware('auth');

Route::post('/filterFiles', [FilterController::class,'filterFiles'])->name('filterFiles')->middleware('auth');

Route::post('/filterByDate', [FilterController::class,'filterByDate'])->name('filterByDate')->middleware('auth');

// -------------------------------------------------------------------------------