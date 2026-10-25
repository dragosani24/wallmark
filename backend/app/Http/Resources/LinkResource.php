<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'favicon_url' => $this->favicon_url,
            'site_name' => $this->site_name,
            'created_at' => $this->created_at?->toIso8601String(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
