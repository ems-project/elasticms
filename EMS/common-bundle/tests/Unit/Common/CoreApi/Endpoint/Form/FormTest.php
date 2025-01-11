<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\Form;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Form\Form;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;

final class FormTest extends TestCase
{
    private Client $client;
    private Form $form;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);

        $this->form = new Form($this->client);
        parent::setUp();
    }

    public function testCreateVerification(): void
    {
        $testCode = '156897';

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn(['code' => $testCode]);

        $this->client->method('post')->willReturn($result);

        $this->assertEquals($testCode, $this->form->createVerification('test'));
    }

    public function testGetVerification(): void
    {
        $testCode = '989786';

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn(['code' => $testCode]);

        $this->client->method('get')->willReturn($result);

        $this->assertEquals($testCode, $this->form->getVerification('test'));
    }
}
