<?php

namespace App\Services;

use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;

class Google2FAService
{
    protected $provider;

    public function __construct(Google2FA $google2FA)
    {
        $this->provider = $google2FA->setQrCodeService(new Chillerlan());
    }

    public function generateSecretKey()
    {
        try {
            return $this->provider->generateSecretKey();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getQRCodeInline(string $secretKey, string $email): string
    {
        return $this->provider->getQRCodeUrl(
            config('app.name'),
            $email,
            $secretKey
        );
    }

    public function checkOTP($secretKey, $OTP)
    {
        return $this->provider->verifyKey($secretKey, $OTP);
    }
}
