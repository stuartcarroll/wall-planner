<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paint extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_code',
        'maker',
        'cmyk_c',
        'cmyk_m',
        'cmyk_y',
        'cmyk_k',
        'rgb_r',
        'rgb_g',
        'rgb_b',
        'hex_color',
        'form',
        'volume_ml',
        'price_gbp',
        'color_description',
    ];

    protected $casts = [
        'price_gbp' => 'decimal:2',
        'volume_ml' => 'integer',
    ];

    public function getVolumeDisplayAttribute()
    {
        if ($this->volume_ml >= 1000) {
            return ($this->volume_ml / 1000) . 'L';
        }
        return $this->volume_ml . 'ml';
    }

    public function paintBundleItems()
    {
        return $this->hasMany(PaintBundleItem::class);
    }
}