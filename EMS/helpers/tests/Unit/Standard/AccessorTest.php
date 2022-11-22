<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Standard;

use EMS\Helpers\Standard\Accessor;
use PHPUnit\Framework\TestCase;

class AccessorTest extends TestCase
{
    public function testFieldPathToPropertyPath()
    {
        self::assertSame('[src][table][0][title]', Accessor::fieldPathToPropertyPath('src.table[0].title'));
        self::assertSame('[locales][fr][files][0][_sha1]', Accessor::fieldPathToPropertyPath('locales.fr.files[0]._sha1'));
    }
}
