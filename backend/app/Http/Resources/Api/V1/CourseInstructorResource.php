<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseInstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_primary' => $this->is_primary,
            'instructor' => $this->whenLoaded('instructor', fn () => [
                'id' => $this->instructor->id,
                'name' => $this->instructor->name,
                'first_name' => $this->instructor->first_name,
                'last_name' => $this->instructor->last_name,
                'email' => $this->instructor->email,
                'avatar_url' => $this->instructor->avatar_url,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
