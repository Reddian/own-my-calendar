<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    /**
     * Display the onboarding form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check if user already has a profile
        $profile = UserProfile::where('user_id', Auth::id())->first();
        
        return response()->json([
            'has_completed_onboarding' => $profile !== null,
            'profile' => $profile
        ]);
    }

    /**
     * Store the onboarding information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mt_everest' => 'required|string|max:1000',
            'money_making_activities' => 'required|string|max:1000',
            'energy_renewal_activities' => 'required|string|max:1000',
            'calendar_preferences' => 'nullable|array',
        ]);

        // Create or update user profile
        $profile = UserProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'mt_everest' => $validated['mt_everest'],
                'money_making_activities' => $validated['money_making_activities'],
                'energy_renewal_activities' => $validated['energy_renewal_activities'],
                'calendar_preferences' => $request->calendar_preferences ?? [],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed successfully',
            'profile' => $profile
        ]);
    }
}
