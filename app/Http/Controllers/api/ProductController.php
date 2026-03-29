<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('Requête création produit reçue:', $request->all());
        \Log::info('Fichiers:', $request->file() ?: []);

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'quantity' => 'required|numeric|min:1',
            'category_id' => 'required|exists:categories,id',
            'store_id' => 'required|exists:stores,id',
        ], [
            'image.required' => 'La photo est obligatoire',
            'image.image' => 'Le fichier doit être une image',
            'quantity.required' => 'La quantité est obligatoire',
            'category_id.required' => 'Le rayon est obligatoire',
            'store_id.required' => 'Le magasin est obligatoire',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            if (!$user->store || $user->store->id != $request->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            $category = Category::find($request->category_id);
            if (!$category || $category->store_id != $request->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catégorie non valide pour ce magasin'
                ], 422);
            }

            DB::beginTransaction();

            // Sauvegarder l'image
            $imageName = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                
                // Sauvegarder dans public/products/
                $image->move(public_path('products'), $imageName);
                
                \Log::info('Image sauvegardée:', [
                    'filename' => $imageName,
                    'path' => public_path('products/' . $imageName)
                ]);
            }

            // Calculer les dates
            $now = Carbon::now();
            $promoEnd = $now->copy()->addHours(24);

            // Créer le produit avec toutes les données requises
            $productData = [
                'image' => $imageName,
                'quantity' => $request->quantity,
                'category_id' => $request->category_id,
                'store_id' => $request->store_id,
                'name' => $request->name ?? 'Produit frais',
                'description' => $request->description ?? 'Produit frais en promotion',
                'original_price' => $request->original_price ?? 1.99,
                'promo_price' => $request->promo_price ?? 0.99,
                'unit' => $request->unit ?? 'unité',
                'promo_start' => $now,
                'promo_end' => $promoEnd,
                'created_at' => $now, // AJOUT IMPORTANT: createdAt pour les notifications
            ];

            \Log::info('Données produit:', $productData);

            $product = Product::create($productData);
            $product->load(['category', 'store']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produit créé avec succès',
                'product' => $product
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création produit:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            \Log::info('Requête produits reçue:', $request->all());
            
            $query = Product::with(['category', 'store'])
                ->where('quantity', '>', 0);

            // FILTRE IMPORTANT: Toujours filtrer par défaut les promotions actives
            $activeOnly = $request->has('active_only') 
                ? filter_var($request->active_only, FILTER_VALIDATE_BOOLEAN)
                : true;
                
            if ($activeOnly) {
                $query->where('promo_end', '>', Carbon::now());
                \Log::info('Filtrage des promotions actives seulement');
            } else {
                \Log::info('Affichage de TOUTES les promotions (même expirées)');
            }

            if ($request->has('category_id') && $request->category_id > 0) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('store_id')) {
                $query->where('store_id', $request->store_id);
            }

            if ($request->has('latitude') && $request->has('longitude')) {
                $latitude = $request->latitude;
                $longitude = $request->longitude;
                $radius = $request->get('radius', 10);

                $query->whereHas('store', function($q) use ($latitude, $longitude, $radius) {
                    $q->select(DB::raw('*, (6371 * acos(cos(radians(' . $latitude . ')) * cos(radians(latitude)) * cos(radians(longitude) - radians(' . $longitude . ')) + sin(radians(' . $latitude . ')) * sin(radians(latitude)))) AS distance'))
                      ->having('distance', '<', $radius)
                      ->orderBy('distance', 'asc');
                });
            }

            $sortBy = $request->get('sort_by', 'created_at');
            if (in_array($sortBy, ['created_at', 'promo_end', 'quantity'])) {
                $query->orderBy($sortBy, 'desc');
            }

            $products = $query->paginate($request->get('per_page', 15));

            \Log::info('Produits récupérés: ' . $products->total());

            return response()->json([
                'success' => true,
                'products' => $products
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération produits: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération des produits'
            ], 500);
        }
    }

    public function publicIndex(Request $request)
    {
        return $this->index($request);
    }

    public function show($id)
    {
        try {
            $product = Product::with(['category', 'store'])->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            // Ajouter l'URL de l'image
            $product->image_url = $product->image 
                ? url('/products/' . $product->image)
                : null;

            return response()->json([
                'success' => true,
                'product' => $product
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération produit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération'
            ], 500);
        }
    }

    public function byStore($storeId)
    {
        try {
            \Log::info('Récupération produits pour magasin ID: ' . $storeId);
            
            $products = Product::with(['category', 'store'])
                ->where('store_id', $storeId)
                ->where('quantity', '>', 0)
                ->where('promo_end', '>', Carbon::now()) // IMPORTANT: filtrer les promotions actives
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Produits actifs trouvés pour magasin ' . $storeId . ': ' . $products->count());

            // Ajouter l'URL complète de l'image pour chaque produit
            $products->transform(function ($product) {
                $product->image_url = $product->image 
                    ? url('/products/' . $product->image)
                    : null;
                return $product;
            });

            return response()->json([
                'success' => true,
                'products' => $products
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur produits par magasin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    // Méthode pour servir les images
    public function serveImage($filename)
    {
        try {
            $path = public_path('products/' . $filename);
            
            if (!file_exists($path)) {
                \Log::error('Image non trouvée: ' . $filename . ' dans ' . $path);
                return response()->json([
                    'success' => false,
                    'message' => 'Image non trouvée: ' . $filename
                ], 404);
            }

            $mime = mime_content_type($path);
            
            return response()->file($path, [
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000',
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur service image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de chargement de l\'image'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            $user = auth()->user();
            if ($user->store->id != $product->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'quantity' => 'nullable|numeric|min:0',
                'promo_end' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image
                if ($product->image && file_exists(public_path('products/' . $product->image))) {
                    unlink(public_path('products/' . $product->image));
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('products'), $imageName);
                
                $product->image = $imageName;
            }

            if ($request->has('quantity')) {
                $product->quantity = $request->quantity;
            }

            if ($request->has('promo_end')) {
                $product->promo_end = $request->promo_end;
            }

            $product->save();
            $product->load(['category', 'store']);

            // Ajouter l'URL de l'image
            $product->image_url = $product->image 
                ? url('/products/' . $product->image)
                : null;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produit mis à jour',
                'product' => $product
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur mise à jour produit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de mise à jour'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produit non trouvé'
                ], 404);
            }

            $user = auth()->user();
            if ($user->store->id != $product->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            if ($product->image && file_exists(public_path('products/' . $product->image))) {
                unlink(public_path('products/' . $product->image));
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur suppression produit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de suppression'
            ], 500);
        }
    }

    public function updateStock(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $product = Product::findOrFail($id);
            $user = auth()->user();

            if ($user->store->id != $product->store_id) {
                return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
            }

            $product->update(['quantity' => $request->quantity]);

            return response()->json([
                'success' => true,
                'message' => 'Stock mis à jour',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    public function byCategory($categoryId)
    {
        try {
            $products = Product::with(['category', 'store'])
                ->where('category_id', $categoryId)
                ->where('quantity', '>', 0)
                ->where('promo_end', '>', Carbon::now())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
}