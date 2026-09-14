<?php

namespace App\Models;

use App\Enums\LearningMaterialType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'type',
        'url',
        'storage_disk',
        'storage_path',
        'mime_type',
        'size_bytes',
        'duration_seconds',
        'position',
        'is_downloadable',
    ];

    protected function casts(): array
    {
        return [
            'type' => LearningMaterialType::class,
            'size_bytes' => 'integer',
            'duration_seconds' => 'integer',
            'position' => 'integer',
            'is_downloadable' => 'boolean',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
