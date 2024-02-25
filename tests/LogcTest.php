<?php

namespace Jarviscdr\LogcClient;

use PHPUnit\Framework\TestCase;

class LogcTest extends TestCase
{
    public function setUp(): void
    {
        error_reporting(-1);
    }

    public function testApiReport(): void
    {
        Client::getInstance()->setApiClient('http://127.0.0.1:8787')->setProject('test');
        Client::getInstance()->report('订单请求异常', ['order'], 1, 'test', Client::REPORT_TYPE_API);
        $this->assertTrue(true);
    }

    public function testWsReport(): void
    {
        Client::getInstance()->setWsClient('ws://127.0.0.1:8788')->setProject('test');
        sleep(1);
        logc('订单请求异常');
        $this->assertTrue(true);
    }
}
