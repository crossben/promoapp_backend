<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;        // 'Api' avec majuscule !
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\CartController;
use App\Http\Controllers\api\StoreController;
use App\Http\Controllers\api\PurchaseController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\CategoryController;

// Route de test
Route::get('/test', function() {
    return response()->json(['status' => 'API fonctionne', 'time' => now()]);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes publiques pour les magasins (sans authentification)
Route::get('/stores/nearby', [StoreController::class, 'nearby']); // IMPORTANT: Route publique
Route::get('/public/stores', [StoreController::class, 'publicIndex']);
Route::get('/public/products', [ProductController::class, 'publicIndex']);
Route::get('/public/categories', [CategoryController::class, 'publicIndex']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Test route
    Route::get('/test-auth', function() {
        return response()->json([
            'authenticated' => true,
            'user' => auth()->user()->only(['id', 'name', 'email'])
        ]);
    });
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [UserController::class, 'getCurrentUser']);
    Route::put('/user/fcm-token', [UserController::class, 'updateFcmToken']);
    
    // Stores
    Route::get('/stores/user/check', [StoreController::class, 'checkUserStore']);
    Route::post('/stores/create-for-user', [StoreController::class, 'createForCurrentUser']);
    
    // Stores autres routes (authentifiées)
    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{store}', [StoreController::class, 'show']);
    Route::post('/stores', [StoreController::class, 'store'])->middleware('role:manager');
    Route::put('/stores/{store}', [StoreController::class, 'update'])->middleware('role:manager');
    Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->middleware('role:manager');
    Route::get('/stores/{store}/products', [StoreController::class, 'products']);
    
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store'])->middleware('role:manager');
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('role:manager');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('role:manager');
    Route::put('/products/{product}/stock', [ProductController::class, 'updateStock']);
    Route::get('/products/category/{category}', [ProductController::class, 'byCategory']);
    Route::get('/products/store/{store}', [ProductController::class, 'byStore']);
    // Routes pour les images de produits
    Route::get('/products/{filename}', [ProductController::class, 'serveImage']);
    
    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('role:manager');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('role:manager');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('role:manager');
    Route::get('/categories/by-store/{storeId}', [CategoryController::class, 'byStore']);
    
    // Purchases
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::get('/purchases/user/{user}', [PurchaseController::class, 'byUser'])->middleware('role:manager');
    Route::get('/purchases/product/{product}', [PurchaseController::class, 'byProduct'])->middleware('role:manager');
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::get('/purchases/history', [PurchaseController::class, 'userHistory']);
    Route::get('/purchases/stats', [PurchaseController::class, 'stats'])->middleware('role:manager');
    
    // Notifications
    Route::get('/notifications', [UserController::class, 'notifications']);
    Route::post('/notifications/read/{id}', [UserController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [UserController::class, 'markAllAsRead']);

     // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'getCart']);
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::put('/update/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/remove/{id}', [CartController::class, 'removeFromCart']);
        Route::post('/validate', [CartController::class, 'validateCart']);
        Route::get('/history', [CartController::class, 'getHistory']);
    });
});
