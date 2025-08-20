<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaintApiController extends Controller
{
    private function getPaints()
    {
        // Use session storage to persist between requests
        return collect(Session::get('paints', [
            1 => (object)[
                'id' => 1,
                'product_name' => 'Heritage Red',
                'maker' => 'Farrow & Ball',
                'product_code' => 'FB-294',
                'form' => 'estate emulsion',
                'hex_color' => '#B91927',
                'cmyk_c' => 20,
                'cmyk_m' => 95,
                'cmyk_y' => 85,
                'cmyk_k' => 10,
                'rgb_r' => 185,
                'rgb_g' => 25,
                'rgb_b' => 39,
                'price_gbp' => 89.00,
                'volume_ml' => 2500,
                'color_description' => 'A deep, sophisticated red inspired by traditional English heritage colors'
            ],
            2 => (object)[
                'id' => 2,
                'product_name' => 'Duck Egg Blue',
                'maker' => 'Farrow & Ball',
                'product_code' => 'FB-203',
                'form' => 'modern emulsion',
                'hex_color' => '#9EB8D0',
                'cmyk_c' => 35,
                'cmyk_m' => 15,
                'cmyk_y' => 0,
                'cmyk_k' => 15,
                'rgb_r' => 158,
                'rgb_g' => 184,
                'rgb_b' => 208,
                'price_gbp' => 89.00,
                'volume_ml' => 2500,
                'color_description' => 'A timeless blue-green that brings serenity to any space'
            ],
            3 => (object)[
                'id' => 3,
                'product_name' => 'Elephant\'s Breath',
                'maker' => 'Farrow & Ball',
                'product_code' => 'FB-229',
                'form' => 'estate emulsion',
                'hex_color' => '#9C8A7D',
                'cmyk_c' => 35,
                'cmyk_m' => 40,
                'cmyk_y' => 50,
                'cmyk_k' => 5,
                'rgb_r' => 156,
                'rgb_g' => 138,
                'rgb_b' => 125,
                'price_gbp' => 89.00,
                'volume_ml' => 2500,
                'color_description' => 'A sophisticated neutral that works beautifully in any setting'
            ]
        ]));
    }

    public function index(Request $request)
    {
        $paints = $this->getPaints();
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);
            $paints = $paints->filter(function ($paint) use ($searchTerm) {
                return str_contains(strtolower($paint->product_name), $searchTerm) ||
                       str_contains(strtolower($paint->maker), $searchTerm) ||
                       str_contains(strtolower($paint->product_code), $searchTerm) ||
                       str_contains(strtolower($paint->form), $searchTerm);
            });
        }

        // Apply manufacturer filter
        if ($request->filled('manufacturer')) {
            $paints = $paints->filter(function ($paint) use ($request) {
                return $paint->maker === $request->manufacturer;
            });
        }

        // Apply color filter
        if ($request->filled('color_filter')) {
            $paints = $paints->filter(function ($paint) use ($request) {
                return $this->getColorFamily($paint->hex_color) === $request->color_filter;
            });
        }

        return response()->json([
            'data' => $paints->values(),
            'manufacturers' => $this->getPaints()->pluck('maker')->unique()->sort()->values(),
        ]);
    }

    public function show($id)
    {
        $paints = $this->getPaints();
        $paint = $paints->get($id);
        
        if (!$paint) {
            return response()->json(['error' => 'Paint not found'], 404);
        }

        return response()->json(['data' => $paint]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_code' => 'required|string|max:255',
            'maker' => 'required|string|max:255',
            'form' => 'required|string|max:255',
            'hex_color' => 'required|string|size:7|regex:/^#[A-Fa-f0-9]{6}$/',
            'price_gbp' => 'required|numeric|min:0',
            'volume_ml' => 'required|integer|min:1',
            'color_description' => 'nullable|string',
        ]);

        $paints = $this->getPaints();
        $newId = $paints->keys()->max() + 1;
        
        $newPaint = (object) array_merge($validated, [
            'id' => $newId,
            'cmyk_c' => 0,
            'cmyk_m' => 0,
            'cmyk_y' => 0,
            'cmyk_k' => 0,
            'rgb_r' => 0,
            'rgb_g' => 0,
            'rgb_b' => 0,
        ]);

        $paints->put($newId, $newPaint);
        Session::put('paints', $paints->toArray());

        return response()->json([
            'data' => $newPaint,
            'message' => 'Paint created successfully'
        ], 201);
    }

    public function destroy($id)
    {
        $paints = $this->getPaints();
        
        if (!$paints->has($id)) {
            return response()->json(['error' => 'Paint not found'], 404);
        }

        $paint = $paints->get($id);
        $paints->forget($id);
        Session::put('paints', $paints->toArray());

        return response()->json([
            'message' => 'Paint deleted successfully'
        ]);
    }

    private function getColorFamily($hexColor)
    {
        $hex = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        
        if ($max - $min < 50) {
            if ($max < 100) return 'black';
            if ($min > 200) return 'white';
            return 'gray';
        }
        
        if ($r === $max) {
            if ($g > $b) return $g > 150 ? 'yellow' : 'red';
            return $r > 200 && $b > 150 ? 'pink' : 'red';
        }
        if ($g === $max) return $b > $r ? 'blue' : 'green';
        if ($b === $max) return $r > $g ? 'purple' : 'blue';
        
        return 'other';
    }
}