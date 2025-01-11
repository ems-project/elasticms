<?php

declare(strict_types=1);

namespace EMS\FormBundle\Tests\Unit\Components\Constraint;

use EMS\FormBundle\Components\Constraint\IsInternationalPhoneNumber;
use EMS\FormBundle\Components\Constraint\IsInternationalPhoneNumberValidator;
use EMS\FormBundle\Components\ValueObject\InternationalPhoneNumber;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class IsInternationalPhoneNumberValidatorTest extends ConstraintValidatorTestCase
{
    #[\Override]
    protected function createValidator(): IsInternationalPhoneNumberValidator
    {
        return new IsInternationalPhoneNumberValidator();
    }

    /**
     * @dataProvider getInvalidPhoneNumbers
     */
    public function testInvalidPhoneNumbers(string $phoneNumber): void
    {
        $constraint = new IsInternationalPhoneNumber([
            'message' => 'myMessage',
        ]);

        $this->validator->validate($phoneNumber, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{string}}', $phoneNumber)
            ->assertRaised();
    }

    /**
     * @return string[][]
     */
    public function getInvalidPhoneNumbers(): array
    {
        return [
            ['+123456789'],
            ['+1234567890'],
            ['+123456789012'],

            ['32470123456'],
            ['032470123456'],
            ['470123456'],

            ['+3229876543'],

            ['+32047012345'],
            ['00320470123456'],
            ['0032047012345'],
            ['0032470123456'],
            ['0470123456'],

            ['003229876543'],
            ['029876543'],

            ['0490/73 26 28'],

            ['0032 490 73 26 28'],

            ['0482 83 81 27'],
            ['0482/83-81-27'],

            ['081582098'],
            ['081/58.20.98'],
            ['081/582.098'],
            ['081 58 20 98'],
            ['081/582 098'],

            ['00 32 2 226 88 88'],
            ['0032 81 58 20 98'],
            ['00 32 482 83 81 27'],
        ];
    }

    /**
     * @dataProvider getValidPhoneNumbers
     */
    public function testValidPhoneNumber(string $phoneNumber): void
    {
        $this->validator->validate($phoneNumber, new IsInternationalPhoneNumber());
        $this->assertNoViolation();
    }

    /**
     * @return string[][]
     */
    public function getValidPhoneNumbers(): array
    {
        return [
            ['+12345678901'],

            ['+32470123456'],
            ['+320470123456'],

            ['+32490732628'],
            ['+32.490.73.26.28'],

            ['+32482838127'],
            ['+32 482 83 81 27'],

            ['+3222268888'],
            ['+32 2 226 88 88'],

            ['+32 81 58.20.98'],
            ['+32 81 58 20 98'],
        ];
    }

    /**
     * @dataProvider getTransformPhoneNumbers
     */
    public function testTransformPhoneNumbers(string $input, string $output): void
    {
        $objectValue = new InternationalPhoneNumber($input);
        $this->assertEquals($output, $objectValue->transform());
    }

    /**
     * @return string[][]
     */
    public function getTransformPhoneNumbers(): array
    {
        return [
            ['+320470123456', '+32470123456'],
            ['+32490732628', '+32490732628'],
            ['+32.490.73.26.28', '+32490732628'],
            ['+32482838127', '+32482838127'],
            ['+32 482 83 81 27', '+32482838127'],

            ['+3222268888', '+3222268888'],
            ['+32 2 226 88 88', '+3222268888'],
            ['+32 81 58.20.98', '+3281582098'],
            ['+32 81 58 20 98', '+3281582098'],

            ['+32 (0)490 73 26 28', '+32490732628'],
            ['+32 (0)81 58 20 98', '+3281582098'],
            ['+32 (0)81 58 20 98', '+3281582098'],
        ];
    }
}
