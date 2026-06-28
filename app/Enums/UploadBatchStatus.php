<?php

namespace App\Enums;

enum UploadBatchStatus: int
{
    case QUEUED = 0;
    case EXTRACTING = 1;
    case PROCESSING = 2;
    case REVIEW_READY = 3;
    case COMPLETED = 4;
    case FAILED = 5;
}
