<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    /**
     * Tag chips and typeahead suggestions ordered by usage.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Tag::query()->withCount('links');

        $search = trim((string) $request->query('q'));
        if ($search !== '') {
            $query->where('name', 'like', '%'.str_replace('%', '\%', $search).'%');
        }

        $tags = $query->orderByDesc('links_count')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return TagResource::collection($tags);
    }
}
