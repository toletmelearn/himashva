<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::active()->where('slug', $slug)->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
