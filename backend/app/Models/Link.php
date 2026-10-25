<?php

namespace App\Models;

use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['url', 'title', 'description', 'image_url', 'favicon_url', 'site_name'])]
class Link extends Model
{
    /** @use HasFactory<LinkFactory> */
    use HasFactory;

    /**
     * The tags attached to this link.
     *
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }
}
