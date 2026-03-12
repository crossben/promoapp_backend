<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Log pour déboguer
            \Log::info('Requête produits reçue avec params:', $request->all());

            // Commencer avec une requête de base
            $query = Product::with(['category', 'store'])
                ->where('promo_end', '>', now())
                ->where('quantity', '>', 0);

            // Filtrage par catégorie
            if ($request->has('category_id') && $request->category_id > 0) {
                $query->where('category_id', $request->category_id);
            }

            // Filtrage par magasin
            if ($request->has('store_id')) {
                $query->where('store_id', $request->store_id);
            }

            // Filtrage par localisation
            if ($request->has('latitude') && $request->has('longitude')) {
                $latitude = (float) $request->latitude;
                $longitude = (float) $request->longitude;
                $radius = $request->get('radius', 10); // 10km par défaut

                \Log::info("Calcul distance: lat=$latitude, lon=$longitude, radius=$radius");

                // Méthode alternative plus simple pour éviter l'erreur SQL
                $storeIds = Store::select('id')
                    ->whereRaw("(
                        6371 * acos(
                            cos(radians(?)) * cos(radians(latitude)) * 
                            cos(radians(longitude) - radians(?)) + 
                            sin(radians(?)) * sin(radians(latitude))
                        )
                    ) <= ?", [$latitude, $longitude, $latitude, $radius])
                    ->pluck('id');

                \Log::info("Magasins dans le rayon: " . $storeIds->count());

                if ($storeIds->count() > 0) {
                    $query->whereIn('store_id', $storeIds);
                } else {
                    // Aucun magasin dans le rayon, retourner vide
                    return response()->json([
                        'products' => [
                            'data' => [],
                            'total' => 0,
                            'per_page' => $request->per_page ?? 20,
                            'current_page' => 1,
                            'last_page' => 1
                        ]
                    ]);
                }
            }

            // Trier les résultats
            if ($request->has('sort_by')) {
                switch ($request->sort_by) {
                    case 'discount':
                        // Calculer le pourcentage de réduction
                        $query->select('*')
                            ->selectRaw('((original_price - promo_price) / original_price) * 100 as discount_rate')
                            ->orderBy('discount_rate', 'desc');
                        break;
                    case 'price':
                        $query->orderBy('promo_price', 'asc');
                        break;
                    case 'newest':
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $products = $query->paginate($perPage);

            // Log pour vérifier les résultats
            \Log::info('Produits trouvés: ' . $products->count());

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur ProductController@index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with(['category', 'store'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'original_price' => 'required|numeric|min:0',
            'promo_price' => 'required|numeric|min:0|lt:original_price',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|in:kg,unit,liter,packet',
            'promo_start' => 'required|date',
            'promo_end' => 'required|date|after:promo_start',
            'category_id' => 'required|exists:categories,id',
            'store_id' => 'required|exists:stores,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Upload de l'image
            $imagePath = $request->file('image')->store('products', 'public');

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $imagePath,
                'original_price' => $request->original_price,
                'promo_price' => $request->promo_price,
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                'promo_start' => $request->promo_start,
                'promo_end' => $request->promo_end,
                'category_id' => $request->category_id,
                'store_id' => $request->store_id,
            ]);

            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'Produit ajouté avec succès'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Erreur création produit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du produit'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'original_price' => 'sometimes|numeric|min:0',
            'promo_price' => 'sometimes|numeric|min:0|lt:original_price',
            'quantity' => 'sometimes|numeric|min:0',
            'unit' => 'sometimes|string|in:kg,unit,liter,packet',
            'promo_start' => 'sometimes|date',
            'promo_end' => 'sometimes|date|after:promo_start',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except('image');

            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($product->image);
                $imagePath = $request->file('image')->store('products', 'public');
                $data['image'] = $imagePath;
            }

            $product->update($data);

            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'Produit mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour produit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    public function updateStock(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'purchased_quantity' => 'required|numeric|min:0.01|max:' . $product->quantity,
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $product->quantity -= $request->purchased_quantity;
            $product->save();

            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => 'Stock mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de mise à jour du stock'
            ], 500);
        }
    }

    public function byCategory($categoryId)
    {
        try {
            $products = Product::with(['category', 'store'])
                ->where('category_id', $categoryId)
                ->where('promo_end', '>', now())
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération'
            ], 500);
        }
    }

    public function byStore($storeId)
    {
        try {
            $products = Product::with(['category', 'store'])
                ->where('store_id', $storeId)
                ->where('promo_end', '>', now())
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération'
            ], 500);
        }
    }

    public function publicIndex(Request $request)
    {
        try {
            $products = Product::with(['category', 'store'])
                ->where('promo_end', '>', now())
                ->where('quantity', '>', 0)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur publicIndex: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'products' => []
            ]);
        }
    }

    private function sendPromoNotifications(Product $product)
    {
        \Log::info('Promotion créée pour le produit: ' . $product->name);
    }
}