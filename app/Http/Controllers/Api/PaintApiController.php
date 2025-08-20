<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaintApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Paint::query();
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('product_name', 'like', "%{$searchTerm}%")
                  ->orWhere('maker', 'like', "%{$searchTerm}%")
                  ->orWhere('product_code', 'like', "%{$searchTerm}%")
                  ->orWhere('form', 'like', "%{$searchTerm}%");
            });
        }

        // Apply manufacturer filter
        if ($request->filled('manufacturer')) {
            $query->where('maker', $request->manufacturer);
        }

        // Apply color filter
        if ($request->filled('color_filter')) {
            $query->where('hex_color', 'like', $this->getColorFilterPattern($request->color_filter));
        }

        $paints = $query->orderBy('maker')->orderBy('product_name')->get();
        $manufacturers = Paint::select('maker')->distinct()->orderBy('maker')->pluck('maker');

        return response()->json([
            'data' => $paints,
            'manufacturers' => $manufacturers,
        ]);
    }

    public function show(Paint $paint)
    {
        return response()->json(['data' => $paint]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_code' => 'required|string|max:255',
            'maker' => 'required|string|max:255',
            'form' => 'required|string|max:255',
            'hex_color' => 'required|string|size:7|regex:/^#[A-Fa-f0-9]{6}$/',
            'price_gbp' => 'required|numeric|min:0',
            'volume_ml' => 'required|integer|min:1',
            'color_description' => 'nullable|string',
            'cmyk_c' => 'nullable|integer|min:0|max:100',
            'cmyk_m' => 'nullable|integer|min:0|max:100',
            'cmyk_y' => 'nullable|integer|min:0|max:100',
            'cmyk_k' => 'nullable|integer|min:0|max:100',
            'rgb_r' => 'nullable|integer|min:0|max:255',
            'rgb_g' => 'nullable|integer|min:0|max:255',
            'rgb_b' => 'nullable|integer|min:0|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate RGB from hex if not provided
        $data = $validator->validated();
        if (!isset($data['rgb_r'])) {
            $rgb = $this->hexToRgb($data['hex_color']);
            $data['rgb_r'] = $rgb['r'];
            $data['rgb_g'] = $rgb['g'];
            $data['rgb_b'] = $rgb['b'];
        }

        $paint = Paint::create($data);

        return response()->json([
            'data' => $paint,
            'message' => 'Paint created successfully'
        ], 201);
    }

    public function update(Request $request, Paint $paint)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_code' => 'required|string|max:255',
            'maker' => 'required|string|max:255',
            'form' => 'required|string|max:255',
            'hex_color' => 'required|string|size:7|regex:/^#[A-Fa-f0-9]{6}$/',
            'price_gbp' => 'required|numeric|min:0',
            'volume_ml' => 'required|integer|min:1',
            'color_description' => 'nullable|string',
            'cmyk_c' => 'nullable|integer|min:0|max:100',
            'cmyk_m' => 'nullable|integer|min:0|max:100',
            'cmyk_y' => 'nullable|integer|min:0|max:100',
            'cmyk_k' => 'nullable|integer|min:0|max:100',
            'rgb_r' => 'nullable|integer|min:0|max:255',
            'rgb_g' => 'nullable|integer|min:0|max:255',
            'rgb_b' => 'nullable|integer|min:0|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        
        // Calculate RGB from hex if not provided
        if (!isset($data['rgb_r'])) {
            $rgb = $this->hexToRgb($data['hex_color']);
            $data['rgb_r'] = $rgb['r'];
            $data['rgb_g'] = $rgb['g'];
            $data['rgb_b'] = $rgb['b'];
        }

        $paint->update($data);

        return response()->json([
            'data' => $paint,
            'message' => 'Paint updated successfully'
        ]);
    }

    public function destroy(Paint $paint)
    {
        $paint->delete();

        return response()->json([
            'message' => 'Paint deleted successfully'
        ]);
    }

    public function importCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $csvData = array_map('str_getcsv', file($path));
        
        if (empty($csvData)) {
            return response()->json(['error' => 'CSV file is empty'], 422);
        }

        // Get the header row
        $header = array_shift($csvData);
        $header = array_map('trim', $header);
        
        // Expected columns
        $expectedColumns = [
            'product_name', 'product_code', 'maker', 'form', 'hex_color', 
            'price_gbp', 'volume_ml', 'color_description'
        ];
        $optionalColumns = [
            'cmyk_c', 'cmyk_m', 'cmyk_y', 'cmyk_k', 'rgb_r', 'rgb_g', 'rgb_b'
        ];
        
        // Check for required columns
        $missingColumns = array_diff($expectedColumns, $header);
        if (!empty($missingColumns)) {
            return response()->json([
                'error' => 'Missing required columns: ' . implode(', ', $missingColumns),
                'expected_columns' => $expectedColumns,
                'optional_columns' => $optionalColumns,
                'found_columns' => $header
            ], 422);
        }

        $imported = 0;
        $errors = [];
        $duplicates = [];

        DB::beginTransaction();
        
        try {
            foreach ($csvData as $rowIndex => $row) {
                $rowData = array_combine($header, $row);
                $rowNumber = $rowIndex + 2; // +2 because we removed header and rows are 1-indexed
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Validate required fields
                $validator = Validator::make($rowData, [
                    'product_name' => 'required|string|max:255',
                    'product_code' => 'required|string|max:255',
                    'maker' => 'required|string|max:255',
                    'form' => 'required|string|max:255',
                    'hex_color' => 'required|string|size:7|regex:/^#[A-Fa-f0-9]{6}$/',
                    'price_gbp' => 'required|numeric|min:0',
                    'volume_ml' => 'required|integer|min:1',
                    'color_description' => 'nullable|string',
                    'cmyk_c' => 'nullable|integer|min:0|max:100',
                    'cmyk_m' => 'nullable|integer|min:0|max:100',
                    'cmyk_y' => 'nullable|integer|min:0|max:100',
                    'cmyk_k' => 'nullable|integer|min:0|max:100',
                    'rgb_r' => 'nullable|integer|min:0|max:255',
                    'rgb_g' => 'nullable|integer|min:0|max:255',
                    'rgb_b' => 'nullable|integer|min:0|max:255',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validator->errors()->all()
                    ];
                    continue;
                }

                $data = $validator->validated();

                // Check for duplicates by product_code and maker
                $existing = Paint::where('product_code', $data['product_code'])
                                ->where('maker', $data['maker'])
                                ->first();
                
                if ($existing) {
                    $duplicates[] = [
                        'row' => $rowNumber,
                        'product_code' => $data['product_code'],
                        'maker' => $data['maker'],
                        'message' => 'Paint with this code and maker already exists'
                    ];
                    continue;
                }

                // Calculate RGB from hex if not provided
                if (!isset($data['rgb_r']) || empty($data['rgb_r'])) {
                    $rgb = $this->hexToRgb($data['hex_color']);
                    $data['rgb_r'] = $rgb['r'];
                    $data['rgb_g'] = $rgb['g'];
                    $data['rgb_b'] = $rgb['b'];
                }

                // Clean up empty optional fields
                foreach ($optionalColumns as $col) {
                    if (isset($data[$col]) && $data[$col] === '') {
                        $data[$col] = null;
                    }
                }

                Paint::create($data);
                $imported++;
            }

            DB::commit();

            return response()->json([
                'message' => "CSV import completed successfully",
                'imported' => $imported,
                'total_rows' => count($csvData),
                'errors' => $errors,
                'duplicates' => $duplicates,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    private function getColorFilterPattern($colorFamily)
    {
        // This is a simplified pattern matching for color families
        // In a real implementation, you might want more sophisticated color matching
        $patterns = [
            'red' => '%',
            'blue' => '%',
            'green' => '%',
            'yellow' => '%',
            'orange' => '%',
            'purple' => '%',
            'pink' => '%',
            'brown' => '%',
            'gray' => '%',
            'black' => '%',
            'white' => '%',
        ];

        return $patterns[$colorFamily] ?? '%';
    }
}