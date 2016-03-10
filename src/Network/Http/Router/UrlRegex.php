<?php
/**
 * @author hupp
 * create date: 16/03/10
 */
namespace Zan\Framework\Network\Http\Router;

class UrlRegex {

    public static function formatRules($rules = [])
    {
        if (!$rules) return false;

        $regexRules = [];
        foreach ($rules as $regex => $realUrl) {
            $regex = ltrim($regex, '/');
            if (!$regex || !$realUrl){
                continue;
            }
            $result = self::parseRule($regex, $realUrl);
            $regexRules[$result['regex']] = $result['rule'];
        }
        return $regexRules;
    }

    private static function parseRule($regex, $realUrl)
    {
        $regex  = self::parseRegex($regex);
        $regex  = str_replace('/','\/',$regex);
        $regex  = '/^' . $regex . '/i';

        return [
            'regex' => $regex,
            'rule'  => [
                'url' => $realUrl
            ],
        ];
    }

    private static function parseRegex($regex)
    {
        if (false === strpos($regex, ':')){
            return $regex;
        }
        $pattern    = '/(\/:([^\/]+))/';
        $replace    = '/?(?<${2}>[^/]*)';

        return preg_replace($pattern, $replace, $regex);
    }

    public static function decode($url, $rules = [])
    {
        if (!$rules) return false;

        foreach ($rules as $regex => $route) {
            if (preg_match($regex, $url, $matching)) {
                $parameter = self::getParameter($matching);
                $route['parameter'] = $parameter;
                return $route;
            }
        }
        return false;
    }

    private static function getParameter($matching)
    {
        $ret = [];
        foreach ($matching as $key => $value) {
            if (!is_int($key))
                $ret[$key] = $value;
        }
        return $ret;
    }


}
