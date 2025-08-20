<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaintBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'project_id',
        'user_id',
        'total_cost',
    ];

    protected $casts = [
        'total_cost' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PaintBundleItem::class);
    }

    public function paints()
    {
        return $this->belongsToMany(Paint::class, 'paint_bundle_items')
                   ->withPivot('quantity', 'unit_price', 'subtotal')
                   ->withTimestamps();
    }

    public function calculateTotalCost()
    {
        return $this->items()->sum('subtotal');
    }

    public function getTotalVolumeAttribute()
    {
        return $this->items()->sum('volume_ml');
    }
}
