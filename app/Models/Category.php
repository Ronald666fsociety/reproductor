<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'password', 'order'];

    public function videos()
    {
        return $this->hasMany(Video::class)->orderBy('order');
    }
}
