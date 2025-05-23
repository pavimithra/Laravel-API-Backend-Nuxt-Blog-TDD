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
            'category_name' => $this->category->name,            
            'description' => Str::words(Str::of($this->description)->stripTags(), 7 ),
            'status' => $this->status,
            'image' => asset('storage/'.$this->image),
        ];
    }
}
