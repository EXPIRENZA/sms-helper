<?php
require './src/SmsHelper.php';

use Expirenza\src\SmsHelper;

class SmsHelperTest extends PHPUnit\Framework\TestCase
{
    public function testApiKeyNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $smsHelper = new SmsHelper(null);
    }

    public function testApiKeySet(): void
    {
        $this->assertInstanceOf(
            SmsHelper::class,
            new SmsHelper('test key')
        );
    }

    public function testSendInDebug(): void
    {
        $smsHelper = new SmsHelper('test key', true);
        $this->assertTrue($smsHelper->send('+10000000000', 'some length text'));
    }

    public function testSendMaxLength(): void
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < 351; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        $smsHelper = new SmsHelper('test key', true);
        $this->expectException(InvalidArgumentException::class);
        $smsHelper->send('+10000000000', $randomString);
    }
}
