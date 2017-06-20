<?php

namespace Zan\Framework\Foundation\Core;


class MultiConfig
{

    public static function init()
    {
        $config = Config::get('multi');
        if (empty($config)){
            return;
        }

        $currentUser =  get_current_user();
        if (empty($config[$currentUser])) {
            return;
        }
        $config = $config[$currentUser];

        $serviceConfig = Config::get('server');

        if (isset($config['host'])) {
            $serviceConfig['host'] = $config['host'];
        }
        if (isset($config['port'])) {
            $serviceConfig['port'] = $config['port'];
        }
        if (isset($config['worker_num'])) {
            $serviceConfig['config']['worker_num'] = $config['worker_num'];
        }

        Config::set('server',$serviceConfig);
    }

}