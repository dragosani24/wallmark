<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLinkRequest;
use App\Models\Link;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    /**
     * Browse every saved link with search and pagination.
     */
    public function index(Request $request): View
    {
        $query = Link::query()->with('tags')->latest();

        $search = trim((string) $request->query('q'));
        if ($search !== '') {
            $like = '%'.str_replace('%', '\%', $search).'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('title', 'like', $like)
                    ->orWhere('url', 'like', $like);
            });
        }

        return view('admin.links.index', [
            'links' => $query->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }

    /**
     * Show the edit form for a link.
     */
    public function edit(Link $link): View
    {
        return view('admin.links.edit', [
            'link' => $link->load('tags'),
            'allTags' => Tag::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the editable fields of a link.
     */
    public function update(UpdateLinkRequest $request, Link $link): RedirectResponse
    {
        $link->update([
            'title' => $request->string('title')->toString(),
            'description' => $request->filled('description') ? $request->string('description')->toString() : null,
        ]);

        if ($request->has('tags')) {
            $ids = [];
            foreach ((array) $request->input('tags', []) as $name) {
                $name = mb_strtolower(trim((string) $name));
                if ($name !== '') {
                    $ids[] = Tag::firstOrCreate(['name' => $name])->id;
                }
            }
            $link->tags()->sync($ids);
        }

        return redirect()->route('admin.links.index')->with('status', 'Enlace actualizado.');
    }

    /**
     * Delete a link from the wall.
     */
    public function destroy(Link $link): RedirectResponse
    {
        $link->delete();

        return redirect()->route('admin.links.index')->with('status', 'Enlace eliminado.');
    }
}
