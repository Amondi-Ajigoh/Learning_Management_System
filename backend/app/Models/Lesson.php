<?php

namespace App\Models;

use App\Enums\LessonType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'slug',
        'summary',
        'content',
        'type',
        'duration_minutes',
        'position',
        'is_preview',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'type' => LessonType::class,
            'duration_minutes' => 'integer',
            'position' => 'integer',
            'is_preview' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class)->orderBy('position');
    }

    public function quiz(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
