<?php

namespace App\Models\Promotions;

use App\Models\Promotions\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Intervention\Image\Image;

class Promotion extends Model
{
    use Mutators;

    public const IMAGE_WIDTH = 640;
    public const IMAGE_HEIGHT = 250;

    protected $fillable = [
        'name', 'image', 'priority'
    ];

    protected $hidden = [
      'created_at', 'updated_at'
    ];

    public static function genFileName(Image $image, string $name): string
    {
        return Str::slug($name) . static::getImageExtension($image);
    }

    public static function getImageExtension(Image $image): string
    {
        return str_replace('image/', '.', $image->mime());
    }
}
