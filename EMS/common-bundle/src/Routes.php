<?php

declare(strict_types=1);

namespace EMS\CommonBundle;

enum Routes: string
{
    case ASSET = 'ems_asset';

    case METRICS = 'ems_metrics';
    case METRICS_PATH = '/metrics';
}
