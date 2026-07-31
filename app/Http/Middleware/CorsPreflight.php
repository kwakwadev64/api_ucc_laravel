<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsPreflight
{
    public function handle(Request $request, Closure $next)
    {
        // Si c'est un preflight OPTIONS, répondre immédiatement
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN')
                ->header('Access-Control-Max-Age', '600');
        }

        $response = $next($request);
        
        // Ajouter les headers CORS sur toutes les réponses
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
        
        return $response;
    }
}