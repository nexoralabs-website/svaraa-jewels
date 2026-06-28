<?php

namespace App\Enums;

enum PreviewStatus: int
{
    case DRAFT = 0;
    case NEEDS_REVIEW = 1;
    case READY = 2;
    case PUBLISHED = 3;
    case FAILED = 4;
}
