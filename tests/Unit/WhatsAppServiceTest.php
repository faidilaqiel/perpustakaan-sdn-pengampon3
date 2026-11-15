<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\WhatsAppService;

class WhatsAppServiceTest extends TestCase
{
    /**
     * Test format nomor HP Indonesia standar (08xx -> 628xx)
     */
    public function test_format_phone_number_with_leading_zero(): void
    {
        $input = '081234567890';
        $formatted = WhatsAppService::formatPhone($input);

        $this->assertEquals('6281234567890', $formatted);
    }

    /**
     * Test format nomor HP yang diawali angka 8 tanpa 0 (8xx -> 628xx)
     */
    public function test_format_phone_number_without_leading_zero(): void
    {
        $input = '81234567890';
        $formatted = WhatsAppService::formatPhone($input);

        $this->assertEquals('6281234567890', $formatted);
    }

    /**
     * Test format nomor HP dengan karakter strip atau spasi
     */
    public function test_format_phone_number_with_special_characters(): void
    {
        $input = '0812-3456 7890';
        $formatted = WhatsAppService::formatPhone($input);

        $this->assertEquals('6281234567890', $formatted);
    }

    /**
     * Test format nomor HP kosong
     */
    public function test_format_phone_number_null_or_empty(): void
    {
        $this->assertNull(WhatsAppService::formatPhone(''));
        $this->assertNull(WhatsAppService::formatPhone(null));
    }
}

