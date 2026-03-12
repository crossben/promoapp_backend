<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    // Ajouter un produit au panier
    public function addToCart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|numeric|min:0.01'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            
            // Vérifier si l'utilisateur est un client
            if ($user->role === 'manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Les managers ne peuvent pas ajouter au panier'
                ], 403);
            }

            $product = Product::with('store')->find($request->product_id);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            if ($product->quantity <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit épuisé'
                ], 400);
            }

            if (now()->gt($product->promo_end)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promotion expirée'
                ], 400);
            }

            if ($request->quantity > $product->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantité non disponible. Stock restant: ' . $product->quantity . ' ' . $product->unit
                ], 400);
            }

            DB::beginTransaction();

            $existingCartItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('is_validated', false)
                ->first();

            if ($existingCartItem) {
                $existingCartItem->quantity += $request->quantity;
                $existingCartItem->save();
                $cartItem = $existingCartItem;
            } else {
                $cartItem = CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'store_id' => $product->store_id,
                    'quantity' => $request->quantity,
                    'is_validated' => false
                ]);
            }

            DB::commit();

            $cartItem->load(['product.category', 'store']);

            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté au panier',
                'cart_item' => $cartItem
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajout au panier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier: ' . $e->getMessage()
            ], 500);
        }
    }

    // Obtenir le panier de l'utilisateur
    public function getCart()
    {
        try {
            $user = auth()->user();
            
            $cartItems = CartItem::with(['product.category', 'store'])
                ->where('user_id', $user->id)
                ->where('is_validated', false)
                ->get();

            $total = $cartItems->sum(function ($item) {
                if ($item->product) {
                    return $item->product->promo_price * $item->quantity;
                }
                return 0;
            });

            return response()->json([
                'success' => true,
                'cart_items' => $cartItems,
                'total_items' => $cartItems->count(),
                'total_amount' => $total
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération panier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération du panier: ' . $e->getMessage()
            ], 500);
        }
    }

    // Mettre à jour la quantité d'un item
    public function updateQuantity(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|numeric|min:0.01'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $cartItem = CartItem::with('product')->find($id);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
            }

            if ($cartItem->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            if ($cartItem->is_validated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article déjà validé'
                ], 400);
            }

            $product = $cartItem->product;

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            if ($request->quantity > $product->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantité non disponible'
                ], 400);
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            $cartItem->load(['product', 'store']);

            return response()->json([
                'success' => true,
                'message' => 'Quantité mise à jour',
                'cart_item' => $cartItem
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour quantité: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    // Supprimer un item du panier
    public function removeFromCart($id)
    {
        try {
            $user = auth()->user();
            $cartItem = CartItem::find($id);

            if (!$cartItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé'
                ], 404);
            }

            if ($cartItem->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article supprimé du panier'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression panier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    // Valider le panier (dans le magasin)
    public function validateCart(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_id' => 'required|exists:stores,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            
            if ($user->role === 'manager') {
                return response()->json([
                    'success' => false,
                    'message' => 'Les managers ne peuvent pas valider de panier'
                ], 403);
            }

            $store = Store::find($request->store_id);
            
            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non trouvé'
                ], 404);
            }

            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $store->latitude,
                $store->longitude
            );

            if ($distance > 0.1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être dans le magasin pour valider le panier (distance: ' . round($distance * 1000) . 'm)',
                    'distance' => $distance
                ], 400);
            }

            DB::beginTransaction();

            $cartItems = CartItem::with('product')
                ->where('user_id', $user->id)
                ->where('store_id', $store->id)
                ->where('is_validated', false)
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Panier vide pour ce magasin'
                ], 400);
            }

            $validatedItems = [];
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                
                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Produit non trouvé'
                    ], 404);
                }

                if ($product->quantity < $cartItem->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Stock insuffisant pour: ' . $product->name,
                        'product_id' => $product->id
                    ], 400);
                }

                $product->quantity -= $cartItem->quantity;
                $product->save();

                $cartItem->update([
                    'is_validated' => true,
                    'validated_at' => now(),
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]);

                $validatedItems[] = $cartItem;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Panier validé avec succès! Stock mis à jour.',
                'validated_items' => count($validatedItems),
                'items' => $validatedItems
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validation panier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation du panier: ' . $e->getMessage()
            ], 500);
        }
    }

    // Historique des validations
    public function getHistory()
    {
        try {
            $user = auth()->user();
            
            $validatedItems = CartItem::with(['product.category', 'store'])
                ->where('user_id', $user->id)
                ->where('is_validated', true)
                ->orderBy('validated_at', 'desc')
                ->get();

            // Grouper par date
            $groupedHistory = [];
            foreach ($validatedItems as $item) {
                if ($item->validated_at) {
                    $date = $item->validated_at->format('Y-m-d');
                    if (!isset($groupedHistory[$date])) {
                        $groupedHistory[$date] = [];
                    }
                    $groupedHistory[$date][] = $item;
                }
            }

            return response()->json([
                'success' => true,
                'history' => $groupedHistory,
                'total_validations' => $validatedItems->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération historique: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération de l\'historique: ' . $e->getMessage()
            ], 500);
        }
    }

    // Calcul de distance (formule haversine)
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
}