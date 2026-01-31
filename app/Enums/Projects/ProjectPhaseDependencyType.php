<?php

namespace App\Enums\Projects;

enum ProjectPhaseDependencyType: string
{
    case FS = 'finish_to_start';
    case SS = 'start_to_start';
    case FF = 'finish_to_finish';
}
