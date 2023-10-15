<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\StoreData;

use EMS\CommonBundle\Common\StoreData\StoreDataHelper;
use PHPUnit\Framework\TestCase;

class StoreDataHelperAiTest extends TestCase
{
    public function testGetKey(): void
    {
        $key = 'test_key';
        $helper = new StoreDataHelper($key);
        $this->assertSame($key, $helper->getKey());
    }

    public function testGetData(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $helper = new StoreDataHelper('key', $data);
        $this->assertSame($data, $helper->getData());
    }

    public function testGet(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $helper = new StoreDataHelper('key', $data);
        $this->assertSame('John', $helper->get('[name]'));
        $this->assertSame(30, $helper->get('[age]'));
    }

    public function testSet(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $helper = new StoreDataHelper('key', $data);
        $helper->set('[name]', 'Jane');
        $helper->set('[age]', 25);
        $this->assertSame('Jane', $helper->get('[name]'));
        $this->assertSame(25, $helper->get('[age]'));
    }
}
