<?php

namespace App\Http\Controllers;

use App\Models\PaintBundle;
use App\Models\PaintBundleItem;
use App\Models\Project;
use App\Models\Paint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaintBundleController extends Controller
{
    private function getPaints()
    {
        // Use session storage to get paints (same as PaintController)
        return collect(Session::get('paints', []));
    }

    private function getBundles()
    {
        // Use session storage for paint bundles
        return collect(Session::get('paint_bundles', []));
    }

    private function saveBundles($bundles)
    {
        Session::put('paint_bundles', $bundles->toArray());
    }

    public function index()
    {
        $bundles = $this->getBundles();
        $projects = collect(Session::get('projects', []));
        
        return view('paint-bundles.index', compact('bundles', 'projects'));
    }

    public function create(Request $request)
    {
        $projects = collect(Session::get('projects', []));
        $paints = $this->getPaints();
        
        // Pre-select project if passed via URL parameter
        $selectedProjectId = $request->get('project_id');
        
        return view('paint-bundles.create', compact('projects', 'paints', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|string',
            'paint_items' => 'required|array',
            'paint_items.*.paint_id' => 'required|string',
            'paint_items.*.quantity' => 'required|integer|min:1',
        ]);

        $bundles = $this->getBundles();
        $paints = $this->getPaints();
        
        $bundleId = (string) ($bundles->keys()->max() + 1);
        $totalCost = 0;
        $items = [];

        // Calculate total cost and create items
        foreach ($validated['paint_items'] as $item) {
            $paint = $paints->get($item['paint_id']);
            if ($paint) {
                $subtotal = $item['quantity'] * $paint->price_gbp;
                $totalCost += $subtotal;
                
                $items[] = [
                    'paint_id' => $item['paint_id'],
                    'paint_name' => $paint->product_name,
                    'paint_maker' => $paint->maker,
                    'paint_hex_color' => $paint->hex_color,
                    'quantity' => $item['quantity'],
                    'unit_price' => $paint->price_gbp,
                    'subtotal' => $subtotal,
                    'volume_ml' => $paint->volume_ml * $item['quantity'],
                ];
            }
        }

        $bundle = (object) [
            'id' => $bundleId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'project_id' => $validated['project_id'],
            'created_by' => auth()->id(),
            'created_by_name' => auth()->user()->name,
            'total_cost' => $totalCost,
            'items_count' => count($items),
            'items' => $items,
            'created_at' => now()->toISOString(),
        ];

        $bundles->put($bundleId, $bundle);
        $this->saveBundles($bundles);

        return redirect()->route('paint-bundles.index')->with('success', 
            'Paint bundle "' . $validated['name'] . '" created successfully!');
    }

    public function show(string $id)
    {
        $bundles = $this->getBundles();
        $bundle = $bundles->get($id);
        
        if (!$bundle) {
            return redirect()->route('paint-bundles.index')->with('error', 'Paint bundle not found.');
        }

        $projects = collect(Session::get('projects', []));
        $project = $projects->get($bundle->project_id);

        return view('paint-bundles.show', compact('bundle', 'project'));
    }

    public function edit(string $id)
    {
        $bundles = $this->getBundles();
        $bundle = $bundles->get($id);
        
        if (!$bundle) {
            return redirect()->route('paint-bundles.index')->with('error', 'Paint bundle not found.');
        }

        $projects = collect(Session::get('projects', []));
        $paints = $this->getPaints();
        
        return view('paint-bundles.edit', compact('bundle', 'projects', 'paints'));
    }

    public function update(Request $request, string $id)
    {
        $bundles = $this->getBundles();
        $bundle = $bundles->get($id);
        
        if (!$bundle) {
            return redirect()->route('paint-bundles.index')->with('error', 'Paint bundle not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|string',
            'paint_items' => 'required|array',
            'paint_items.*.paint_id' => 'required|string',
            'paint_items.*.quantity' => 'required|integer|min:1',
        ]);

        $paints = $this->getPaints();
        $totalCost = 0;
        $items = [];

        // Recalculate total cost and items
        foreach ($validated['paint_items'] as $item) {
            $paint = $paints->get($item['paint_id']);
            if ($paint) {
                $subtotal = $item['quantity'] * $paint->price_gbp;
                $totalCost += $subtotal;
                
                $items[] = [
                    'paint_id' => $item['paint_id'],
                    'paint_name' => $paint->product_name,
                    'paint_maker' => $paint->maker,
                    'paint_hex_color' => $paint->hex_color,
                    'quantity' => $item['quantity'],
                    'unit_price' => $paint->price_gbp,
                    'subtotal' => $subtotal,
                    'volume_ml' => $paint->volume_ml * $item['quantity'],
                ];
            }
        }

        // Update bundle
        $bundle->name = $validated['name'];
        $bundle->description = $validated['description'] ?? '';
        $bundle->project_id = $validated['project_id'];
        $bundle->total_cost = $totalCost;
        $bundle->items_count = count($items);
        $bundle->items = $items;
        $bundle->updated_at = now()->toISOString();

        $bundles->put($id, $bundle);
        $this->saveBundles($bundles);

        return redirect()->route('paint-bundles.show', $id)->with('success', 
            'Paint bundle updated successfully!');
    }

    public function destroy(string $id)
    {
        $bundles = $this->getBundles();
        $bundle = $bundles->get($id);
        
        if (!$bundle) {
            return redirect()->route('paint-bundles.index')->with('error', 'Paint bundle not found.');
        }

        $bundleName = $bundle->name;
        $bundles->forget($id);
        $this->saveBundles($bundles);

        return redirect()->route('paint-bundles.index')->with('success', 
            'Paint bundle "' . $bundleName . '" deleted successfully!');
    }

    public function addToPaintBundle(Request $request)
    {
        // AJAX endpoint for adding paints to bundle from paint catalog
        $request->validate([
            'paint_ids' => 'required|array',
            'paint_ids.*' => 'required|string',
            'bundle_id' => 'nullable|string',
            'bundle_name' => 'required_without:bundle_id|string|max:255',
            'project_id' => 'required|string',
        ]);

        $paints = $this->getPaints();
        $selectedPaints = [];

        foreach ($request->paint_ids as $paintId) {
            $paint = $paints->get($paintId);
            if ($paint) {
                $selectedPaints[] = [
                    'id' => $paintId,
                    'name' => $paint->product_name,
                    'maker' => $paint->maker,
                    'hex_color' => $paint->hex_color,
                    'price_gbp' => $paint->price_gbp,
                    'volume_ml' => $paint->volume_ml,
                ];
            }
        }

        // Store in session for bundle creation
        Session::put('selected_paints_for_bundle', [
            'paints' => $selectedPaints,
            'bundle_name' => $request->bundle_name ?? 'New Paint Bundle',
            'project_id' => $request->project_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paints added to bundle queue',
            'redirect_url' => route('paint-bundles.create')
        ]);
    }
}