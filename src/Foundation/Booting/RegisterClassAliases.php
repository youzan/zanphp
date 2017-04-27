<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Contract\Foundation\Bootable;
use Zan\Framework\Foundation\Application;

class RegisterClassAliases implements Bootable
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        $this->registerClassAliasByMap($this->getClassAliasesMap());

        $this->registerClassAliasByPath($this->getClassAliasPathes());
    }

    private function registerClassAliasByMap(array $classAliasMap)
    {
        foreach ($classAliasMap as $alias => $original) {
            class_alias($original, $alias);
        }
    }

    private function registerClassAliasByPath(array $classAliasPathes)
    {

    }

    /**
     * @todo 共享配置+自定义配置化
     *
     * @return array
     */
    private function getClassAliasesMap()
    {
        return [
            'Config'          => 'Zan\\Framework\\Foundation\\Core\\Config',
            'HttpServer'      => 'Zan\\Framework\\Network\\Http\\Server',
            'TcpServer'       => 'Zan\\Framework\\Network\\Tcp\\Server',
            'Url'             => 'Zan\\Framework\\Utilities\\Types\\URL',
            'Log'             => 'Zan\\Framework\\Sdk\\Log\\Log',
        ];
    }

    /**
     * @todo 共享配置+自定义配置化
     *
     * @return array
     */
    private function getClassAliasPathes()
    {
        return [
            'Foundation/Contract',
            'Foundation/Core',
            'Foundation/Domain',
            'Utilities/Types',
        ];
    }
}
