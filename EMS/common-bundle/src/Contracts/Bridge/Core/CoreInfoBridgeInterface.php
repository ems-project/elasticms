<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Contracts\Bridge\Core;

use EMS\CommonBundle\Common\EMSLink;

interface CoreInfoBridgeInterface
{
    /**
     * @param list<string> $environments
     *
     * @return array<string, array{
     *     'id': int,
     *     'draft': bool,
     *     'revisions': array<string, ?int>,
     *     'status': array<string, 'not_published'|'outdated'|'published'>
     * }>
     */
    public function documents(array $environments, EMSLink ...$emsLinks): array;
}
