<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Log;

class UserProfileController extends Controller
{
    /**
     * Get the onboarding profile data for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOnboardingProfile()
    {
        $user = Auth::user();
        $profile = $user->profile()->first(); // Use the relationship defined in User model

        if (!$profile) {
            // Optionally create a blank profile if none exists, 
            // or just return null/empty data
            Log::info("No onboarding profile found for user", ["user_id" => $user->id]);
            return response()->json(["profile" => null]);
        }

        Log::info("Fetched onboarding profile for user", ["user_id" => $user->id]);
        return response()->json(["profile" => $profile]);
    }

    /**
     * Save or update the onboarding profile data for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveOnboardingProfile(Request $request)
    {
        $user = Auth::user();
        Log::info("Saving onboarding profile for user", ["user_id" => $user->id]);

        $validated = $request->validate([
            "mt_everest" => ["required", "string", "max:65535"], // Use max text length
            "money_making_activities" => ["required", "string", "max:65535"],
            "energy_renewal_activities" => ["required", "string", "max:65535"],
        ]);

        try {
            // Use updateOrCreate to handle both creation and update
            $profile = UserProfile::updateOrCreate(
                ["user_id" => $user->id],
                $validated
            );

            // Mark onboarding as complete for the user
            if (!$user->has_completed_onboarding) {
                $user->has_completed_onboarding = true;
                $user->save();
                Log::info("Marked onboarding as complete for user", ["user_id" => $user->id]);
            }

            Log::info("Successfully saved onboarding profile for user", ["user_id" => $user->id]);
            // Return the updated profile and a success message
            return response()->json([
                "message" => "Profile saved successfully.",
                "profile" => $profile,
                "user" => $user->fresh() // Return updated user data with the flag
            ], 200);

        } catch (\Exception $e) {
            Log::error("Failed to save onboarding profile", [
                "user_id" => $user->id,
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString() // More detailed log
            ]);
            return response()->json(["message" => "Failed to save profile. Please try again."], 500);
        }
    }
}

