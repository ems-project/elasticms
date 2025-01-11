<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Hook;

use DG\BypassFinals;
use PHPUnit\Runner\BeforeTestHook;

final class BypassFinalHook implements BeforeTestHook
{
    #[\Override]
    public function executeBeforeTest(string $test): void
    {
        BypassFinals::enable();
    }
}
