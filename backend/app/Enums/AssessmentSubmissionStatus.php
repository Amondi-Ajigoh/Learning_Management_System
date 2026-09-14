<?php

namespace App\Enums;

enum AssessmentSubmissionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Graded = 'graded';
    case Returned = 'returned';
}
