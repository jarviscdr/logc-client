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
        Client::getInstance()->setApiClient('http://127.0.0.1:10001', 1.0)->setProject('测试');
        Client::getInstance()->setThrowException(false);
        // Client::getInstance()->report('订单请求异常', ['order'], 1, 'test', Client::REPORT_TYPE_API);
        logc(['err' => -1, 'data' => '订单请求异常', 'oid' => 1234567890], 'order,alipay');
        $this->assertTrue(true);
    }

    /* public function testWsReport(): void
    {
        Client::getInstance()->setWsClient('ws://127.0.0.1:10002')->setProject('测试');
        sleep(1);
        logc('订单请求异常');
        $this->assertTrue(true);
    } */
}
