<?php

namespace Zan\Framework\Sdk\Log\Track;

class LoggerTCPSender {
    private static $_instance;
    private $socket;
    private $host = "192.168.66.204";
    private $backup = "10.200.175.94";
    private $port = 5140;
    private $reconnected = false;

    private function __construct() {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $connected = socket_connect($this->socket, $this->host, $this->port);
        if (! $connected) {
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            $this->reconnected = socket_connect($this->socket, $this->host, $this->port);
        }
    }

    private function __clone() {}

    public function __destruct() {
        yield socket_close($this->socket);
    }

    public static function getInstance() {
        if (! self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function send($log) {
        if ($this->reconnected) {
            $this->reconnected = false;
            $logger = LoggerFactory::get("php-framework", "logger_tcp_sender");
            $logger->error("flume not avalible");
        }
        $log = $log."\n";
        yield socket_write($this->socket, $log);
    }
}