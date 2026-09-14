<?php

namespace App\Enums;

enum LearningMaterialType: string
{
    case Document = 'document';
    case Video = 'video';
    case Audio = 'audio';
    case Link = 'link';
    case Presentation = 'presentation';
    case Other = 'other';
}
