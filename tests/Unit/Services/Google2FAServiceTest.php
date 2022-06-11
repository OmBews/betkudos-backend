<?php

namespace Tests\Unit\Services;

use App\Services\Google2FAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class Google2FAServiceTest extends TestCase
{
    use WithFaker;

    public function testCanCreateAnSecretKey()
    {
        $service = app()->make(Google2FAService::class);

        $secretKey = $service->generateSecretKey();

        $this->assertNotEmpty($secretKey);
        $this->assertTrue(strlen($secretKey) === 16);
    }
}
