<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/8
 * Time: 下午9:47
 */

namespace Zan\Framework\Network\Http;

use Zan\Framework\Network\Http\DataTraffic;
use Zan\Framework\Foundation\Core\Config;

class JsVar
{
    private $_mappingConfig;

    private $_dataTraffic;

    public function __construct(DataTraffic $dataTraffic, $jsDataMappingConfig = 'common.jsDataMapping.default')
    {
        $this->_mappingConfig = $jsDataMappingConfig;
        $this->_dataTraffic = $dataTraffic;
    }

    /**
     * @param array $jsDataMapping 仅仅开给测试的口子，日常开发不要传
     * @return array
     */
    public function getData(array $jsDataMapping = [])
    {
        $return = [];
        $dataSet = get_object_vars($this->_dataTraffic);
        $config = empty($jsDataMapping) ? Config::get($this->_mappingConfig) : $jsDataMapping;
        foreach($config as $parentJsVarKey => $mapping) {
            foreach($mapping as $childJsVarKey => $dataTrafficKey) {
                $return[$parentJsVarKey][$childJsVarKey] = isset($dataSet[$dataTrafficKey]) ? $dataSet[$dataTrafficKey] : null;
            }
        }
        return $return;
    }
} 