<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        if ($this->resource->relationLoaded('links_count') || isset($this->links_count)) {
            $data['links_count'] = (int) $this->links_count;
        }

        return $data;
    }
}
