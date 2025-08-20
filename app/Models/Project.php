<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location',
        'manager_email',
        'wall_height_cm',
        'wall_width_cm',
        'location_url',
        'permalink',
        'status',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function paintBundles()
    {
        return $this->hasMany(PaintBundle::class);
    }

    public function images()
    {
        return $this->hasMany(ProjectImage::class);
    }

    public function photos()
    {
        return $this->hasMany(ProjectImage::class)->where('type', 'photo');
    }

    public function sketches()
    {
        return $this->hasMany(ProjectImage::class)->where('type', 'sketch');
    }

    public function inspirations()
    {
        return $this->hasMany(ProjectImage::class)->where('type', 'inspiration');
    }

    public function members()
    {
        return $this->hasMany(ProjectMember::class);
    }
}
