<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

abstract class Controller
{


public function checkProximity(Request $request)
{
    $response = Http::post('https://your-flask-api-url/check_proximity', [
        'warehouse' => [14.5995, 120.9842],
        'delivery' => [$request->lat, $request->lng],
        'radius' => $request->radius ?? 250
    ]);

    return view('dashboard.alerts', ['data' => $response->json()]);
}

}

