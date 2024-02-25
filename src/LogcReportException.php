<?php

namespace Jarviscdr\LogcClient;

use Exception;

class LogcReportException extends Exception
{
    protected $code = 1;
    protected $message = '';
    protected $data = [];

    public function __construct($message = '上报日志失败', $code = 1, $data = [])
    {
        $this->message = "[Logc]{$message}";
        $this->code = $code;
        $this->data = $data;
    }
}
