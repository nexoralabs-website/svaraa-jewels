<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'status', 'meta_title', 'meta_description'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->name . ' Jewellery | Svaraa Jewels';
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description
            ?: 'Shop our exclusive ' . $this->name . ' collection at Svaraa Jewels. Handcrafted luxury jewellery for every occasion.';
    }

    public function getImageUrlAttribute(): string
    {
        if (! empty($this->image)) {
            $path = preg_replace('#^storage/#', '', $this->image);
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            }
        }

        return asset('images/placeholders/category-coming-soon.svg');
    }
}
