<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Purchase::with(['product', 'user'])
            ->orderBy('purchase_date', 'desc');

        // Filtrer par utilisateur (pour les managers)
        if ($user->isManager() && $request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Si c'est un client, il ne voit que ses achats
        if (!$user->isManager()) {
            $query->where('user_id', $user->id);
        }

        $purchases = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'purchases' => $purchases,
            'total' => $purchases->total()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::findOrFail($request->product_id);

        // Vérifier le stock
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'message' => 'Stock insuffisant. Disponible: ' . $product->quantity . ' ' . $product->unit
            ], 422);
        }

        // Vérifier que la promotion est toujours valide
        if (now() > $product->promo_end) {
            return response()->json([
                'message' => 'Cette promotion a expiré'
            ], 422);
        }

        // Calculer le total
        $totalPrice = $request->quantity * $product->promo_price;

        // Créer l'achat
        $purchase = Purchase::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'total_price' => $totalPrice,
            'purchase_date' => now(),
        ]);

        // Mettre à jour le stock
        $product->quantity -= $request->quantity;
        $product->save();

        return response()->json([
            'purchase' => $purchase,
            'message' => 'Achat enregistré avec succès'
        ], 201);
    }

    public function byUser($userId)
    {
        $purchases = Purchase::with(['product'])
            ->where('user_id', $userId)
            ->orderBy('purchase_date', 'desc')
            ->paginate(20);

        // Calculer les statistiques
        $totalSpent = Purchase::where('user_id', $userId)->sum('total_price');
        $totalItems = Purchase::where('user_id', $userId)->sum('quantity');

        return response()->json([
            'purchases' => $purchases,
            'stats' => [
                'total_spent' => $totalSpent,
                'total_items' => $totalItems,
                'total_purchases' => $purchases->total()
            ]
        ]);
    }

    public function byProduct($productId)
    {
        $purchases = Purchase::with(['user'])
            ->where('product_id', $productId)
            ->orderBy('purchase_date', 'desc')
            ->paginate(20);

        return response()->json([
            'purchases' => $purchases
        ]);
    }

    public function userHistory(Request $request)
    {
        $user = $request->user();

        $purchases = Purchase::with(['product' => function($query) {
            $query->withTrashed(); // Inclure les produits supprimés
        }])
            ->where('user_id', $user->id)
            ->orderBy('purchase_date', 'desc')
            ->paginate(20);

        // Calculer les économies
        $totalSpent = $purchases->sum('total_price');
        $totalOriginalPrice = 0;
        
        foreach ($purchases as $purchase) {
            if ($purchase->product) {
                $totalOriginalPrice += $purchase->quantity * $purchase->product->original_price;
            }
        }
        
        $totalSaved = $totalOriginalPrice - $totalSpent;

        return response()->json([
            'purchases' => $purchases,
            'summary' => [
                'total_purchases' => $purchases->total(),
                'total_spent' => $totalSpent,
                'total_saved' => $totalSaved,
                'total_items' => $purchases->sum('quantity')
            ]
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isManager()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Statistiques pour le manager
        $store = $user->store;
        
        if (!$store) {
            return response()->json(['message' => 'Aucun magasin associé'], 404);
        }

        // Produits les plus vendus
        $topProducts = Purchase::selectRaw('product_id, SUM(quantity) as total_sold, SUM(total_price) as total_revenue')
            ->whereHas('product', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->with('product')
            ->limit(10)
            ->get();

        // Ventes par jour (derniers 30 jours)
        $dailySales = Purchase::selectRaw('DATE(purchase_date) as date, COUNT(*) as transactions, SUM(total_price) as revenue')
            ->whereHas('product', function($query) use ($store) {
                $query->where('store_id', $store->id);
            })
            ->where('purchase_date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Chiffre d'affaires total
        $totalRevenue = Purchase::whereHas('product', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->sum('total_price');

        // Nombre total de transactions
        $totalTransactions = Purchase::whereHas('product', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->count();

        return response()->json([
            'stats' => [
                'total_revenue' => $totalRevenue,
                'total_transactions' => $totalTransactions,
                'top_products' => $topProducts,
                'daily_sales' => $dailySales,
                'store' => $store->only(['id', 'name', 'address'])
            ]
        ]);
    }
}