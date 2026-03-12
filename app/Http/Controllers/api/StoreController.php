<?php

namespace App\Http\Controllers\Api;  // Changez 'Api' en 'api' (minuscules)

use App\Http\Controllers\Controller;  // Importez le Controller de base
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{
    /**
     * Vérifier si l'utilisateur a un magasin
     */
    public function checkUserStore()
    {
        try {
            $user = auth()->user();
            
            // Simple query
            $store = Store::where('manager_id', $user->id)->first();
            
            return response()->json([
                'success' => true,
                'has_store' => $store !== null,
                'store' => $store
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    /**
     * Créer un magasin pour le manager connecté - SANS is_active
     */
    public function createForCurrentUser(Request $request)
    {
        try {
            // Vérifier l'authentification
            $user = auth()->user();
            
            // Validation simple
            if (empty($request->name) || empty($request->address) || empty($request->phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nom, adresse et téléphone sont obligatoires'
                ], 422);
            }

            // Vérifier si l'utilisateur a déjà un magasin
            $existingStore = Store::where('manager_id', $user->id)->first();
            if ($existingStore) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà un magasin'
                ], 400);
            }
            
            // Créer le magasin SANS is_active
            $store = Store::create([
                'name' => trim($request->name),
                'address' => trim($request->address),
                'phone' => trim($request->phone),
                'latitude' => $request->latitude ?? 48.8566,
                'longitude' => $request->longitude ?? 2.3522,
                'manager_id' => $user->id,
                'opening_time' => '08:00',
                'closing_time' => '20:00',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Magasin créé avec succès',
                'store' => $store
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur interne: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Store::with(['manager:id,name,email', 'categories:id,name,store_id']);

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $stores = $query->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'stores' => $stores,
                'total' => $stores->total()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $store = Store::with(['manager:id,name,email', 'categories:id,name,store_id'])->find($id);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'store' => $store
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'phone' => 'nullable|string|max:20',
                'opening_time' => 'required|date_format:H:i',
                'closing_time' => 'required|date_format:H:i|after:opening_time',
                'manager_id' => 'required|exists:users,id',
            ]);

            $store = Store::create($validated);

            return response()->json([
                'success' => true,
                'store' => $store,
                'message' => 'Magasin créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $store = Store::find($id);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non trouvé'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|string',
                'latitude' => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',
                'phone' => 'nullable|string|max:20',
                'opening_time' => 'sometimes|date_format:H:i',
                'closing_time' => 'sometimes|date_format:H:i|after:opening_time',
            ]);

            $store->update($validated);

            return response()->json([
                'success' => true,
                'store' => $store,
                'message' => 'Magasin mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $store = Store::find($id);
            
            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non trouvé'
                ], 404);
            }

            if ($store->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce magasin car il contient des produits'
                ], 422);
            }

            $store->delete();

            return response()->json([
                'success' => true,
                'message' => 'Magasin supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    /**
     * Magasins à proximité
     */
    public function nearby(Request $request)
    {
        try {
            Log::info('Requête nearby reçue:', $request->all());
            
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|numeric|min:1|max:50',
            ]);

            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->radius ?? 10;

            Log::info("Recherche magasins près de: lat=$latitude, lng=$longitude, radius=$radius km");

            // Requête SQL pour calculer la distance
            $stores = Store::selectRaw("*, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", 
                [$latitude, $longitude, $latitude])
                ->having('distance', '<', $radius)
                ->orderBy('distance')
                ->with(['categories:id,name'])
                ->limit(20)
                ->get();

            Log::info("Nombre de magasins trouvés: " . $stores->count());

            return response()->json([
                'success' => true,
                'stores' => $stores,
                'current_location' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur nearby: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    public function products($storeId)
    {
        try {
            $store = Store::with(['products' => function($query) {
                $query->where('promo_end', '>', now())
                      ->where('quantity', '>', 0)
                      ->limit(50);
            }])->find($storeId);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Magasin non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'store' => $store
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    public function publicIndex()
    {
        try {
            $stores = Store::with(['categories:id,name'])
                ->orderBy('name')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'stores' => $stores
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur publicIndex: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'stores' => [],
                'message' => 'Erreur serveur'
            ]);
        }
    }
}