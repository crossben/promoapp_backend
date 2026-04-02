<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function getCurrentUser(Request $request)
    {
        $user = $request->user()->load('store');
        
        return response()->json([
            'user' => $user
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $user = $request->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'message' => 'Token FCM mis à jour'
        ]);
    }

    public function notifications(Request $request)
    {
        $user = $request->user();
        
        // Récupérer les notifications (à implémenter avec le système de notifications)
        $notifications = [
            // Notifications simulées
            [
                'id' => 1,
                'title' => 'Nouvelle promotion',
                'message' => 'Promotion sur les fruits et légumes',
                'read' => false,
                'created_at' => now()->toDateTimeString()
            ]
        ];

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        // Marquer une notification comme lue
        return response()->json([
            'message' => 'Notification marquée comme lue'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        // Marquer toutes les notifications comme lues
        return response()->json([
            'message' => 'Toutes les notifications marquées comme lues'
        ]);
    }

    /**
     * Mettre à jour les informations de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'sometimes|string|max:20|nullable',
            'current_password' => 'required_with:password|string|min:6',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        // Vérifier le mot de passe actuel si on veut changer le mot de passe
        if ($request->has('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Le mot de passe actuel est incorrect'
                ], 422);
            }
            $validated['password'] = Hash::make($request->password);
        }

        // Supprimer current_password et password_confirmation du tableau validated
        unset($validated['current_password']);
        
        // Mettre à jour l'utilisateur
        $user->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user->fresh('store')
        ]);
    }

    /**
     * Supprimer le compte utilisateur connecté
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        // Vérifier le mot de passe
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Le mot de passe est incorrect'
            ], 422);
        }

        // Supprimer l'utilisateur
        $user->delete();

        // Révoquer tous les tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Compte supprimé avec succès'
        ]);
    }

    /**
     * NOUVELLE METHODE: Mettre à jour les informations d'un utilisateur par son ID (avec mot de passe)
     */
    public function update(Request $request, $id)
    {
        // Trouver l'utilisateur par son ID
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'sometimes|string|max:20|nullable',
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        // Hasher le mot de passe si présent
        if ($request->has('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        // Mettre à jour l'utilisateur
        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour avec succès',
            'user' => $user->fresh('store')
        ]);
    }

    /**
     * NOUVELLE METHODE: Supprimer un utilisateur par son ID
     */
    public function delete($id)
    {
        $user = User::findOrFail($id);
        
        // Supprimer l'utilisateur
        $user->delete();

        // Révoquer tous les tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ]);
    }
}