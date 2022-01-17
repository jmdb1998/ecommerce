<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
             [   'name' => 'Celurares y tablelts',
                'slug' => Str::slug('Celurares y tablets'),
                'icon' => '<i class="fas fa-mobile-alt"></i>'
             ],
            [   'name' => 'Tv, audio, video',
                'slug' => Str::slug('Tv, audio, video'),
                'icon' => '<i class="fas fa-tv"></i>'
            ],
            [   'name' => 'Consolas y videojuegos',
                'slug' => Str::slug('Consolas y videojuegos'),
                'icon' => '<i class="fas fa-gamepad"></i>'
            ],
            [   'name' => 'Computacion',
                'slug' => Str::slug('Computacion'),
                'icon' => '<i class="fas fa-laptop"></i>'
            ],
            [   'name' => 'Moda',
                'slug' => Str::slug('Moda'),
                'icon' => '<i class="fas fa-tshirt"></i>'
            ],
        ];

        foreach ($categories as $category){
            $category = Category::factory()->create($category);

            $brands = Brand::factory(4)->create();

            foreach ($brands as $brand){
                $brand->categories()->attach($category->id);
            }
        }
    }
}
