<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}