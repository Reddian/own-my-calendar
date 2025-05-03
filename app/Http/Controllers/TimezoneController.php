<?php

namespace App\Http\Controllers;

use DateTimeZone;
use Illuminate\Http\JsonResponse;

class TimezoneController extends Controller
{
    /**
     * Get a list of valid timezones.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        return response()->json(["timezones" => $timezones]);
    }
}

