<?php

namespace App\Http\Controllers\Portail;

use App\Http\Controllers\Controller;

class PortailDashboardController extends Controller
{
    public function index()
    {
        $client = auth('clients')->user();

        $contrats = $client->contrats()->with(['lot.lotissement'])->get();

        return view('portail.dashboard', compact('contrats'));
    }

    public function profile()
    {
        $client = auth('clients')->user();

        return view('portail.profile', compact('client'));
    }
}
