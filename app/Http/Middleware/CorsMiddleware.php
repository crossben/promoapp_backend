<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle(Request $request, Closure $next)
    {
        // Pour une app mobile, vous pouvez autoriser toutes les origines
        // ou gérer via token d'API plutôt que CORS
        
        $headers = [
            'Access-Control-Allow-Origin' => '*',  // Simple pour mobile
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Max-Age' => '86400',
        ];

        if ($request->getMethod() === 'OPTIONS') {
            return response()->json('OK', 200, $headers);
        }

        $response = $next($request);
        
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
    // public function handle(Request $request, Closure $next)
    // {
    //     // Liste des domaines autorisés
    //     // $allowedOrigins = [
    //     //     'http://localhost:3000',
    //     //     'http://localhost:3001', 
    //     //     'https://nos-provisions.netlify.app',
    //     //     'https://nosprovisions.com',
    //     //     'https://*.netlify.app'
    //     // ];

    //     // Vérifier l'origine de la requête
    //     $origin = $request->headers->get('Origin');
        
    //     if (in_array($origin, $allowedOrigins)) {
    //         $allowOrigin = $origin;
    //     } else {
    //         // Autoriser toutes les origines en développement
    //         $allowOrigin = '*';
    //     }

    //     // Headers CORS
    //     $headers = [
    //         'Access-Control-Allow-Origin' => $allowOrigin,
    //         'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    //         'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept',
    //         'Access-Control-Allow-Credentials' => 'true',
    //         'Access-Control-Max-Age' => '86400',
    //     ];

    //     // Gérer les requêtes OPTIONS (preflight)
    //     if ($request->getMethod() === 'OPTIONS') {
    //         $response = response('', 200);
    //     } else {
    //         $response = $next($request);
    //     }

    //     // Ajouter les headers CORS à la réponse
    //     foreach ($headers as $key => $value) {
    //         $response->headers->set($key, $value);
    //     }

    //     return $response;
    // }
}