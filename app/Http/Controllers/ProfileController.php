<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log; // Add Log for debugging

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        Log::info("Profile update attempt", ["user_id" => $user->id]); // DEBUG

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        try {
            $user->fill($validated);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null; // Require re-verification if email changes
                // Optionally: Send email verification notification here
                // $user->sendEmailVerificationNotification();
                Log::info("Profile update: Email changed, requires re-verification", ["user_id" => $user->id]); // DEBUG
            }

            $user->save();
            Log::info("Profile update successful", ["user_id" => $user->id]); // DEBUG

            // Return the updated user data along with a success message
            return response()->json([
                'message' => 'Profile updated successfully.',
                'user' => $user->fresh() // Return fresh user data
            ]);
        } catch (\Exception $e) {
            Log::error("Profile update failed", ["user_id" => $user->id, "error" => $e->getMessage()]); // DEBUG
            return response()->json(['message' => 'Failed to update profile. Please try again.'], 500);
        }
    }

    /**
     * Update the authenticated user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        Log::info("Password update attempt", ["user_id" => $user->id]); // DEBUG

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password:web'], // Check against the 'web' guard
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        try {
            $user->forceFill([
                'password' => Hash::make($validated['password']),
            ])->save();

            Log::info("Password update successful", ["user_id" => $user->id]); // DEBUG
            return response()->json(['message' => 'Password updated successfully.'], 200);

        } catch (\Exception $e) {
            Log::error("Password update failed", ["user_id" => $user->id, "error" => $e->getMessage()]); // DEBUG
            return response()->json(['message' => 'Failed to update password. Please try again.'], 500);
        }
    }
}

