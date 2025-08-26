<?php
// app/Services/KeycloakService.php

namespace App\services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class KeycloakService
{
    private $httpClient;
    private $baseUrl;
    private $realm;
    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->baseUrl = env('KEYCLOAK_BASE_URL');
        $this->realm = env('KEYCLOAK_REALM');
        $this->clientId = env('KEYCLOAK_CLIENT_ID');
        $this->clientSecret = env('KEYCLOAK_CLIENT_SECRET');
    }

    public function getUserInfo(string $accessToken): array
    {
        try {
            $response = $this->httpClient->get(
                "{$this->baseUrl}/realms/{$this->realm}/protocol/openid_connect/userinfo",
                [
                    'headers' => [
                        'Authorization' => "Bearer {$accessToken}",
                        'Accept' => 'application/json',
                    ]
                ]
            );

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \Exception('Erreur lors de la récupération des informations utilisateur');
        }
    }

    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = $this->httpClient->post(
                "{$this->baseUrl}/realms/{$this->realm}/protocol/openid_connect/token",
                [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret,
                        'refresh_token' => $refreshToken,
                    ]
                ]
            );

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \Exception('Erreur lors du rafraîchissement du token');
        }
    }
}
