<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profile = UserProfile::where('user_id', Auth::id())->first();
        
        return response()->json([
            'profile' => $profile,
            'has_profile' => $profile !== null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mt_everest' => 'required|string|max:1000',
            'money_making_activities' => 'required|string|max:1000',
            'energy_renewal_activities' => 'required|string|max:1000',
            'calendar_preferences' => 'nullable|array',
        ]);

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
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Only allow viewing own profile
        if ($id != Auth::id() && $id != 'me') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $profile = UserProfile::where('user_id', Auth::id())->firstOrFail();
        
        return response()->json([
            'profile' => $profile
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Only allow updating own profile
        if ($id != Auth::id() && $id != 'me') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $validated = $request->validate([
            'mt_everest' => 'sometimes|required|string|max:1000',
            'money_making_activities' => 'sometimes|required|string|max:1000',
            'energy_renewal_activities' => 'sometimes|required|string|max:1000',
            'calendar_preferences' => 'nullable|array',
        ]);
        
        $profile = UserProfile::where('user_id', Auth::id())->firstOrFail();
        $profile->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }
}
