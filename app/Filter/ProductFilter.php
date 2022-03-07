<?php

namespace App\Filter;

use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends QueryFilter
{
    public function rules():array
    {

        return [
            'search' => 'filled',
            'category' => 'filled',
            'subcategory' => 'filled',
            'price' => 'filled',
            'brand' => 'filled',
            'status' => 'in:1,2',
            'colors' => 'in:0,1',
            'sizes' => 'in:0,1'
        ];
    }

    public function search($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    public function category($query, $categorySearch)
    {
        return $query->whereHas('subcategory', function (Builder $query) use ($categorySearch) {
            $query->whereHas('category', function (Builder $query) use ($categorySearch) {
                $query->where('name', 'LIKE', "%{$categorySearch}%");
            });
        });
    }

    public function subcategory($query, $subcategorySearch)
    {
        return $query->whereHas('subcategory', function (Builder $query) use ($subcategorySearch) {
            $query->where('name', 'LIKE', "%{$subcategorySearch}%");
        });
    }

    public function brand($query, $brandSearch)
    {
        return $query->whereHas('brand', function (Builder $query) use ($brandSearch) {
            $query->where('name', 'LIKE', "%{$brandSearch}%");
        });
    }

    public function status($query, $status)
    {
        return $query->where('status', $status);
    }

    public function colors($query, $colorId)
    {
        /*return $query->whereHas('colors', function ($query) use ($colorId) {
            $query->where('colors.id', $colorId);
        })->orWhereHas('sizes', function ($query) use ($colorId) {
            $query->where(function ($query) use ($colorId) {
                $query->whereHas('colors', function ($query) use ($colorId) {
                    $query->where('color_id', $colorId);
                });
            });
        });*/
        return $query->whereHas('colors')
            ->orWhereHas('sizes');
    }

    public function sizes($query)
    {
        return $query->whereHas('sizes');
    }
}
