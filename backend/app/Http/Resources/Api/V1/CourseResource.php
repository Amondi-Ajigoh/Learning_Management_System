<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'level' => $this->level?->value,
            'language' => $this->language,
            'estimated_minutes' => $this->estimated_minutes,
            'price' => $this->price,
            'currency' => $this->currency,
            'status' => $this->status?->value,
            'published_at' => $this->published_at?->toISOString(),

            'category' => $this->whenLoaded(
                'category',
                fn () => new CourseCategoryResource($this->category)
            ),

            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'first_name' => $this->creator->first_name,
                'last_name' => $this->creator->last_name,
                'email' => $this->creator->email,
                'avatar_url' => $this->creator->avatar_url,
            ]),

            'instructors' => CourseInstructorResource::collection(
                $this->whenLoaded('courseInstructors')
            ),

            'modules_count' => $this->when(
                isset($this->modules_count),
                $this->modules_count
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
