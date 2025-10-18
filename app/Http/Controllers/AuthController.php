<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::channel('single')->info(json_encode([
            'header' => $request->header(),
            'body' => $request->all()
        ]));

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'roles' => 'int',
            ]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();

            return $this->responseFail($firstError, 'E0FV', 400);
        }

        try {
            DB::beginTransaction();
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $validated['roles'] ?? null
            ]);
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'user' => [
                    'email' => $user->email,
                    'name' => $user->name
                ],
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->responseFail("Transaction failed", "E0TRX", 502);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return $this->responseFail('Invalid email or password', 'E0L', 400);
        }

        $token = $user->createToken('HykeeApp')->plainTextToken;

        return $this->responseSuccess([
            'token' => $token
        ]);
    }
}
