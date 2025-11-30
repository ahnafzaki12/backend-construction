<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'author',
        'excerpt',
        'category',
        'image',
        'featured'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = self::generateCuid();
            }
        });
    }

    private static function generateCuid()
    {
        return 'c' . substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(12))), 0, 20);
    }

    
}
