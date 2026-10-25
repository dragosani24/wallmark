<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;

class TagController extends Controller
{
    /**
     * Delete a tag everywhere it is used.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('status', 'Etiqueta eliminada.');
    }
}
