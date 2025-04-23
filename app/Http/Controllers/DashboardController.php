<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DashboardController extends Controller
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
     * Show the dashboard home page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Show the calendar page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function calendar()
    {
        return view('calendar');
    }

    /**
     * Show the history page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function history()
    {
        return view('history');
    }

    /**
     * Show the extension page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function extension()
    {
        return view('extension');
    }

    /**
     * Show the settings page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function settings()
    {
        return view('settings');
    }

    /**
     * Show the subscription page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function subscription()
    {
        return view('subscription');
    }
}
