<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Contracts\ContentType;

interface ContentTypeInterface
{
    /**
     * Used by the cacheHelper, if the cache contentType has the same cache compare it will be used.
     * Total needs to be included, for deleted documents on a contentType.
     *
     * Also used in the formBundle for invalidating formConfig cache.
     */
    public function getCacheValidityTag(): string;
}
