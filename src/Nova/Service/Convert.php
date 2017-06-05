<?php

namespace Kdt\Iron\Nova\Service;



use Kdt\Iron\Nova\Exception\ProtocolException;

class Convert {

    /**
     * @param $data
     * @param $struct
     * @return array
     * @throws ProtocolException
     *
     * 业务方thrift 协议方法做参数兼容升级, 方法实现必须配置默认值
     * v1 func(arg1, arg2) 升级 v2 func(arg1, arg2, arg3)
     * func服务的实现方法: func(arg1, arg2, arg3 = default_value)
     */
    public static function argsToArray($data, $struct)
    {
        $pack = [];
        $maybeDefault = true;

        foreach (array_reverse($struct, true) as $config) {
            if (isset($data[$config['var']])) {
                $pack[] = $data[$config['var']];
                $maybeDefault = false;
            } else {
                if (!$maybeDefault) {
                    throw new ProtocolException("Missing Nova argument or argument is null");
                }
            }
        }

        return array_reverse($pack);
    }
}