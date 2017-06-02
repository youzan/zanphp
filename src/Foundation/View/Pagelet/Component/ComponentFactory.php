<?php

namespace Zan\Framework\Foundation\Pagelet\Component;

use Zan\Framework\Foundation\Pagelet\Component\ComponentAbstract;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class ComponentFactory
{
    /**
     * component modules namespace prefix
     */
    const COMPONENT_NAMESPACE_PREFIX = 'Kdt\Component';

    const COMPONENT_DIR_NAME_PREFIX = 'Yz_';

    /**
     * @var
     */
    private static $_instance;

    private static function _construct() {}

    public static function getInstance()
    {
        if(!self::$_instance) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    /**
     * @param $componentGroup
     * @param $componentType
     * @param $componentKey
     * @return mixed
     * @throws \Exception_Msg
     */
    public function create($componentGroup, $componentType, $componentKey)
    {
        $componentObjName = $this->_getComponentObjName($componentGroup, $componentType);
        if(!class_exists($componentObjName)) {
            throw new InvalidArgumentException(50000, "group:{$componentGroup} type:{$componentType} view component class not existed");
        }
        $componentObj = new $componentObjName($componentKey);
        if(!$componentObj instanceof ComponentAbstract) {
            throw new InvalidArgumentException(50000, "group:{$componentGroup} type:{$componentType} view component not instanceof ComponentAbstract");
        }
        return $componentObj;
    }

    /**
     * get the component name
     *
     * @param $componentGroup
     * @param $componentType
     *
     * @return string
     */
    private function _getComponentObjName($componentGroup, $componentType)
    {
        return $this->_getComponentNamespace($componentGroup, $componentType) . '\\' . ucfirst($componentType);
    }

    /**
     * @param $componentGroup
     * @param $componentType
     * @return string
     */
    private function _getComponentNamespace($componentGroup, $componentType)
    {
        return self::COMPONENT_NAMESPACE_PREFIX . '\\' . ucfirst(strtolower($componentGroup)) . '\\' . self::COMPONENT_DIR_NAME_PREFIX . ucfirst(strtolower($componentType));
    }
}

