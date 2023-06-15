<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Component\MediaLibrary\Request;

use Symfony\Component\HttpFoundation\Request;

class MediaLibraryRequest
{
    public readonly int $from;
    public readonly string $path;

    public function __construct(Request $request)
    {
        $this->from = $request->query->getInt('from');
        $this->path = $request->query->has('path') ? $request->query->get('path').'/' : '/';
    }
}
