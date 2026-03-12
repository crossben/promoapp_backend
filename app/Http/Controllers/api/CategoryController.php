<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Récupérer toutes les catégories de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            // Si l'utilisateur a un magasin, récupérer uniquement ses catégories
            if ($user->store) {
                $categories = Category::where('store_id', $user->store->id)
                    ->withCount(['products' => function($query) {
                        $query->where('promo_end', '>', now())
                              ->where('quantity', '>', 0);
                    }])
                    ->orderBy('name')
                    ->get();
            } else {
                $categories = collect();
            }

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur index categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération des catégories'
            ], 500);
        }
    }

    /**
     * Afficher une catégorie spécifique
     */
    public function show($id)
    {
        try {
            $category = Category::with(['products' => function($query) {
                $query->where('promo_end', '>', now())
                      ->where('quantity', '>', 0);
            }])->find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Catégorie non trouvée'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'category' => $category
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur show category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération'
            ], 500);
        }
    }

    /**
     * Ajouter une nouvelle catégorie
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'store_id' => 'required|exists:stores,id',
        ], [
            'name.required' => 'Le nom du rayon est obligatoire',
            'store_id.exists' => 'Magasin non valide',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Vérifier si le magasin appartient à l'utilisateur
            $user = auth()->user();
            if ($user->store->id != $request->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à créer un rayon dans ce magasin'
                ], 403);
            }

            $category = Category::create([
                'name' => $request->name,
                'icon' => $request->icon ?? 'category',
                'store_id' => $request->store_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Rayon créé avec succès',
                'category' => $category
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur création catégorie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de création: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une catégorie
     */
    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rayon non trouvé'
                ], 404);
            }

            // Vérifier que l'utilisateur possède ce magasin
            $user = auth()->user();
            if ($user->store->id != $category->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'icon' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $category->update($request->only(['name', 'icon']));

            return response()->json([
                'success' => true,
                'message' => 'Rayon mis à jour',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur update category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de mise à jour'
            ], 500);
        }
    }

    /**
     * Supprimer une catégorie
     */
    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rayon non trouvé'
                ], 404);
            }

            // Vérifier que l'utilisateur possède ce magasin
            $user = auth()->user();
            if ($user->store->id != $category->store_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }

            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer: il y a des produits dans ce rayon'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rayon supprimé'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur suppression catégorie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de suppression'
            ], 500);
        }
    }

    /**
     * Catégories par magasin - FONCTION CRITIQUE CORRIGÉE
     */
    public function byStore($storeId)
    {
        try {
            \Log::info('Récupération catégories pour store: ' . $storeId);

            // Vérifier si le magasin existe
            $store = Store::find($storeId);
            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non trouvé'
                ], 404);
            }

            // CORRECTION : Retirer la condition deleted_at qui n'existe pas
            $categories = Category::where('store_id', $storeId)
                ->withCount(['products' => function($query) {
                    // Retirer 'products.deleted_at is null' car pas de soft delete
                    $query->where('promo_end', '>', now())
                          ->where('quantity', '>', 0);
                }])
                ->orderBy('name')
                ->get();

            \Log::info('Catégories trouvées: ' . $categories->count());

            return response()->json([
                'success' => true,
                'categories' => $categories,
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur byStore: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Version publique (sans authentification)
     */
    public function publicIndex()
    {
        try {
            $categories = Category::withCount(['products' => function($query) {
                $query->where('promo_end', '>', now())
                      ->where('quantity', '>', 0);
            }])->get();

            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur publicIndex: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'categories' => []
            ]);
        }
    }
}