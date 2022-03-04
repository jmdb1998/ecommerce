<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    const BORRADOR = 1;
    const PUBLICADO = 2;

    protected $fillable = ['name', 'slug', 'description', 'price', 'subcategory_id', 'brand_id', 'quantity'];
    //protected $guarded = ['id', 'created_at', 'updated_at'];

    public function sizes()
    {
        return $this->hasMany(Size::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class)->withPivot('quantity','id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    public function scopeCategoryFilter($query, $categorySearch)
    {
        return $query->whereHas('subcategory', function (Builder $query) use ($categorySearch) {
            $query->whereHas('category', function (Builder $query) use ($categorySearch) {
                $query->where('name', 'LIKE', "%{$categorySearch}%");
            });
        });
    }

    public static function scopeSubcategoryFilter($query, $subcategorySearch)
    {
        return $query->whereHas('subcategory', function (Builder $query) use ($subcategorySearch) {
            $query->where('name', 'LIKE', "%{$subcategorySearch}%");
        });
    }

    public static function scopeBrandFilter($query, $brandSearch)
    {
        return $query->whereHas('brand', function (Builder $query) use ($brandSearch) {
            $query->where('name', 'LIKE', "%{$brandSearch}%");
        });
    }

    public static function scopeStatusFilter($query, $status)
    {
        return $query->where('status', $status);
    }

    public static function scopeColorsFilter($query, $colorId)
    {
        return $query->whereHas('colors', function ($query) use ($colorId) {
            $query->where('colors.id', $colorId);
        })->orWhereHas('sizes', function ($query) use ($colorId) {
            $query->where(function ($query) use ($colorId) {
                $query->whereHas('colors', function ($query) use ($colorId) {
                    $query->where('color_id', $colorId);
                });
            });
        });
    }

    public static function scopeSizeFilter($query)
    {
        return $query->whereHas('sizes');
    }

    public function getStockAttribute()
    {
        if ($this->subcategory->size) {
            return ColorSize::whereHas('size.product', function (Builder $query) {
                $query->where('id', $this->id);
            })->sum('quantity');
        } elseif ($this->subcategory->color) {
            return ColorProduct::whereHas('product', function (Builder $query) {
                $query->where('id', $this->id);
            })->sum('quantity');
        } else {
            return $this->quantity;
        }
    }
}
