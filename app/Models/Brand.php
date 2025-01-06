<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    Use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug', 'image', 'active'];

    public function products(){
        return $this->hasMany(Product::class);
    }
}
