<?php

namespace Zan\Framework\Foundation\Pagelet\Contracts;

Interface ComponentOutputInterface
{
    /**
     * return html string to the ComponentManagement
     *
     * @return string
     */
    public function getHtml();

    /**
     * return js string to the ComponentManagement
     *
     * @return string
     */
    public function getJs();

    /**
     * return css string to the ComponentManagement
     *
     * @return string
     */
    public function getCss();

    /**
     * return biz data to the ComponentManagement
     *
     * @return json string
     */
    public function getData();
}