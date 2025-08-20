<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectImage extends Model
{
    protected $fillable = [
        'project_id',
        'filename',
        'original_name',
        'type',
        'description',
        'mime_type',
        'file_size',
        'width',
        'height',
    ];

    protected $appends = ['url'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function getUrlAttribute()
    {
        return asset('storage/project_images/' . $this->filename);
    }
}
