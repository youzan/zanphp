<?php

namespace Zan\Framework\Foundation\Console;

use Zan\Framework\Foundation\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Zan\Framework\Foundation\Core\Config;

class Console extends SymfonyApplication
{
    protected $commands = [];

    /**
     * The Zan application instance.
     *
     * @var \Zan\Framework\Foundation\Application
     */
    protected $zanApp;

    /**
     * Create a new Artisan console application.
     *
     * @param Application $app
     * @param  string $version
     */
    public function __construct(Application $app, $version = 'UNKNOWN')
    {
        parent::__construct($app->getName(), $version);
        
        $this->zanApp = $app;
        $this->setAutoExit(false);
        //$this->setCatchExceptions(false);

        $this->registerCommands();
    }

    /**
     * Add a command to the console.
     *
     * @param  \Symfony\Component\Console\Command\Command  $command
     * @return \Symfony\Component\Console\Command\Command
     */
    public function add(SymfonyCommand $command)
    {
        if ($command instanceof Command) {
            $command->setZanApp($this->zanApp);
        }

        return parent::add($command);
    }

    public function registerCommands()
    {
        $commands = Config::get('console.commands');

        foreach ($commands as $alias => $className) {
            $this->commands[$alias] = $className;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (isset($this->commands[$name])) {
            if (!is_object($this->commands[$name])) {
                $this->add(
                    $this->zanApp->make($this->commands[$name])
                );
            }
        }

        return parent::get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function all($namespace = null, $resolve = false)
    {
        if ($resolve) {
            foreach ($this->commands as $name => $command) {
                if (!is_object($command)) {
                    $this->commands[$name] = $this->add(
                        $this->zanApp->make($this->commands[$name])
                    );
                }
            }
        }

        return parent::all($namespace);
    }
    
    /**
     * Get the default input definitions for the applications.
     *
     * This is used to add the --env option to every available command.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption($this->getEnvironmentOption());

        return $definition;
    }

    /**
     * Get the global environment option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getEnvironmentOption()
    {
        $message = 'The environment the command should run under.';

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * Get the Zan application instance.
     *
     * @return \Zan\Framework\Foundation\Application
     */
    public function getZanApp()
    {
        return $this->zanApp;
    }

}