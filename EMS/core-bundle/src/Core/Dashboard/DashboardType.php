<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Dashboard;

use Symfony\Component\Translation\TranslatableMessage;

use function Symfony\Component\Translation\t;

enum DashboardType: string
{
    case EXPORT = 'ems_core.dashboard.export';
    case REVISION_TASK = 'ems_core.dashboard.revision_task';
    case TEMPLATE = 'ems_core.dashboard.template';

    public function getLabel(): TranslatableMessage
    {
        return match ($this) {
            self::EXPORT => t('ems_core.dashboard.export.label', [], 'emsco-core') ,
            self::REVISION_TASK => t('ems_core.dashboard.revision_task.label', [], 'emsco-core'),
            self::TEMPLATE => t('ems_core.dashboard.template.label', [], 'emsco-core')
        };
    }
}
