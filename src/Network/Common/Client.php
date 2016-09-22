<?php

namespace Zan\Framework\Network\Common;

use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Foundation\Exception\BusinessException;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Network\Common\Exception\UnexpectedResponseException;
use Zan\Framework\Utilities\Types\Json;

class Client implements Async
{
    const JAVA_TYPE = 'java';
    const PHP_TYPE = 'php';

    private static $apiConfig;

    /** @var  HttpClient */
    private $httpClient;

    private $type;

    private $host;
    private $port;

    private $timeout;

    private $uri;
    private $method;

    private $params;

    private $format = 'form';


    private function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public static function call($api, $params = [],$callback = null, $method = 'POST',$format='form')
    {
        $apiConfig = self::getApiConfig($api);
        $params = self::filterParams($params, $apiConfig['type']);
        $client = new self($apiConfig['host'], $apiConfig['port']);
        $client->setType($apiConfig['type']);
        $client->setTimeout($apiConfig['timeout']);
        $client->setMethod($method);
        $client->setUri($api);
        $client->setParams($params);
        $client->setFormat($format);

        yield $client->build();
    }

    public function execute(callable $callback, $task)
    {
        $this->httpClient->setCallback($this->getCallback($callback))->handle();
    }

    private function setType($type)
    {
        $this->type = $type == 'local' ? self::PHP_TYPE : $type;
    }

    private function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    private function setUri($api)
    {
        if (false !== strpos($api, '.')) {
            $this->uri = '/' . str_replace('.', '/', $api);
        }
    }

    private function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    private function setParams($params)
    {
        $this->params = $params;
    }

    private function setFormat($format){
        $this->format = $format;
    }
    private function build()
    {
        $this->httpClient = new HttpClient($this->host, $this->port);

        $this->httpClient->setTimeout($this->timeout);
        $this->httpClient->setMethod($this->method);

        if ($this->method == 'GET') {
            $this->uri = $this->uri . '?' . http_build_query($this->params);
        } else {
            if ($this->type == self::PHP_TYPE) {
                switch ($this->format) {
                    case 'form':
                        $body = http_build_query($this->params);
                        $contentType = 'application/x-www-form-urlencoded';
                        break;
                    case 'json':
                        $body = json_encode($this->params);
                        $contentType = 'application/json';
                        break;

                }
            } else {
                $body = json_encode($this->params);
                $contentType = 'application/json';
            }
            $this->httpClient->setHeader([
                'Content-Type' => $contentType
            ]);
            $this->httpClient->setBody($body);
        }
        $this->httpClient->setUri($this->uri);

        return $this;
    }

    private function getCallback(callable $callback)
    {
        return function($response, $exception = null) use ($callback) {
            if ($exception) {
                call_user_func($callback, $response, $exception);
                return;
            }

            $body = $response->getBody();

            $jsonData = Json::decode($body, true, 512, JSON_BIGINT_AS_STRING);
            if (false === $jsonData || !is_array($jsonData)) {
                // TODO 分配 code
                $e = new UnexpectedResponseException('网络错误', 10000, NULL, ['response' => $response, 'request' => $this->getRequestMetadata()]);
                call_user_func($callback, null, $e);
                return;
            }

            // 检查格式
            if (!isset($jsonData['code']) || !array_key_exists('data', $jsonData)) {
                // TODO 分配 code, 调整提示语
                $e = new UnexpectedResponseException('服务方返回的数据格式有误', 10000, NULL, ['response' => $response, 'request' => $this->getRequestMetadata()]);
                call_user_func($callback, null, $e);
                return;
            }

            $code = $jsonData['code'];
            if ($code > 0) {
                $msg = isset($jsonData['msg']) ? $jsonData['msg'] : '网络错误';
                $e = $this->generateException($code, $msg, ['response' => $response, 'request' => $this->getRequestMetadata()]);
                call_user_func($callback, null, $e);
                return;
            }

            // 兼容 Java HTTP 接口返回了两层数据, MLGBD
            // array_key_exists 效率较低,但是 isset 不能满足所有场景
            if ($this->type == self::JAVA_TYPE
                && is_array($jsonData['data'])
                && (isset($jsonData['data']['data']) || array_key_exists('data', $jsonData['data']))
                && isset($jsonData['data']['code'])
                && (isset($jsonData['data']['message']) || array_key_exists('message', $jsonData['data']))
            ) {
                // 我......
                if (isset($jsonData['data']['success'])) {
                    // 请保持该条件独立判断
                    if ($jsonData['data']['success'] == false) {
                        $msg = $jsonData['data']['message'];
                        $e = $this->generateException($jsonData['data']['code'], $msg, ['response' => $response, 'request' => $this->getRequestMetadata()]);
                        call_user_func($callback, null, $e);
                        return;
                    }
                } else {
                    $code = $jsonData['data']['code'];
                    if ($code > 0) {
                        $msg = $jsonData['data']['message'];
                        $e = $this->generateException($code, $msg, ['response' => $response, 'request' => $this->getRequestMetadata()]);
                        call_user_func($callback, null, $e);
                        return;
                    }
                }

                $response = $jsonData['data']['data'];
            } else {
                $response = $jsonData['data'];
            }

            call_user_func($callback, $response);
        };
    }

    /**
     * @param $code
     * @param $msg
     * @param null $metaData
     * @return BusinessException|UnexpectedResponseException
     */
    private function generateException($code, $msg, $metaData = null)
    {
        if (BusinessException::isValidCode($code)) {
            $e = new BusinessException($msg, $code);
        } else {
            $e = new UnexpectedResponseException($msg, $code, null, $metaData);
        }
        
        return $e;
    }
    
    private static function getApiConfig($api)
    {
        if (is_null(self::$apiConfig)) {
            $configFile = __DIR__ . '/ApiConfig.php';
            if (!file_exists($configFile)) {
                throw new UnexpectedResponseException('service_host 配置文件不存在');
            }
            $allApiConfig = require $configFile;

            $runMode = RunMode::get();
            if (isset($allApiConfig[$runMode])) {
                self::$apiConfig = $allApiConfig[$runMode];
            } elseif ($runMode == 'pre' && isset($allApiConfig['online'])) {
                self::$apiConfig = $allApiConfig['online'];
            } elseif($runMode == 'pubtest' && isset($allApiConfig['test'])){
                self::$apiConfig = $allApiConfig['test'];
            }else{
                throw new UnexpectedResponseException('service_host 配置文件不完整');
            }
        }

        $pos = stripos ($api, ".");
        if (false === $pos) {
            return false;
        }
        $mod = substr ($api, 0, $pos);
        $target = isset (self::$apiConfig[$mod]) ? self::$apiConfig[$mod] : ['type' => 'local'];
        if (isset($target['sub']) && $target['sub']) {
            $target = static::getSubTarget($target, $api);
        }
        if (!empty($target['host'])) {
            $target['host'] = str_replace('http://', '', $target['host']);
            $hostInfo = explode(':', $target['host']);
        } else {
            $hostInfo = null;
        }

        $port = isset($hostInfo[1]) ? $hostInfo[1] : 80;
        $type = isset($target['type']) ? $target['type'] : 'local';
        if ($type == 'local') {
            $host = 'api.koudaitong.com';
        } else {
            $host = isset($hostInfo[0]) ? str_replace('/', '', $hostInfo[0]) : 'api.koudaitong.com';
        }

        $timeout = isset($target['timeout']) ? $target['timeout'] : 3000;

        return [
            'host' => $host,
            'port' => $port,
            'timeout' => $timeout,
            'type' => $type
        ];

    }

    private static function getSubTarget($target, $path) {
        $sub = $target ['sub'];
        while(true) {
            foreach ( $sub as $item ) {
                if ($item ['mod'] == $path) {
                    return $item;
                }
            }
            $cursor = strrpos ( $path, "." );
            if (!$cursor) {
                break;
            }
            $path = substr ( $path, 0, $cursor );
        }
        return $target;
    }

    private static function filterParams($params, $type)
    {
        if ($type == 'local') {
            $params['debug'] = 'json';
        }

        return $params;
    }

    private function getRequestMetadata() {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'method' => $this->method,
            'params' => $this->params,
            'uri' => $this->uri
        ];
    }
}