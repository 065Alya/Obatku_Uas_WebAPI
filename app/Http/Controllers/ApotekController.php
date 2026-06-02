<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ApotekController
 *
 * Handles the pharmacy finder page (PRD F-07, Page #14).
 * Uses Google Maps Places API (client-side JS) — API key is served via config
 * so it is never exposed as a plain string in blade markup.
 */
class ApotekController extends Controller
{
    /**
     * GET /apotek
     * Show the pharmacy finder page with Google Maps integration.
     */
    public function index(Request $request): View
    {
        $mapsApiKey = config('services.google_maps.key', '');

        return view('apotek.index', compact('mapsApiKey'));
    }
}
