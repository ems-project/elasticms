<?php

declare(strict_types=1);

namespace EMS\FormBundle\Tests\Unit\Components\Constraint;

use EMS\FormBundle\Components\Constraint\IsBelgiumPhoneNumber;
use EMS\FormBundle\Components\Constraint\IsBelgiumPhoneNumberValidator;
use EMS\FormBundle\Components\ValueObject\BelgiumPhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class IsBelgiumPhoneNumberValidatorTest extends ConstraintValidatorTestCase
{
    #[\Override]
    protected function createValidator(): IsBelgiumPhoneNumberValidator
    {
        return new IsBelgiumPhoneNumberValidator();
    }

    #[DataProvider('getInvalidPhoneNumbers')]
    public function testInvalidPhoneNumbers(string $phoneNumber): void
    {
        $constraint = new IsBelgiumPhoneNumber([
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
    public static function getInvalidPhoneNumbers(): array
    {
        return [
            ['+123456789'],
            ['+1234567890'],
            ['+12345678901'],
            ['+123456789012'],
            ['32470123456'],
            ['032470123456'],
            ['470123456'],
            ['+320470123456'],
            ['+32047012345'],
            ['00320470123456'],
            ['0032047012345'],
        ];
    }

    #[DataProvider('getValidPhoneNumbers')]
    public function testValidPhoneNumber(string $phoneNumber): void
    {
        $this->validator->validate($phoneNumber, new IsBelgiumPhoneNumber());
        $this->assertNoViolation();
    }

    /**
     * @return string[][]
     */
    public static function getValidPhoneNumbers(): array
    {
        return [
            ['+32470123456'],
            ['0032470123456'],
            ['0470123456'],
            ['+3229876543'],
            ['003229876543'],
            ['029876543'],

            ['+32490732628'],
            ['0490/73 26 28'],
            ['+32.490.73.26.28'],
            ['0032 490 73 26 28'],
            ['+32482838127'],
            ['0482 83 81 27'],
            ['0482/83-81-27'],
            ['+32 482 83 81 27'],
            ['00 32 482 83 81 27'],

            ['+3222268888'],
            ['+32 2 226 88 88'],
            ['00 32 2 226 88 88'],
            ['081582098'],
            ['081/58.20.98'],
            ['081/582.098'],
            ['081 58 20 98'],
            ['081/582 098'],
            ['+32 81 58.20.98'],
            ['+32 81 58 20 98'],
            ['0032 81 58 20 98'],
        ];
    }

    #[DataProvider('getTransformPhoneNumbers')]
    public function testTransformPhoneNumbers(string $input, string $output): void
    {
        $objectValue = new BelgiumPhoneNumber($input);
        $this->assertEquals($output, $objectValue->transform());
    }

    /**
     * @return string[][]
     */
    public static function getTransformPhoneNumbers(): array
    {
        return [
            ['+32490732628', '+32490732628'],
            ['0490/73 26 28', '0490732628'],
            ['+32.490.73.26.28', '+32490732628'],
            ['0032 490 73 26 28', '0032490732628'],
            ['+32482838127', '+32482838127'],
            ['0482 83 81 27', '0482838127'],
            ['0482/83-81-27', '0482838127'],
            ['+32 482 83 81 27', '+32482838127'],
            ['00 32 482 83 81 27', '0032482838127'],

            ['+3222268888', '+3222268888'],
            ['+32 2 226 88 88', '+3222268888'],
            ['00 32 2 226 88 88', '003222268888'],
            ['081582098', '081582098'],
            ['081/58.20.98', '081582098'],
            ['081/582.098', '081582098'],
            ['081 58 20 98', '081582098'],
            ['081/582 098', '081582098'],
            ['+32 81 58.20.98', '+3281582098'],
            ['+32 81 58 20 98', '+3281582098'],
            ['0032 81 58 20 98', '003281582098'],

            // ['+32 (0)490 73 26 28', '+32490732628'],
            // ['+32 (0)81 58 20 98', '+3281582098'],
            // ['+32 (0)81 58 20 98', '+3281582098'],
        ];
    }
}
