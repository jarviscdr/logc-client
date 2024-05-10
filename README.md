# LogClient 日志中心客户端
当前客户端是用于适配LogC日志中心的客户端，用于快捷上报日志使用；

## 安装
```bash
composer require jarviscdr/logc-client
```

## 使用
```php
// 创建实例
Client::getInstance()
    ->setApiClient('http://127.0.0.1:10001', 1.0) // 设置服务地址和请求超时时间
    ->setProject('测试')                          // 设置默认项目名称(也可以在后续上报日志时指定项目名称)
    ->setThrowException(false);                  // 设置是否抛出异常

// 上报日志
Client::getInstance()->report('订单请求异常', ['order'], Constant::DEBUG, 'test', Client::REPORT_TYPE_API);

// 使用配套函数上报日志
logc(['err' => -1, 'data' => '订单请求异常', 'oid' => 1234567890], 'order,alipay', Constant::DEBUG);

```
