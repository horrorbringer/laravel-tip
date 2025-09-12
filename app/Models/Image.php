<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
     use HasFactory;

    protected $fillable = [
        'public_id',
        'url',
        'width',
        'height',
        'format',
        'size',
        'folder',
        'user_id'
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'size' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
