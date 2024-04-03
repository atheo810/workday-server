<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $user = User::where('email', $credentials['email'])->first();

            $user->tokens()->delete();

            $expiration = now()->addDay(1);
            $generateToken = $user->createToken(
                name: $user->value('uuid'),
                expiresAt: $expiration
            );

            return response()->json([
                'id' => $user->value('uuid'),
                'token' => $generateToken->plainTextToken,
                'message' => 'Login successful. Welcome back!'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' =>
            'Something went wrong, please try again later'], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout successful.'], 200);
    }
}
