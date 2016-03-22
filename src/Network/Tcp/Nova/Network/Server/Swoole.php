<?php
/**
 * Server for swoole
 * User: moyo
 * Date: 12/2/15
 * Time: 4:48 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Network\Server;

use Zan\Framework\Foundation\Core\Config;

use swoole_server as SwooleServer;

use Zan\Framework\Network\Tcp\Nova\Transport\Server as TransportServer;
use Zan\Framework\Network\Tcp\Nova\Exception\ProtocolException;
use Exception as SysException;

class Swoole
{
    /**
     * @var SwooleServer
     */
    private $instance = null;

    /**
     * @var bool
     */
    private $verboseMode = false;

    /**
     * @var string
     */
    private $serverHost = '0.0.0.0';

    /**
     * @var int
     */
    private $serverPort = 0;

    /**
     * @var string
     */
    private $swooleConfKey = 'nova.swoole.server';

    /**
     * @var string
     */
    private $attachmentContent = '{}';

    /**
     * @var string
     */
    private $processorExceptionB64 = 'gAEAAwAAABBzZXJ2ZXIucHJvY2Vzc29yAAAAAAsAAQAAABpzZXJ2ZXIucHJvY2Vzc29yLmV4Y2VwdGlvbggAAgAAAAAA';

    /**
     * @var string
     */
    private $processTitlePrefix = 'php-nova: ';

    /**
     * @param $verboseMode
     * @param $serverConfig
     * @param $platformConfig
     */
    public function startup($verboseMode, $serverConfig, $platformConfig)
    {
        $this->verboseMode = $verboseMode;
        $this->serverHost = $serverConfig['host'];
        $this->serverPort = $serverConfig['port'];

        $this->instance = new SwooleServer($this->serverHost, $this->serverPort);

        $this->instance->set(Config::get($this->swooleConfKey));
        $this->instance->nova_config($platformConfig);

        $this->instance->on('start', [$this, 'processStart']);
        $this->instance->on('workerStart', [$this, 'processWorkerStart']);
        $this->instance->on('workerStop', [$this, 'processWorkerStop']);
        $this->instance->on('workerError', [$this, 'processWorkerError']);
        $this->instance->on('managerStart', [$this, 'processManagerStart']);
        $this->instance->on('managerStop', [$this, 'processManagerStop']);
        $this->instance->on('receive', [$this, 'processServiceRequest']);
        $this->instance->on('close', [$this, 'processConnClose']);
        $this->instance->on('shutdown', [$this, 'processShutdown']);

        $this->instance->start();
    }

    /**
     * @param SwooleServer $server
     */
    public function processStart(SwooleServer $server)
    {
        $listen = $this->serverHost.':'.$this->serverPort;
        $this->setProcessName('master process ('.$listen.')');
        $this->logging('server starting', ['listen' => $listen, 'pid' => $server->master_pid]);
    }

    /**
     * @param SwooleServer $server
     */
    public function processShutdown(SwooleServer $server)
    {
        $this->logging('server closed', ['pid' => $server->master_pid]);
    }

    /**
     * @param SwooleServer $server
     */
    public function processManagerStart(SwooleServer $server)
    {
        $this->setProcessName('manager process');
        $this->logging('manager started', ['server' => $server->master_pid, 'pid' => $server->manager_pid]);
    }

    /**
     * @param SwooleServer $server
     */
    public function processManagerStop(SwooleServer $server)
    {
        $this->logging('manager stopped', ['server' => $server->master_pid, 'pid' => $server->manager_pid]);
    }

    /**
     * @param SwooleServer $server
     * @param $worker_id
     */
    public function processWorkerStart(SwooleServer $server, $worker_id)
    {
        $this->setProcessName('worker # '.$worker_id);
        if (extension_loaded('opcache'))
        {
            opcache_reset();
        }
        $this->logging('worker started', ['server' => $server->master_pid, 'id' => $server->worker_id, 'pid' => $server->worker_pid]);
    }

    /**
     * @param SwooleServer $server
     * @param $worker_id
     */
    public function processWorkerStop(SwooleServer $server, $worker_id)
    {
        $this->logging('worker stopped', ['server' => $server->master_pid, 'id' => $server->worker_id, 'pid' => $server->worker_pid]);
    }

    /**
     * @param SwooleServer $server
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     */
    public function processWorkerError(SwooleServer $server, $worker_id, $worker_pid, $exit_code)
    {
        $this->logging('worker error', ['server' => $server->master_pid, 'id' => $worker_id, 'pid' => $worker_pid, 'code' => $exit_code]);
    }

    /**
     * process service-requesting
     * @param SwooleServer $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    public function processServiceRequest(SwooleServer $server, $fd, $from_id, $data)
    {
        $mstBegin = $mstFinish = null;

        if ($this->verboseMode)
        {
            $mstBegin = microtime(true);
        }

        $serviceName = $methodName = $remoteIP = $remotePort = $seqNo = $novaData = $attachData = $execResult = $outputBuffer = $sendState = $reqState = null;

        try
        {
            // TODO tmp add is_admin
            Config::set('is_admin', false);
            // TODO tmp add is _admin
            if (nova_decode($data, $serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $attachData, $novaData))
            {
                // TODO tmp add is_admin
                if (substr($attachData, 0, 1) == '{')
                {
                    $json = json_decode($attachData, true);
                    if (is_array($json) && isset($json['op_is_admin']) && $json['op_is_admin'])
                    {
                        Config::set('is_admin', true);
                    }
                }
                // TODO tmp add is_admin
                $execResult = TransportServer::instance()->handle($serviceName, $methodName, $novaData);
            }
            else
            {
                throw new ProtocolException('nova.decoding.failed ~[server:'.strlen($data).']');
            }
        }
        catch (SysException $e)
        {
            if ($seqNo)
            {
                // default exception bin
                $execResult = base64_decode($this->processorExceptionB64);
            }
        }

        if (is_null($execResult))
        {
            // exec failed && no send
            $sendState = false;
        }
        else
        {
            // encoding && sending
            if (nova_encode($serviceName, $methodName, $remoteIP, $remotePort, $seqNo, $this->attachmentContent, $execResult, $outputBuffer))
            {
                $sendState = $server->send($fd, $outputBuffer);
            }
        }

        if ($sendState)
        {
            $reqState = 'OK';
        }
        {
            $reqState = 'ER';
            $server->close($fd);
        }

        if ($this->verboseMode)
        {
            $mstFinish = microtime(true);
            $mstUse = (string)round($mstFinish - $mstBegin, 3) . 's';

            $this->logging('new-req', ['FROM' => long2ip($remoteIP).':'.$remotePort, 'SEQ' => $seqNo, 'URI' => $serviceName.'::'.$methodName, 'IO' => strlen($novaData).':'.strlen($execResult), 'ET' => $mstUse, 'STA' => $reqState]);
        }
    }

    /**
     * @param SwooleServer $server
     * @param $fd
     * @param $from_id
     */
    public function processConnClose(SwooleServer $server, $fd, $from_id)
    {
        $this->logging('conn closed', ['server' => $server->master_pid, 'conn' => $from_id, 'fd' => $fd]);
    }

    /**
     * @param $title
     */
    private function setProcessName($title)
    {
        if (strtolower(substr(php_uname('s'), 0, 6)) === 'darwin')
        {
            // ignore it under osx
        }
        else
        {
            if (function_exists('cli_set_process_title'))
            {
                cli_set_process_title($this->processTitlePrefix.$title);
            }
            else
            {
                swoole_set_process_name($this->processTitlePrefix.$title);
            }
        }
    }

    /**
     * @param $msg
     * @param array $args
     */
    private function logging($msg, $args = [])
    {
        $buffer = [];
        array_walk($args, function ($val, $key) use (&$buffer) { $buffer[] = $key.'='.$val; });
        echo sprintf('[%s]<||>%s ~ %s', date('Y-m-d H:i:s'), $msg, implode('|', $buffer)), "\n";
    }
}