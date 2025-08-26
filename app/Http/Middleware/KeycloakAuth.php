<?php
// app/Http/Middleware/KeycloakAuth.php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KeycloakAuth
{
    private $httpClient;
    private $keycloakBaseUrl;
    private $realm;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->keycloakBaseUrl = env('KEYCLOAK_BASE_URL');
        $this->realm = env('KEYCLOAK_REALM');
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json(['error' => 'Token manquant'], 401);
        }

        try {
            $decodedToken = $this->validateToken($token);
            $request->merge(['user' => $decodedToken]);

            return $next($request);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token invalide'], 401);
        }
    }

    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        return substr($authHeader, 7);
    }

    private function validateToken(string $token): object
    {
        // Récupérer les clés publiques de Keycloak
        $jwksUri = "{$this->keycloakBaseUrl}/realms/{$this->realm}/protocol/openid_connect/certs";
        $response = $this->httpClient->get($jwksUri);
        $jwks = json_decode($response->getBody(), true);

        // Décoder le header du JWT pour obtenir le kid
        $header = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], explode('.', $token)[0])), true);

        // Trouver la clé correspondante
        $key = null;
        foreach ($jwks['keys'] as $jwk) {
            if ($jwk['kid'] === $header['kid']) {
                $key = $this->jwkToPem($jwk);
                break;
            }
        }

        if (!$key) {
            throw new Exception('Clé non trouvée');
        }

        return JWT::decode($token, new Key($key, 'RS256'));
    }

    private function jwkToPem(array $jwk): string
    {
        // Conversion JWK vers PEM (simplifié)
        $n = base64_decode(strtr($jwk['n'], '-_', '+/'));
        $e = base64_decode(strtr($jwk['e'], '-_', '+/'));

        // Construction du certificat PEM
        $modulus = unpack('H*', $n)[1];
        $exponent = unpack('H*', $e)[1];

        $rsa = "30820122300d06092a864886f70d01010105000382010f003082010a0282010100{$modulus}0203{$exponent}";
        $der = hex2bin($rsa);
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }
}
