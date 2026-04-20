<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'title', 'description', 'path', 'thumbnail_path', 'order', 'file_type', 'file_size', 'is_favorite'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function shareLinks()
    {
        return $this->hasMany(ShareLink::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }
}
