<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Models\Tag;
use App\Services\MetadataExtractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LinkController extends Controller
{
    public function __construct(private readonly MetadataExtractor $extractor) {}

    /**
     * List links with optional search and tag filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Link::query()->with('tags')->latest();

        $search = trim((string) $request->query('q'));
        if ($search !== '') {
            $like = '%'.str_replace('%', '\%', $search).'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('title', 'like', $like)
                    ->orWhere('url', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('site_name', 'like', $like);
            });
        }

        $tag = trim((string) $request->query('tag'));
        if ($tag !== '') {
            $query->whereHas('tags', fn ($builder) => $builder->where('name', $tag));
        }

        $perPage = min(max((int) $request->query('per_page', 24), 1), 100);

        return LinkResource::collection($query->paginate($perPage)->withQueryString());
    }

    /**
     * Store a link, extracting its remote metadata.
     */
    public function store(StoreLinkRequest $request): JsonResponse
    {
        $url = $request->string('url')->toString();
        $metadata = $this->extractor->extract($url);

        $link = Link::create([
            'url' => $url,
            ...$metadata,
        ]);

        $this->syncTags($link, $request->input('tags', []));

        return (new LinkResource($link->load('tags')))->response()->setStatusCode(201);
    }

    /**
     * Update the editable fields of a link.
     */
    public function update(UpdateLinkRequest $request, Link $link): LinkResource
    {
        $link->update([
            'title' => $request->string('title')->toString(),
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
        ]);

        if ($request->has('tags')) {
            $this->syncTags($link, $request->input('tags', []));
        }

        return new LinkResource($link->load('tags'));
    }

    /**
     * Remove a link from the wall.
     */
    public function destroy(Link $link): JsonResponse
    {
        $link->delete();

        return response()->json(status: 204);
    }

    /**
     * Attach the given tag names, creating missing tags as lowercase names.
     *
     * @param  array<int, string|null>  $names
     */
    private function syncTags(Link $link, array $names): void
    {
        $normalized = [];
        foreach ($names as $name) {
            $name = mb_strtolower(trim((string) $name));
            if ($name !== '') {
                $normalized[$name] = true;
            }
        }

        $ids = [];
        foreach (array_keys($normalized) as $name) {
            $ids[] = Tag::firstOrCreate(['name' => $name])->id;
        }

        $link->tags()->sync($ids);
    }
}
