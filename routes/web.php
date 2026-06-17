<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\testController;
use App\Http\Controllers\backendController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });
Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');

Route::get('/test', [testController::class, 'test'])->name('test');
Route::post('/chat/store', [testController::class, 'store'])->name('chat.store');
Route::get('/chat/conversations', [testController::class, 'conversations'])->name('chat.conversations');
Route::get('/chat/conversation/{id}', [testController::class, 'conversation'])->name('chat.conversation');



Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/conversations', [backendController::class, 'conversations'])->name('admin.conversations');
    Route::get('/conversations/{conversation}', [BackendController::class, 'conversationMessages'])->name('admin.messages');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
