<?php

namespace Zan\Framework\Foundation\Booting;

use Zan\Framework\Foundation\Application;

class RegisterClassAliases
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Zan\Framework\Foundation\Application  $app
     */
    public function bootstrap(Application $app)
    {
        $this->registerClassAliasByMap($this->getClassAliasesMap());

        $this->registerClassAliasByPath($this->getClassAliasPathes());
    }

    private function registerClassAliasByMap(array $classAliasMap)
    {
        foreach($classAliasMap as $alias => $original) {
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
            'Zan'             => 'Zan\\Framework\\Zan',
            'UnitTest'        => 'Zan\\Framework\\Foundation\\Test\\UnitTest',
            'Config'          => 'Zan\\Framework\\Foundation\\Core\\Config',
            'Handler'         => 'Zan\\Framework\\Foundation\\Exception\\Handler',
            'HttpServer'      => 'Zan\\Framework\\Network\\Http\\Server',
            'HttpApplication' => 'Zan\\Framework\\Network\\Http\\Application',
            'TcpServer'       => 'Zan\\Framework\\Network\\Tcp\\Server',
            'TcpApplication'  => 'Zan\\Framework\\Network\\Tcp\\Application',
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