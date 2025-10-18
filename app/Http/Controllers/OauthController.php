<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Oauth;
use Illuminate\Validation\ValidationException;

class OauthController extends Controller
{
    public function generateToken(Request $request)
    {
        $clientId = $request->header('X-CLIENT-ID');
        $signature = $request->header('X-SIGNATURE');
        $timestamp = $request->header('X-TIMESTAMP');

        if (!$clientId) {
            return response()->json([
                'error' => 'client id is required {E01V}'
            ], 400);
        }

        if (!$signature) {
            return response()->json([
                'error' => 'signature is required {E01V}'
            ], 400);
        }

        if (!$timestamp) {
            return response()->json([
                'error' => 'timestamp is required {E01V}'
            ], 400);
        }

        try {

            $validated = $request->validate([
                'grant_type' => 'required'
            ], [
                'grant_type.required' => 'grant_type is required {E01V}'
            ]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'error' => $firstError
            ]);
        }

        if ($validated['grant_type'] !== 'client_credentials') {
            return response()->json([
                'error' => 'Unauthorized access credentials'
            ], 401);
        }

        $getOauthConfig = Oauth::select([
            'client_secret',
            'token_expired_at',
            'available_token'
        ])->where(['client_id' => $clientId])
            ->first();

        if (!$getOauthConfig) {
            return response()->json([
                'error' => 'Invalid client {E0I}'
            ], 401);
        }

        $currentTokenExpiredAt = $getOauthConfig->token_expired_at;

        // jika token belum expired
        if (date('Y-m-d H:i:s') <= date('Y-m-d H:i:s', strtotime($currentTokenExpiredAt))) {
            return response()->json([
                'data' => [
                    'access_token' => $getOauthConfig->available_token,
                    'expired_at' => 1800
                ]
            ]);
        }

        $secretKey = $getOauthConfig->client_secret;

        $validSignature = $this->generateSignature($clientId, $secretKey, $timestamp);

        if ($signature !== $validSignature) {
            return response()->json([
                'error' => 'Invalid signature {E0S}',
                'must_be' => $validSignature
            ], 401);
        }

        $accessToken = bin2hex(random_bytes(32)); // random 64 karakter hex
        $tokenExpired = date('Y-m-d H:i:s', strtotime('+30 minute'));

        Oauth::where(['client_id' => $clientId])
            ->update([
                'available_token' => $accessToken,
                'token_expired_at' => $tokenExpired
            ]);

        return response()->json([
            'data' => [
                'access_token' => $accessToken,
                'expired_at' => 1800
            ]
        ]);
    }

    public function generateSignature($clientId, $secretKey, $timestamp)
    {
        $dataHash = $clientId . ':' . $timestamp;
        return base64_encode(hash_hmac('sha256', $dataHash, $secretKey, true));
    }
}
