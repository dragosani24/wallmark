<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Models\Tag;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Overview of the wall contents.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'linkCount' => Link::query()->count(),
            'tagCount' => Tag::query()->count(),
            'imagelessCount' => Link::query()->whereNull('image_url')->count(),
            'latestLinks' => Link::query()->with('tags')->latest()->limit(10)->get(),
            'topTags' => Tag::query()->withCount('links')->orderByDesc('links_count')->limit(10)->get(),
        ]);
    }
}
