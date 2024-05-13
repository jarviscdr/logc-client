<?php

namespace Jarviscdr\LogcClient;

use GuzzleHttp\Client as HttpClient;
use WebSocket\Client as WsClient;

class Client
{
    /**
     * API请求客户端
     *
     * @var \GuzzleHttp\Client
     */
    private $apiClient;

    /**
     * API请求Host
     *
     * @var string
     */
    private $apiHost = '';

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
     * 默认标签
     *
     * @var []string
     */
    private $tags = [];

    /**
     * 上报日志错误时是否抛出异常
     *
     * @var bool
     */
    private $throwException = true;

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
    public function setApiClient($host, $timeout = 2.0)
    {
        $this->apiHost = $host;
        $this->apiClient = new HttpClient([
            'timeout'  => $timeout,
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
     * 设置默认标签
     *
     * @param  []string $tags
     * @return self
     * @author Jarvis
     * @date   2024-05-13 11:41
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * 设置是否抛出异常
     *
     * @param  bool $throwException
     * @return self
     * @author Jarvis
     * @date   2024-05-10 22:40
     */
    public function setThrowException($throwException)
    {
        $this->throwException = $throwException;
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
            'tags' => $tags ?: $this->tags,
            'type' => $type,
            'time' => date('Y-m-d H:i:s'),
            'content' => $content,
        ];

        $exception = null;
        try {
            if ($clientType === 'cli') {
                return $this->wsSend($data);
            } else {
                return $this->apiSend($data);
            }
        } catch (LogcReportException $e) {
            // 自定义日志错误，无需进行转换
            $exception = $e;
        } catch (\Throwable $th) {
            // 其他错误，需要转换为自定义日志错误
            $exception = new LogcReportException('请求日志服务API失败:'.$th->getMessage());
        }

        // 判断是否需要抛出异常
        if ($this->throwException && !empty($exception)) {
            throw $exception;
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

        $response = $this->apiClient->post($this->apiHost.'/api/log/record', [
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
