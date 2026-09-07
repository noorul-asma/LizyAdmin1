<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => ['This account has been disabled.']]);
        }

        // Named per-device (not a static 'lizy-admin' string for every
        // login) so the Active Sessions list can actually tell devices
        // apart - previously every session showed the identical name,
        // making a second device's session indistinguishable from the
        // first once both appeared in the list.
        $token = $user->createToken($request->userAgent() ?: 'Unknown device')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->slug,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('role.permissions');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'role' => $user->role?->slug,
            'permissions' => $user->role?->permissions->pluck('slug'),
        ]);
    }

    /** Settings page → Admin & Security → "Update Profile". */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $user->update($data);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role?->slug,
            ],
        ]);
    }

    /** Settings page → Admin & Security → "Change Password". */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($data['new_password'])]);

        return response()->json(['message' => 'Password updated.']);
    }

    /** Settings page → Admin & Security → "Active Sessions" list. */
    public function sessions(Request $request)
    {
        $current = $request->user()->currentAccessToken();

        $tokens = $request->user()->tokens()->orderByDesc('last_used_at')->get();

        return response()->json([
            'data' => $tokens->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
                'is_current' => $current && (int) $token->id === (int) $current->id,
            ]),
        ]);
    }

    /** Settings page → Admin & Security → "Logout From Other Devices" (bulk). */
    public function logoutOtherSessions(Request $request)
    {
        $current = $request->user()->currentAccessToken();

        $count = $request->user()->tokens()
            ->when($current, fn ($query) => $query->where('id', '!=', $current->id))
            ->delete();

        return response()->json(['message' => "{$count} other session(s) logged out."]);
    }

    /**
     * Settings page → Admin & Security → "Active Sessions" → per-row "Log out".
     * Revokes a single named session (e.g. one specific other device) rather
     * than every other session at once. Scoped to $request->user()->tokens()
     * so one account can never revoke another account's token by guessing an id.
     */
    public function logoutSession(Request $request, int $id)
    {
        $token = $request->user()->tokens()->where('id', $id)->first();

        if (! $token) {
            return response()->json(['message' => 'Session not found.'], 404);
        }

        $token->delete();

        return response()->json(['message' => 'Session logged out.']);
    }
}
