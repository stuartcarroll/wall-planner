<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class PaintController extends Controller
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
                'product_name' => 'Brilliant White',
                'maker' => 'Dulux',
                'product_code' => 'DUL-1001',
                'form' => 'matt emulsion',
                'hex_color' => '#FEFEFE',
                'cmyk_c' => 0,
                'cmyk_m' => 0,
                'cmyk_y' => 0,
                'cmyk_k' => 1,
                'rgb_r' => 254,
                'rgb_g' => 254,
                'rgb_b' => 254,
                'price_gbp' => 32.99,
                'volume_ml' => 2500,
                'color_description' => 'The purest white for clean, contemporary spaces'
            ],
            4 => (object)[
                'id' => 4,
                'product_name' => 'Forest Moss',
                'maker' => 'Little Greene',
                'product_code' => 'LG-235',
                'form' => 'intelligent matt emulsion',
                'hex_color' => '#4A5D23',
                'cmyk_c' => 60,
                'cmyk_m' => 25,
                'cmyk_y' => 95,
                'cmyk_k' => 30,
                'rgb_r' => 74,
                'rgb_g' => 93,
                'rgb_b' => 35,
                'price_gbp' => 45.50,
                'volume_ml' => 1000,
                'color_description' => 'A natural green that brings the outdoors inside'
            ],
            5 => (object)[
                'id' => 5,
                'product_name' => 'Urban Grey',
                'maker' => 'Crown',
                'product_code' => 'CRN-8847',
                'form' => 'silk emulsion',
                'hex_color' => '#6B7280',
                'cmyk_c' => 45,
                'cmyk_m' => 35,
                'cmyk_y' => 25,
                'cmyk_k' => 10,
                'rgb_r' => 107,
                'rgb_g' => 114,
                'rgb_b' => 128,
                'price_gbp' => 28.75,
                'volume_ml' => 2500,
                'color_description' => 'A contemporary grey perfect for modern urban living'
            ],
            6 => (object)[
                'id' => 6,
                'product_name' => 'Sunset Orange',
                'maker' => 'Benjamin Moore',
                'product_code' => 'BM-2018-20',
                'form' => 'advance paint',
                'hex_color' => '#FF6B35',
                'cmyk_c' => 0,
                'cmyk_m' => 65,
                'cmyk_y' => 80,
                'cmyk_k' => 0,
                'rgb_r' => 255,
                'rgb_g' => 107,
                'rgb_b' => 53,
                'price_gbp' => 67.20,
                'volume_ml' => 1000,
                'color_description' => 'A vibrant orange that energizes and warms any room'
            ]
        ]));
    }

    private function savePaints($paints)
    {
        Session::put('paints', $paints->toArray());
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

        return view('paints.index', compact('paints'));
    }

    public function vueIndex(Request $request)
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

        return Inertia::render('Paints/Index', [
            'paints' => $paints->values(),
        ]);
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('paints.index')->with('error', 'Only administrators can create paints.');
        }
        
        return view('paints.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('paints.index')->with('error', 'Only administrators can create paints.');
        }
        
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_code' => 'required|string|max:255',
            'maker' => 'required|string|max:255',
            'cmyk_c' => 'required|integer|min:0|max:100',
            'cmyk_m' => 'required|integer|min:0|max:100',
            'cmyk_y' => 'required|integer|min:0|max:100',
            'cmyk_k' => 'required|integer|min:0|max:100',
            'rgb_r' => 'required|integer|min:0|max:255',
            'rgb_g' => 'required|integer|min:0|max:255',
            'rgb_b' => 'required|integer|min:0|max:255',
            'hex_color' => 'required|string|size:7|regex:/^#[A-Fa-f0-9]{6}$/',
            'form' => 'required|string|max:255',
            'volume_ml' => 'required|integer|min:1',
            'price_gbp' => 'required|numeric|min:0',
            'color_description' => 'nullable|string|max:500',
        ]);

        $paints = $this->getPaints();
        $nextId = $paints->keys()->max() + 1;
        
        $newPaint = (object)[
            'id' => $nextId,
            'product_name' => $validated['product_name'],
            'maker' => $validated['maker'],
            'product_code' => $validated['product_code'],
            'form' => $validated['form'],
            'hex_color' => $validated['hex_color'],
            'cmyk_c' => $validated['cmyk_c'],
            'cmyk_m' => $validated['cmyk_m'],
            'cmyk_y' => $validated['cmyk_y'],
            'cmyk_k' => $validated['cmyk_k'],
            'rgb_r' => $validated['rgb_r'],
            'rgb_g' => $validated['rgb_g'],
            'rgb_b' => $validated['rgb_b'],
            'price_gbp' => $validated['price_gbp'],
            'volume_ml' => $validated['volume_ml'],
            'color_description' => $validated['color_description'],
        ];

        $paints->put($nextId, $newPaint);
        $this->savePaints($paints);

        return redirect()->route('paints.index')->with('success', 
            'Paint "' . $validated['product_name'] . '" has been created successfully!');
    }

    public function show(string $id)
    {
        $paints = $this->getPaints();
        $paint = $paints->get($id);
        
        if (!$paint) {
            return redirect()->route('paints.index')->with('error', 'Paint not found.');
        }

        return view('paints.show', compact('paint'));
    }

    public function edit(string $id)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('paints.index')->with('error', 'Only administrators can edit paints.');
        }

        $paints = $this->getPaints();
        $paint = $paints->get($id);
        
        if (!$paint) {
            return redirect()->route('paints.index')->with('error', 'Paint not found.');
        }

        return view('paints.edit', compact('paint'));
    }

    public function update(Request $request, string $id)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('paints.index')->with('error', 'Only administrators can update paints.');
        }

        $paints = $this->getPaints();
        $paint = $paints->get($id);
        
        if (!$paint) {
            return redirect()->route('paints.index')->with('error', 'Paint not found.');
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_code' => 'required|string|max:255',
            'maker' => 'required|string|max:255',
            'cmyk_c' => 'required|integer|min:0|max:100',
            'cmyk_m' => 'required|integer|min:0|max:100',
            'cmyk_y' => 'required|integer|min:0|max:100',
            'cmyk_k' => 'required|integer|min:0|max:100',
            'rgb_r' => 'required|integer|min:0|max:255',
            'rgb_g' => 'required|integer|min:0|max:255',
            'rgb_b' => 'required|integer|min:0|max:255',
            'hex_color' => 'required|string|size:7|regex:/^#[A-Fa-f0-9]{6}$/',
            'form' => 'required|string|max:255',
            'volume_ml' => 'required|integer|min:1',
            'price_gbp' => 'required|numeric|min:0',
            'color_description' => 'nullable|string|max:500',
        ]);

        // Update the paint object
        foreach ($validated as $key => $value) {
            $paint->$key = $value;
        }

        $paints->put($id, $paint);
        $this->savePaints($paints);

        return redirect()->route('paints.show', $id)->with('success', 
            'Paint "' . $validated['product_name'] . '" has been updated successfully!');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('paints.index')->with('error', 'Only administrators can delete paints.');
        }

        $paints = $this->getPaints();
        $paint = $paints->get($id);
        
        if (!$paint) {
            return redirect()->route('paints.index')->with('error', 'Paint not found.');
        }

        $paintName = $paint->product_name;
        $paints->forget($id);
        $this->savePaints($paints);

        return redirect()->route('paints.index')->with('success', 
            'Paint "' . $paintName . '" has been deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('paints.index')->with('error', 'Only administrators can perform bulk operations.');
        }

        $request->validate([
            'paint_ids' => 'required|array',
            'paint_ids.*' => 'required|string'
        ]);

        $paints = $this->getPaints();
        $deletedCount = 0;

        foreach ($request->paint_ids as $paintId) {
            if ($paints->has($paintId)) {
                $paints->forget($paintId);
                $deletedCount++;
            }
        }

        $this->savePaints($paints);

        return redirect()->route('paints.index')->with('success', 
            "Successfully deleted {$deletedCount} paint(s).");
    }

    public function csvImport(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->path()));
            $headers = array_shift($csvData); // Remove header row

            $paints = $this->getPaints();
            $importedCount = 0;
            $nextId = $paints->keys()->max() + 1;

            foreach ($csvData as $row) {
                if (count($row) < count($headers)) continue; // Skip incomplete rows

                $paintData = array_combine($headers, $row);
                
                // Validate required fields
                if (empty($paintData['product_name']) || empty($paintData['maker']) || empty($paintData['hex_color'])) {
                    continue;
                }

                // Create new paint object
                $paint = (object)[
                    'id' => $nextId,
                    'product_name' => $paintData['product_name'] ?? '',
                    'product_code' => $paintData['product_code'] ?? 'IMPORT-' . $nextId,
                    'maker' => $paintData['maker'] ?? '',
                    'form' => $paintData['form'] ?? 'emulsion',
                    'hex_color' => $paintData['hex_color'] ?? '#000000',
                    'cmyk_c' => intval($paintData['cmyk_c'] ?? 0),
                    'cmyk_m' => intval($paintData['cmyk_m'] ?? 0),
                    'cmyk_y' => intval($paintData['cmyk_y'] ?? 0),
                    'cmyk_k' => intval($paintData['cmyk_k'] ?? 0),
                    'rgb_r' => intval($paintData['rgb_r'] ?? 0),
                    'rgb_g' => intval($paintData['rgb_g'] ?? 0),
                    'rgb_b' => intval($paintData['rgb_b'] ?? 0),
                    'price_gbp' => floatval($paintData['price_gbp'] ?? 0.00),
                    'volume_ml' => intval($paintData['volume_ml'] ?? 1000),
                    'color_description' => $paintData['color_description'] ?? ''
                ];

                $paints->put($nextId, $paint);
                $nextId++;
                $importedCount++;
            }

            $this->savePaints($paints);

            return response()->json([
                'success' => true, 
                'message' => "Successfully imported {$importedCount} paints."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getColorFamily($hexColor)
    {
        // Simple color family detection
        $hex = str_replace('#', '', $hexColor);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        
        // Handle grayscale colors
        if ($max - $min < 50) {
            if ($max < 100) return 'black';
            if ($min > 200) return 'white';
            return 'gray';
        }
        
        // Handle chromatic colors
        if ($r === $max) {
            if ($g > $b) return ($g > 150) ? 'yellow' : 'red';
            return ($r > 200 && $b > 150) ? 'pink' : 'red';
        }
        if ($g === $max) return ($b > $r) ? 'blue' : 'green';
        if ($b === $max) return ($r > $g) ? 'purple' : 'blue';
        
        return 'other';
    }
}