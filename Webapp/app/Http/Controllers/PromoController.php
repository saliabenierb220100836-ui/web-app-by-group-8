<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Services\PricingService;

class PromoController extends Controller
{
    public function index(PricingService $pricing)
    {
        $promos = Promo::active()->ordered()->get()->map(fn ($p) => [
            'promo' => $p,
            'live' => $pricing->isLiveNow($p),
        ]);

        return view('promos.index', compact('promos'));
    }
}
