<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CoreBundle\Core\Config\ConfigFactoryInterface;

class MediaLibraryConfigFactory implements ConfigFactoryInterface
{
    /** {@inheritdoc} */
    public function create(array $options): MediaLibraryConfig
    {
        return new MediaLibraryConfig($options);
    }
}
