<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaintBundle;
use App\Models\Paint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PaintBundleApiController extends Controller
{
    private function getPaints()
    {
        return collect(Session::get('paints', [
            1 => (object)[
                'id' => 1,
                'product_name' => 'Heritage Red',
                'maker' => 'Farrow & Ball',
                'product_code' => 'FB-294',
                'form' => 'estate emulsion',
                'hex_color' => '#B91927',
                'price_gbp' => 89.00,
                'volume_ml' => 2500,
            ],
            2 => (object)[
                'id' => 2,
                'product_name' => 'Duck Egg Blue',
                'maker' => 'Farrow & Ball',
                'product_code' => 'FB-203',
                'form' => 'modern emulsion',
                'hex_color' => '#9EB8D0',
                'price_gbp' => 89.00,
                'volume_ml' => 2500,
            ],
            3 => (object)[
                'id' => 3,
                'product_name' => 'Elephant\'s Breath',
                'maker' => 'Farrow & Ball',
                'product_code' => 'FB-229',
                'form' => 'estate emulsion',
                'hex_color' => '#9C8A7D',
                'price_gbp' => 89.00,
                'volume_ml' => 2500,
            ]
        ]));
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->isAdmin()) {
            // Admins can see all bundles
            $bundles = PaintBundle::with(['user', 'items'])->orderBy('created_at', 'desc')->get();
        } else {
            // Regular users can only see their own bundles and bundles from projects they're involved in
            $bundles = PaintBundle::with(['user', 'items'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'data' => $bundles->map(function ($bundle) {
                return [
                    'id' => $bundle->id,
                    'name' => $bundle->name,
                    'description' => $bundle->description,
                    'total_cost' => $bundle->total_cost,
                    'item_count' => $bundle->items->count(),
                    'user' => [
                        'id' => $bundle->user->id,
                        'name' => $bundle->user->name,
                        'email' => $bundle->user->email,
                    ],
                    'created_at' => $bundle->created_at,
                    'updated_at' => $bundle->updated_at,
                ];
            }),
            'can_create' => true,
            'is_admin' => $user->isAdmin(),
        ]);
    }

    public function show(Request $request, PaintBundle $paintBundle)
    {
        $user = $request->user();
        
        // Check if user can view this bundle
        if (!$user->isAdmin() && $paintBundle->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $paintBundle->load(['user', 'items']);
        $paints = $this->getPaints();

        return response()->json([
            'data' => [
                'id' => $paintBundle->id,
                'name' => $paintBundle->name,
                'description' => $paintBundle->description,
                'total_cost' => $paintBundle->total_cost,
                'user' => [
                    'id' => $paintBundle->user->id,
                    'name' => $paintBundle->user->name,
                    'email' => $paintBundle->user->email,
                ],
                'items' => $paintBundle->items->map(function ($item) use ($paints) {
                    $paint = $paints->get($item->paint_id);
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price_per_unit' => $item->price_per_unit,
                        'total_price' => $item->quantity * $item->price_per_unit,
                        'paint' => $paint ? [
                            'id' => $paint->id,
                            'product_name' => $paint->product_name,
                            'maker' => $paint->maker,
                            'product_code' => $paint->product_code,
                            'hex_color' => $paint->hex_color,
                            'price_gbp' => $paint->price_gbp,
                            'volume_ml' => $paint->volume_ml,
                        ] : null,
                    ];
                }),
                'created_at' => $paintBundle->created_at,
                'updated_at' => $paintBundle->updated_at,
            ],
            'can_edit' => $user->isAdmin() || $paintBundle->user_id === $user->id,
            'can_delete' => $user->isAdmin() || $paintBundle->user_id === $user->id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.paint_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_per_unit' => 'required|numeric|min:0',
        ]);

        // Validate paint IDs against session data
        $paints = $this->getPaints();
        foreach ($request->items as $item) {
            if (!$paints->has($item['paint_id'])) {
                return response()->json([
                    'error' => 'Invalid paint ID: ' . $item['paint_id']
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Calculate total cost
            $totalCost = 0;
            foreach ($request->items as $item) {
                $totalCost += $item['quantity'] * $item['price_per_unit'];
            }

            // Create paint bundle
            $paintBundle = PaintBundle::create([
                'name' => $request->name,
                'description' => $request->description,
                'total_cost' => $totalCost,
                'user_id' => $request->user()->id,
            ]);

            // Create paint bundle items
            foreach ($request->items as $itemData) {
                $paintBundle->items()->create([
                    'paint_id' => $itemData['paint_id'],
                    'quantity' => $itemData['quantity'],
                    'price_per_unit' => $itemData['price_per_unit'],
                ]);
            }

            DB::commit();

            $paintBundle->load(['user', 'items.paint']);

            return response()->json([
                'data' => $paintBundle,
                'message' => 'Paint bundle created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to create paint bundle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, PaintBundle $paintBundle)
    {
        $user = $request->user();
        
        // Check if user can edit this bundle
        if (!$user->isAdmin() && $paintBundle->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $paintBundle->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $paintBundle->load(['user', 'items.paint']);

        return response()->json([
            'data' => $paintBundle,
            'message' => 'Paint bundle updated successfully'
        ]);
    }

    public function destroy(Request $request, PaintBundle $paintBundle)
    {
        $user = $request->user();
        
        // Check if user can delete this bundle
        if (!$user->isAdmin() && $paintBundle->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $paintBundle->delete();

        return response()->json([
            'message' => 'Paint bundle deleted successfully'
        ]);
    }
}