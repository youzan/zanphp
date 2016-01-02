<?php

namespace Zan\Framework\Network\Http\Router;

class Regex {

    private static $instance = null;

    private $rules  = [];

    public static function instance()
    {
        if(null === self::$instance){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {

    }

    public function decode($url)
    {

    }

    private function getParameter($rows)
    {

    }

    private function formatRules($rules)
    {

    }

    private function parseRule($regex,$data)
    {

    }

    private function parseRegex($regex)
    {

    }

    private function parseRegexFromConfig($regex,$data=[])
    {

    }

    private function getRouteFromConfig(& $data)
    {

    }
}
