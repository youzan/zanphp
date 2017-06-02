<?php

namespace Zan\Framework\Foundation;

use RuntimeException;
use Zan\Framework\Foundation\Booting\InitializeCliInput;
use Zan\Framework\Foundation\Booting\InitializeCache;
use Zan\Framework\Foundation\Booting\LoadFiles;
use Zan\Framework\Foundation\Container\Container;
use Zan\Framework\Foundation\Booting\InitializeSharedObjects;
use Zan\Framework\Foundation\Booting\InitializePathes;
use Zan\Framework\Foundation\Booting\InitializeRunMode;
use Zan\Framework\Foundation\Booting\InitializeDebug;
use Zan\Framework\Foundation\Booting\InitializeEnv;
use Zan\Framework\Foundation\Booting\LoadConfiguration;
use Zan\Framework\Foundation\Booting\RegisterClassAliases;
use Zan\Framework\Utilities\Types\Arr;
use Zan\Framework\Network\Server\Factory as ServerFactory;

class Application
{
    /**
     * The Zan framework version.
     *
     * @var string
     */
    const VERSION = '2.0';

    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * The name for the App.
     *
     * @var string
     */
    protected $appName;

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
     * @var \Zan\Framework\Foundation\Container\Container
     */
    protected $container;

    /**
     * @var \Zan\Framework\Network\Server\ServerBase;
     */
    protected $server;

    /**
     * Create a new Zan application instance.
     *
     * @param string $appName
     * @param  string $basePath
     */
    public function __construct($appName, $basePath)
    {
        $this->appName = $appName;

        static::setInstance($this);

        $this->setBasePath($basePath);

        $this->bootstrap();
    }

    protected function bootstrap()
    {
        $this->setContainer();

        $bootstrapItems = [
            InitializeEnv::class,
            InitializeCliInput::class,
            InitializeRunMode::class,
            InitializeDebug::class,
            InitializePathes::class,
            LoadConfiguration::class,
            InitializeSharedObjects::class,
            RegisterClassAliases::class,
            LoadFiles::class,
            InitializeCache::class,
        ];

        foreach ($bootstrapItems as $bootstrap) {
            $this->make($bootstrap)->bootstrap($this);
        }
    }

    public function make($abstract, array $parameters = [], $shared = false)
    {
        return $this->container->make($abstract, $parameters, $shared);
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
     * Get the app name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->appName;
    }

    /**
     * Get the base path of the App installation.
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
     * Get the app path.
     *
     * @return string
     */
    public function getAppPath()
    {
        return $this->basePath . '/' . 'src';
    }

    /**
     * Get the di
     *
     * @return \Zan\Framework\Foundation\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the di
     *
     * @return $this
     */
    public function setContainer()
    {
        $this->container = new Container();

        return $this;
    }

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * Set the shared instance of the container.
     *
     * @param  \Zan\Framework\Foundation\Application $app
     * @return void
     */
    public static function setInstance($app)
    {
        static::$instance = $app;
    }

    /**
     * Get the application namespace.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getNamespace()
    {
        if (! is_null($this->namespace)) {
            return $this->namespace;
        }

        $composer = json_decode(
            file_get_contents($this->getBasePath().'/'.'composer.json'),
            true
        );

        foreach ((array) Arr::get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath($this->getAppPath()) == realpath($this->getBasePath().'/'.$pathChoice)) {
                    return $this->namespace = $namespace;
                }
            }
        }

        throw new RuntimeException('Unable to detect application namespace.');
    }

    /**
     * get http server.
     *
     * @return \Zan\Framework\Network\Http\Server
     */
    public function createHttpServer()
    {
        $server = $this->getContainer()
            ->make(ServerFactory::class, ['server'])
            ->createHttpServer();

        $this->server = $server;

        return $server;
    }

    /**
     * get tcp server.
     *
     * @return \Zan\Framework\Network\Tcp\Server
     */
    public function createTcpServer()
    {
        $server = $this->getContainer()
            ->make(ServerFactory::class, ['server'])
            ->createTcpServer();

        $this->server = $server;

        return $server;
    }

    /**
     * get websocket server.
     *
     * @return \Zan\Framework\Network\WebSocket\Server
     */
    public function createWebSocketServer()
    {
        $server = $this->getContainer()
            ->make(ServerFactory::class, ['server'])
            ->createWebSocketServer();

        $this->server = $server;

        return $server;
    }

    /**
     * @return \Zan\Framework\Network\Server\ServerBase
     */
    public function getServer()
    {
        return $this->server;
    }
}
