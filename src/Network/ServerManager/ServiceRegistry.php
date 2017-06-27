<?php

namespace Zan\Framework\Network\ServerManager;

/**
 * Interface ServiceRegistry
 * @package Zan\Framework\Network\ServerManager
 *
 * TODO 重构服务注册订阅
 * 1. etcd registry
 * 2. apcu registry
 * 3. no discovery
 * 4. service chain
 */
interface ServiceRegistry
{
    public function register($config);

    public function refreshing($config);

    public function watch();

    public function lookup();
}