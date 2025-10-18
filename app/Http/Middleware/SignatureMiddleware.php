<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Oauth;

class SignatureMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next) /* : Response */
    {
        $hykeeToken = $request->header('X-HYKEE-AUTHORIZATION');
        $hykeeSignature = $request->header('X-SIGNATURE');
        $clientId = $request->header('X-CLIENT-ID');


        if (!$hykeeToken) {
            return response()->json([
                'erorr' => 'Hykee Token is required {E01V}'
            ]);
        }

        if (!$hykeeSignature) {
            return response()->json([
                'error' => 'Hykee Signature is required {E01V}'
            ]);
        }

        if (!$clientId) {
            return response()->json([
                'error' => 'Client ID is required {E01V}'
            ]);
        }

        $token = trim(preg_replace('/Bearer/i', '', $hykeeToken));

        $isTokenAuthorized = $this->authorizeToken($clientId, $token);
        if (isset($isTokenAuthorized['error'])) {
            return response()->json([
                'error' => $isTokenAuthorized['error']
            ]);
        }

        if ($hykeeSignature !== $this->generateSignature($clientId, $token)) {
            return response()->json(['error' => 'Invalid signature. {E4010}']);
        }

        return $next($request);
    }


    public function authorizeToken($clientId, $token): array
    {
        $oauth = Oauth::select([
            'available_token',
            'token_expired_at'
        ])
            ->where(['client_id' => $clientId])
            ->first();

        if (!$oauth) {
            return ['error' => 'Invalid client id. {E4010}'];
        }

        if ($token !== $oauth->available_token) {
            return ['error' => 'Unauthorized. {E4010}'];
        }

        if (date('Y-m-d H:i:s', strtotime($oauth->token_expired_at)) <= date('Y-m-d H:i:s')) {
            return ['error' => 'Token expired. {E4010}'];
        }

        return ['success' => 'ok'];
    }

    public function generateSignature($clientId, $token)
    {
        $oauth = Oauth::select([
            'client_secret'
        ])
            ->where(['client_id' => $clientId])
            ->first();

        $secret = $oauth->client_secret;

        $generateHash = $clientId . ':' . $token . ':' . $secret;

        return base64_encode(hash_hmac('sha256', $generateHash, $secret, true));
    }
}
