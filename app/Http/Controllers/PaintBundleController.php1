<?php
namespace app\Http\Controllers;

use app\Models\Paint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Paint::query();
        
        if ($request->filled('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%')
                  ->orWhere('maker', 'like', '%' . $request->search . '%');
        }
        
        $paints = $query->paginate(20);
        
        return view('paints.index', compact('paints'));
    }

    public function create()
    {
        Gate::authorize('manage-paints');
        return view('paints.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-paints');
        
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
            'color_description' => 'nullable|string',
        ]);

        Paint::create($validated);

        return redirect()->route('paints.index')->with('success', 'Paint created successfully.');
    }

    public function show(Paint $paint)
    {
        return view('paints.show', compact('paint'));
    }

    public function edit(Paint $paint)
    {
        Gate::authorize('manage-paints');
        return view('paints.edit', compact('paint'));
    }

    public function update(Request $request, Paint $paint)
    {
        Gate::authorize('manage-paints');
        
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
            'color_description' => 'nullable|string',
        ]);

        $paint->update($validated);

        return redirect()->route('paints.index')->with('success', 'Paint updated successfully.');
    }

    public function destroy(Paint $paint)
    {
        Gate::authorize('manage-paints');
        
        $paint->delete();
        
        return redirect()->route('paints.index')->with('success', 'Paint deleted successfully.');
    }
}