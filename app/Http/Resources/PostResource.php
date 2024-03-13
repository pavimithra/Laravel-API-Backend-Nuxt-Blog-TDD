<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PostResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => Str::words(Str::of($this->content)->stripTags(), 8 ),
            'content' => $this->content,
            'status' => $this->status,
            'image' => "http://localhost:8000/storage/".$this->image,
        ];
    }
}
