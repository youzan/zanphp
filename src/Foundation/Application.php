<?php

namespace Zan\Framework\Foundation;

use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Booting\InitializeSharedObjects;
use Zan\Framework\Foundation\Booting\LoadConfiguration;
use Zan\Framework\Foundation\Booting\RegisterClassAliases;

class Application
{
    /**
     * The Zan framework version.
     *
     * @var string
     */
    const VERSION = '1.0';

    /**
     * The base path for the App installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The application namespace.
     *
     * @var string
     */
    protected $namespace = null;


    /**
     * @var \Zan\Framework\Foundation\Container\Di
     */
    protected $di;

    /**
     * Create a new Zan application instance.
     *
     * @param  string|null  $basePath
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->bootstrap();
    }

    protected function bootstrap()
    {
        $this->setDi();

        $bootstrapItems = [
            LoadConfiguration::class,
            InitializeSharedObjects::class,
            RegisterClassAliases::class,
        ];

        foreach ($bootstrapItems as $bootstrap) {
            $this->make($bootstrap)->bootstrap($this);
        }
    }

    public function make($abstract, array $parameters = [], $shared = false)
    {
        return $this->di->make($abstract, $parameters, $shared);
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Set the base path for the application.
     *
     * @param  string  $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        return $this;
    }

    /**
     * Get the di
     *
     * @return \Zan\Framework\Foundation\Container\Di
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * Set the di
     *
     * @return $this
     */
    public function setDi()
    {
        $this->di = Di::getInstance();

        return $this;
    }
}