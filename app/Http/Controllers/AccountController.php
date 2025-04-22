<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the settings page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $notificationSettings = NotificationSetting::where('user_id', $user->id)->first();
        
        return view('settings', compact('notificationSettings'));
    }

    /**
     * Update the user's account information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
        ]);
        
        $user = Auth::user();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();
        
        return redirect()->route('settings')->with('success', 'Account information updated successfully!');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        $user->password = bcrypt($validated['new_password']);
        $user->save();
        
        return redirect()->route('settings')->with('success', 'Password updated successfully!');
    }
}
