<?php

namespace App\Http\Controllers;

use App\Services\KeycloakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private $keycloakService;

    public function __construct(KeycloakService $keycloakService)
    {
        $this->keycloakService = $keycloakService;
    }

    public function getUserInfo(Request $request): JsonResponse
    {
        $user = $request->get('user'); // Injecté par le middleware

        return response()->json([
            'user' => $user,
            'message' => 'Utilisateur authentifié avec succès'
        ]);
    }

    public function refreshToken(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token manquant'], 400);
        }

        try {
            $tokens = $this->keycloakService->refreshToken($refreshToken);

            return response()->json($tokens);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function logout(Request $request)
    {
        // Keycloak gère la déconnexion côté client
        return response()->json(['message' => 'Déconnecté avec succès']);
    }
}
