<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Accessor;
use PHPUnit\Framework\TestCase;

class AccessorAiTest extends TestCase
{
    public function testFieldPathToPropertyPath(): void
    {
        $this->assertSame('[field][subfield]', Accessor::fieldPathToPropertyPath('field.subfield'));
        $this->assertSame('[field][0]', Accessor::fieldPathToPropertyPath('field[0]'));
        $this->assertSame('[field][subfield][0]', Accessor::fieldPathToPropertyPath('field.subfield[0]'));
        $this->assertSame('[field][sub][0]', Accessor::fieldPathToPropertyPath('field.sub[0]'));
    }
}
