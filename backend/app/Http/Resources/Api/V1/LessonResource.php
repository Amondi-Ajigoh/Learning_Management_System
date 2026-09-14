<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'module_id' => $this->module_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->content,
            'type' => $this->type?->value,
            'duration_minutes' => $this->duration_minutes,
            'position' => $this->position,
            'is_preview' => $this->is_preview,
            'is_published' => $this->is_published,

            'materials_count' => $this->when(
                isset($this->materials_count),
                $this->materials_count
            ),

            'materials' => LearningMaterialResource::collection(
                $this->whenLoaded('materials')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
