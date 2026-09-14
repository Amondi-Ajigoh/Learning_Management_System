<?php

namespace App\Models;

use App\Enums\AssessmentSubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'user_id',
        'submission_url',
        'submission_text',
        'score',
        'status',
        'feedback',
        'submitted_at',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'status' => AssessmentSubmissionStatus::class,
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
