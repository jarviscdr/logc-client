<?php
namespace Jarviscdr\LogcClient;

class Constant {
    /**
     * 上报类型 WebSocket
     */
    public const REPORT_TYPE_WS = 'cli';

    /**
     * 上报类型 Api
     */
    public const REPORT_TYPE_API = 'fpm';

    /**
     * 错误日志
     */
    public const ERROR   = 1;

    /**
     * 警告日志
     */
    public const WARNING = 2;

    /**
     * 信息日志
     */
    public const INFO = 3;

    /**
     * 调试日志
     */
    public const DEBUG = 4;

    /**
     * 通知日志
     */
    public const NOTICE = 5;

}