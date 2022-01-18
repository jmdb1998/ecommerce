<?php

namespace App\Http\Controllers;

use App\Models\Category;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        $categories = Category::all();
        return view('welcome', compact('categories'));
    }
}
