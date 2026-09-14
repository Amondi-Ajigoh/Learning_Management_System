<?php

namespace App\Enums;

enum LessonType: string
{
    case Text = 'text';
    case Video = 'video';
    case Mixed = 'mixed';
    case Resource = 'resource';
}
