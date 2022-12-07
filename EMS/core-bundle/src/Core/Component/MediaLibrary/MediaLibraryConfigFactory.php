<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary;

use EMS\CoreBundle\Core\Config\AbstractConfigFactory;
use EMS\CoreBundle\Core\Config\ConfigFactoryInterface;

class MediaLibraryConfigFactory extends AbstractConfigFactory implements ConfigFactoryInterface
{
    /** {@inheritdoc} */
    public function create(array $options): MediaLibraryConfig
    {
        return new MediaLibraryConfig($options);
    }

    public function createFromHash(string $hash): MediaLibraryConfig
    {
        $options = $this->getOptions($hash);

        return $this->create($options);
    }
}
