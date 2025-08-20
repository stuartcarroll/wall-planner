<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WebScrapingController extends Controller
{

    public function scrapeProductList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $response = Http::timeout(30)->withOptions(['verify' => false])->get($request->url);
            
            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch webpage',
                    'status' => $response->status()
                ], 400);
            }

            $html = $response->body();
            $productUrls = $this->extractProductUrls($html, $request->url);

            return response()->json([
                'message' => 'Product URLs extracted successfully',
                'urls' => $productUrls,
                'count' => count($productUrls)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Scraping failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scrapeProductDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $response = Http::timeout(30)->withOptions(['verify' => false])->get($request->url);
            
            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch product page',
                    'status' => $response->status()
                ], 400);
            }

            $html = $response->body();
            $productData = $this->extractProductData($html, $request->url);

            if (!$productData) {
                return response()->json([
                    'error' => 'No product data could be extracted from this page'
                ], 400);
            }

            return response()->json([
                'message' => 'Product data extracted successfully',
                'data' => $productData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Product scraping failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scrapeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'urls' => 'required|array',
            'urls.*' => 'required|url',
            'save_to_database' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $urls = $request->urls;
        $saveToDatabase = $request->save_to_database ?? false;
        $results = [];
        $saved = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($urls as $index => $url) {
                try {
                    $response = Http::timeout(30)->withOptions(['verify' => false])->get($url);
                    
                    if (!$response->successful()) {
                        $errors[] = [
                            'url' => $url,
                            'error' => "Failed to fetch (HTTP {$response->status()})"
                        ];
                        continue;
                    }

                    $html = $response->body();
                    $productData = $this->extractProductData($html, $url);

                    if (!$productData) {
                        $errors[] = [
                            'url' => $url,
                            'error' => 'No extractable product data found'
                        ];
                        continue;
                    }

                    $results[] = $productData;

                    if ($saveToDatabase && $productData) {
                        $this->savePaintData($productData, $url);
                        $saved++;
                    }

                    // Add delay to be respectful to the server
                    sleep(1);

                } catch (\Exception $e) {
                    $errors[] = [
                        'url' => $url,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Batch scraping completed',
                'total_urls' => count($urls),
                'successful_scrapes' => count($results),
                'saved_to_database' => $saved,
                'errors' => $errors,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Batch scraping failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function extractProductUrls($html, $baseUrl)
    {
        $urls = [];
        $dom = new \DOMDocument();
        
        // Suppress HTML parsing warnings
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        
        // Try different selectors for product links
        $selectors = [
            '//a[contains(@href, "/product/")]',
            '//a[contains(@class, "product")]',
            '//a[contains(@class, "woocommerce-LoopProduct-link")]'
        ];

        foreach ($selectors as $selector) {
            $links = $xpath->query($selector);
            
            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                if ($href) {
                    $fullUrl = $this->resolveUrl($href, $baseUrl);
                    if (strpos($fullUrl, '/product/') !== false) {
                        $urls[] = $fullUrl;
                    }
                }
            }
        }

        return array_values(array_unique($urls));
    }

    private function extractProductData($html, $url)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        
        $data = [
            'source_url' => $url,
            'scraped_at' => now()->toISOString()
        ];

        // Extract product name
        $nameSelectors = [
            '//h1[@class="product_title entry-title"]',
            '//h1[contains(@class, "product")]',
            '//title',
            '//h1'
        ];
        
        foreach ($nameSelectors as $selector) {
            $elements = $xpath->query($selector);
            if ($elements->length > 0) {
                $name = trim($elements->item(0)->textContent);
                if ($name && strlen($name) < 200) {
                    $data['product_name'] = $name;
                    break;
                }
            }
        }

        // Extract product code from URL or title
        if (preg_match('/\/([^\/]+)\/?$/', $url, $matches)) {
            $data['product_code'] = strtoupper(str_replace('-', '_', $matches[1]));
        }

        // Extract volume information
        if (isset($data['product_name'])) {
            if (preg_match('/(\d+)\s*ml/i', $data['product_name'], $matches)) {
                $data['volume_ml'] = (int) $matches[1];
            }
        }

        // Set default values for Loop Colors
        $data['maker'] = 'Loop Colors';
        $data['form'] = 'spray paint';
        $data['price_gbp'] = 0; // Price not available on website
        
        // Extract description
        $descSelectors = [
            '//div[contains(@class, "product_description")]',
            '//div[contains(@class, "woocommerce-product-details__short-description")]',
            '//meta[@name="description"]/@content'
        ];
        
        foreach ($descSelectors as $selector) {
            $elements = $xpath->query($selector);
            if ($elements->length > 0) {
                $desc = trim($elements->item(0)->textContent);
                if ($desc && strlen($desc) > 10) {
                    $data['color_description'] = substr($desc, 0, 500);
                    break;
                }
            }
        }

        // Try to extract color information
        $colorData = $this->extractColorData($html, $xpath);
        $data = array_merge($data, $colorData);

        // Validate we have minimum required data
        if (!isset($data['product_name']) || !$data['product_name']) {
            return null;
        }

        // Set defaults for missing required fields
        if (!isset($data['hex_color'])) {
            $data['hex_color'] = '#000000'; // Default to black
        }
        if (!isset($data['color_description'])) {
            $data['color_description'] = 'Professional spray paint';
        }
        if (!isset($data['volume_ml'])) {
            $data['volume_ml'] = 400; // Default volume
        }

        return $data;
    }

    private function extractColorData($html, $xpath)
    {
        $colorData = [];

        // Look for hex color codes in various formats
        if (preg_match('/#([A-Fa-f0-9]{6})/i', $html, $matches)) {
            $colorData['hex_color'] = '#' . strtoupper($matches[1]);
        }

        // Look for RGB values
        if (preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/i', $html, $matches)) {
            $colorData['rgb_r'] = (int) $matches[1];
            $colorData['rgb_g'] = (int) $matches[2];
            $colorData['rgb_b'] = (int) $matches[3];
            
            // Convert RGB to hex if not already set
            if (!isset($colorData['hex_color'])) {
                $colorData['hex_color'] = sprintf('#%02X%02X%02X', 
                    $colorData['rgb_r'], 
                    $colorData['rgb_g'], 
                    $colorData['rgb_b']
                );
            }
        }

        // Look for CMYK values
        if (preg_match('/cmyk\((\d+),\s*(\d+),\s*(\d+),\s*(\d+)\)/i', $html, $matches)) {
            $colorData['cmyk_c'] = (int) $matches[1];
            $colorData['cmyk_m'] = (int) $matches[2];
            $colorData['cmyk_y'] = (int) $matches[3];
            $colorData['cmyk_k'] = (int) $matches[4];
        }

        return $colorData;
    }

    private function savePaintData($data, $sourceUrl)
    {
        // Check for duplicates
        $existing = Paint::where('product_code', $data['product_code'])
                         ->where('maker', $data['maker'])
                         ->first();
        
        if ($existing) {
            return; // Skip duplicates
        }

        // Convert hex to RGB if RGB not provided
        if (isset($data['hex_color']) && !isset($data['rgb_r'])) {
            $rgb = $this->hexToRgb($data['hex_color']);
            $data['rgb_r'] = $rgb['r'];
            $data['rgb_g'] = $rgb['g'];
            $data['rgb_b'] = $rgb['b'];
        }

        Paint::create([
            'product_name' => $data['product_name'],
            'product_code' => $data['product_code'],
            'maker' => $data['maker'],
            'form' => $data['form'],
            'hex_color' => $data['hex_color'],
            'price_gbp' => $data['price_gbp'],
            'volume_ml' => $data['volume_ml'],
            'color_description' => $data['color_description'],
            'cmyk_c' => $data['cmyk_c'] ?? null,
            'cmyk_m' => $data['cmyk_m'] ?? null,
            'cmyk_y' => $data['cmyk_y'] ?? null,
            'cmyk_k' => $data['cmyk_k'] ?? null,
            'rgb_r' => $data['rgb_r'] ?? null,
            'rgb_g' => $data['rgb_g'] ?? null,
            'rgb_b' => $data['rgb_b'] ?? null,
        ]);
    }

    private function resolveUrl($href, $baseUrl)
    {
        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return $href;
        }

        $parsedBase = parse_url($baseUrl);
        $baseScheme = $parsedBase['scheme'] ?? 'https';
        $baseHost = $parsedBase['host'] ?? '';
        
        if (strpos($href, '//') === 0) {
            return $baseScheme . ':' . $href;
        }
        
        if (strpos($href, '/') === 0) {
            return $baseScheme . '://' . $baseHost . $href;
        }
        
        return $baseScheme . '://' . $baseHost . '/' . ltrim($href, '/');
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
}