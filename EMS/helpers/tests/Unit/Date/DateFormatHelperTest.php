<?php

declare(strict_types=1);

namespace EMS\Helpers\Tests\Unit\Date;

use EMS\Helpers\Date\DateFormatHelper;
use Monolog\Test\TestCase;

class DateFormatHelperTest extends TestCase
{
    public function testFormatDate(): void
    {
        $this->assertEquals('Y/m/d g:i:s', DateFormatHelper::convert('java', 'yyyy/MM/dd hh:mm:ss'));
        $this->assertEquals('d-FM-Y HH:m:ss', DateFormatHelper::convert('js', 'dd-MMM-yyyy HH:mm:ss'));
    }
}
