<?php

use App\Models\Product;
use App\Models\Size;
use Illuminate\Validation\Rule;

class ProductFilter
{

    public function rules(): array
    {
        return [
            'search' => 'filled',
        ];
    }

    public function search($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

}
