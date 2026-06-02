<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends ApiController
{
    /**
     * POST /api/v1/login
     * Issue a Sanctum personal access token.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('Email atau kata sandi salah.', 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return $this->errorResponse('Akun Anda telah dinonaktifkan. Hubungi administrator.', 403);
        }

        $token = $user->createToken('api-v1-token', ['*'], now()->addDays(30))->plainTextToken;

        ActivityLogService::log('api_login', 'Login via REST API v1');

        return $this->successResponse([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
            'user'       => $this->userArray($user),
        ], 'Login berhasil.');
    }

    /**
     * POST /api/v1/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'role'      => 'user',
            'is_active' => true,
        ]);

        $token = $user->createToken('api-v1-token', ['*'], now()->addDays(30))->plainTextToken;

        ActivityLogService::log('api_register', 'Registrasi akun baru via REST API v1');

        return $this->successResponse([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60,
            'user'       => $this->userArray($user),
        ], 'Registrasi berhasil.', 201);
    }

    /**
     * POST /api/v1/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logout berhasil.');
    }

    /**
     * GET /api/v1/user
     */
    public function user(Request $request): JsonResponse
    {
        return $this->successResponse($this->userArray($request->user()));
    }

    /* ── Private Helpers ── */

    private function userArray(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'role'       => $user->role,
            'is_active'  => $user->is_active,
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
