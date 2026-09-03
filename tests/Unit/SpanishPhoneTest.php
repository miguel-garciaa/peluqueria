<?php

namespace Tests\Unit;

use App\Support\SpanishPhone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SpanishPhoneTest extends TestCase
{
    #[DataProvider('validPhoneNumbers')]
    public function test_it_formats_spanish_phone_numbers(string $input): void
    {
        $this->assertSame('+34 600 12 34 56', SpanishPhone::format($input));
    }

    public static function validPhoneNumbers(): array
    {
        return [
            ['600123456'],
            ['600 123 456'],
            ['+34600123456'],
            ['+34 600 12 34 56'],
            ['0034 600 123 456'],
        ];
    }

    public function test_it_keeps_invalid_input_available_for_validation(): void
    {
        $this->assertSame('123', SpanishPhone::format('123'));
        $this->assertNull(SpanishPhone::format(''));
    }
}
