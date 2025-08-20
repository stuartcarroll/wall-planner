<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaintBundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'paint_bundle_id',
        'paint_id',
        'quantity',
        'price_per_unit',
        'subtotal',
        'volume_ml',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_per_unit' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'volume_ml' => 'integer',
    ];

    public function paintBundle()
    {
        return $this->belongsTo(PaintBundle::class);
    }

    public function paint()
    {
        return $this->belongsTo(Paint::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically calculate subtotal when creating/updating
        static::saving(function ($item) {
            $item->subtotal = $item->quantity * $item->price_per_unit;
        });
    }
}
