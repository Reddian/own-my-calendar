<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    // We won't use the full RegistersUsers trait for the API, 
    // but we can borrow validation/creation logic.
    // use RegistersUsers; 

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = '/home'; // Not used for API

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Handle a registration request for the application (API version).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function apiRegister(Request $request)
    {
        Log::info('API Registration attempt', ['email' => $request->email]);
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            Log::warning('API Registration validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            event(new Registered($user = $this->create($request->all())));
            Log::info('API Registration successful', ['user_id' => $user->id]);

            // Optional: Log the user in immediately after registration
            // Auth::guard('web')->login($user);
            // $request->session()->regenerate();
            // Log::info('User automatically logged in after registration', ['user_id' => $user->id]);
            // return response()->json($user, 201);

            // Return a success response, user needs to verify email / login separately
            return response()->json(['message' => 'Registration successful. Please check your email to verify your account.'], 201);

        } catch (\Exception $e) {
            Log::error('API Registration failed during user creation or event dispatch', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Registration failed. Please try again later.'], 500);
        }
    }
}

