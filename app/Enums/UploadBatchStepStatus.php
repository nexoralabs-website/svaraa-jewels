<?php

namespace App\Enums;

enum UploadBatchStepStatus: int
{
    case PENDING = 0;
    case RUNNING = 1;
    case COMPLETED = 2;
    case FAILED = 3;
}
