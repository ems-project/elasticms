<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Tests\Unit\Common\CoreApi\Endpoint\Form;

use EMS\CommonBundle\Common\CoreApi\Client;
use EMS\CommonBundle\Common\CoreApi\Endpoint\Form\Form;
use EMS\CommonBundle\Common\CoreApi\Result;
use PHPUnit\Framework\TestCase;

class FormAiTest extends TestCase
{
    private Client $client;
    private Form $form;

    #[\Override]
    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->form = new Form($this->client);
    }

    public function testCreateVerification(): void
    {
        $resultData = ['code' => '123456'];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($resultData);

        $this->client->expects($this->once())
            ->method('post')
            ->with('api/forms/verifications', ['value' => 'test-value'])
            ->willReturn($result);

        $code = $this->form->createVerification('test-value');

        $this->assertEquals('123456', $code);
    }

    public function testGetVerification(): void
    {
        $resultData = ['code' => '654321'];

        $result = $this->createMock(Result::class);
        $result->method('getData')->willReturn($resultData);

        $this->client->expects($this->once())
            ->method('get')
            ->with('api/forms/verifications', ['value' => 'test-value'])
            ->willReturn($result);

        $code = $this->form->getVerification('test-value');

        $this->assertEquals('654321', $code);
    }
}
