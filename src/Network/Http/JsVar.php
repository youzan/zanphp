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

    public function getData()
    {
        $return = [];
        $datas = get_class_vars($this->_dataTraffic);
        $config = Config::get($this->_mappingConfig);
        foreach($config as $jsVarKey => $dataTrafficKey) {
            $return[$jsVarKey] = isset($datas[$dataTrafficKey]) ? $datas[$dataTrafficKey] : null;
        }
        return $return;
    }
} 