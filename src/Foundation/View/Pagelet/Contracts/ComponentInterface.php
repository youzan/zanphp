<?php

namespace Zan\Framework\Foundation\Pagelet\Contracts;

interface ComponentInterface
{
    /**
     * component construct
     * @param $key
     */
    public function __construct($key);

    /**
     * component view mode
     *
     * @param $extraData
     *
     * @return ComponentOutput
     */
    public function view(array $extraData);

    /**
     * component edit mode
     *
     * @return ComponentOutput
     */
    public function edit();

    /**
     * component save itself
     *
     * @param $data
     *
     * @return boolean
     */
    public function save(array $data);

    /**
     * get the component type
     *
     * @return string
     */
    public function getType();
}