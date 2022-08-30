<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'title',
        'image',
        'address',
        'phone',
        'facebook',
        'instagram',
        'instagram',
        'youtube',
        'phone',
        'siteweb',
        ];
        
    use HasFactory;
}
