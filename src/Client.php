<?php

namespace Jarviscdr\LogcClient;

use GuzzleHttp\Client as HttpClient;
use WebSocket\Client as WsClient;

class Client
{
    /**
     * 上报类型 WebSocket
     */
    public const REPORT_TYPE_WS = 'cli';

    /**
     * 上报类型 Api
     */
    public const REPORT_TYPE_API = 'fpm';

    /**
     * API请求客户端
     *
     * @var \GuzzleHttp\Client
     */
    private $apiClient;

    /**
     * WebSocket客户端
     *
     * @var \WebSocket\Client
     */
    private $wsClient;

    /**
     * 项目标识
     *
     * @var string
     */
    private $project = '';

    /**
     * 当前类的实例
     *
     * @var Client
     */
    private static $_instance = null;

    /**
     * 获取实例
     *
     * @return Client
     * @author Jarvis
     * @date   2024-02-25 15:10
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 私有克隆方法，防止克隆
     *
     * @return void
     * @author Jarvis
     * @date   2024-02-25 15:10
     */
    private function __clone()
    {
        trigger_error('Clone is not allowed !');
    }

    /**
     * 设置WebSocket客户端
     *
     * @param  string $host
     * @return self
     * @author Jarvis
     * @date   2024-02-25 13:06
     */
    public function setWsClient($host)
    {
        $this->wsClient = new WsClient($host);
        return $this;
    }

    /**
     * 设置API客户端
     *
     * @param  string $host
     * @return self
     * @author Jarvis
     * @date   2024-02-25 13:07
     */
    public function setApiClient($host)
    {
        $this->apiClient = new HttpClient([
            'base_uri' => $host,
            'timeout'  => 2.0,
        ]);
        return $this;
    }

    /**
     * 设置项目标识
     *
     * @param  string $project
     * @return self
     * @author Jarvis
     * @date   2024-02-25 15:13
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * 上报日志
     *
     * @param  array|string $content
     * @param  array        $tags
     * @param  int          $type
     * @param  string       $project
     * @return void
     * @author Jarvis
     * @date   2024-02-25 11:24
     */
    public function report($content, array $tags, int $type, string $project = '', $forceType = null)
    {
        switch ($forceType) {
            case 'cli':
                $clientType = 'cli';
                break;
            case 'fpm':
                $clientType = 'fpm';
                break;
            default:
                $clientType = php_sapi_name();
                break;
        }

        $data = [
            'project' => $project ?: $this->project,
            'type' => $type,
            'tags' => $tags,
            'content' => $content,
            'time' => date('Y-m-d H:i:s'),
        ];

        if ($clientType === 'cli') {
            return $this->wsSend($data);
        } else {
            return $this->apiSend($data);
        }
    }

    /**
     * API发送日志
     *
     * @param  array $data
     * @return void
     * @author Jarvis
     * @date   2024-02-25 11:24
     */
    protected function apiSend($data)
    {
        if (empty($this->apiClient)) {
            throw new LogcReportException('未配置API客户端');
        }

        $response = $this->apiClient->post('/api/log/record', [
            'json' => $data,
        ]);

        if ($response->getStatusCode() != 200) {
            throw new LogcReportException();
        }

        $result = json_decode($response->getBody(), true);
        if (empty($result)) {
            throw new LogcReportException();
        }

        if ($result['code'] > 0) {
            throw new LogcReportException($result['msg'], $result['code'], $result['data']);
        }
    }

    /**
     * WebSocket发送日志
     *
     * @param  array $data
     * @return void
     * @author Jarvis
     * @date   2024-02-25 11:35
     */
    protected function wsSend($data)
    {
        if (empty($this->wsClient)) {
            throw new LogcReportException('未配置WebSocket客户端');
        }

        $this->wsClient->text(json_encode($data));
        $response = $this->wsClient->receive();
        $result = json_decode($response, true);

        if (empty($result)) {
            throw new LogcReportException();
        }

        if ($result['code'] > 0) {
            throw new LogcReportException($result['msg'], $result['code'], $result['data']);
        }
    }
}
