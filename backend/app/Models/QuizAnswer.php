<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'option_id',
        'answer_text',
        'points_awarded',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'points_awarded' => 'decimal:2',
            'is_correct' => 'boolean',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class);
    }
}
