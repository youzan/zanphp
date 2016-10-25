<?php
/**
 * Created by PhpStorm.
 * User: asher
 * Date: 16/3/9
 * Time: 下午2:37
 */

namespace Zan\Framework\Foundation\View\Pagelet\Component;


use Zan\Framework\Foundation\View\Pagelet\Contracts\ComponentInterface;

abstract class ComponentAbstract implements ComponentInterface
{
    /**
     * @param $key
     */
    abstract public function __construct($key);

    /**
     * @param $extraData
     * @return ComponentInterface;
     */
    abstract public function view(array $extraData);

    /**
     * @return ComponentInterface;
     */
    abstract public function edit();

    /**
     * @param $data
     * @return bool
     */
    abstract public function save(array $data);

    /**
     * @return string
     */
    abstract public function getType();
}


