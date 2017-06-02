<?php
namespace Zan\Framework\Network\Http\Routing;

class UrlRegex {

    public static function formatRules($rules = [])
    {
        if (!$rules) return false;
        $regexRules = [];
        foreach ($rules as $regex => $realUrl) {
            $regex = ltrim($regex, '/');
            if(!$regex or !$realUrl) {
                continue;
            }
            $result = self::parseRule($regex, $realUrl);
            $regexRules[$result['regex']] = $result['url'];
        }
        return $regexRules;
    }

    private static function parseRule($regex, $realUrl)
    {
        $regex  = self::parseRegex($regex);
        $regex  = str_replace('/','\/',$regex);
        $regex  = '#' . $regex . '#i';
        return [
            'regex' => $regex,
            'url'  => $realUrl
        ];
    }

    private static function parseRegex($regex)
    {
        if(false === strpos($regex, ':') and false === strpos($regex, '.*')) {
            return $regex;
        }
        $pattern = [
            '/(\/:([^\/]+))/',
            '/(\/\.\*\/\?)/'
        ];
        $replace = [
            '/?(?<${2}>[^/]*)',
            '/.*/'
        ];
        return preg_replace($pattern, $replace, $regex);
    }

    public static function decode($url, $rules = [])
    {
        $return = [
            'url' => $url,
            'parameter' => [],
        ];
        if (!$rules) return $return;
        foreach ($rules as $regex => $route) {
            if (preg_match($regex, $url, $matching)) {
                $parameter = self::getParameter($matching);
                $return = [
                    'url' => $route,
                    'parameter' => $parameter
                ];
                break;
            }
        }
        return self::parseDecodeResult($return);
    }

    private static function parseDecodeResult($result)
    {
        if(count($result['parameter']) <= 0 or false === strpos($result['url'], '${')) {
            return $result;
        }
        $tmp = [];
        foreach($result['parameter'] as $key => $value) {
            $find = '${' . $key . '}';
            if(false !== strpos($result['url'], $find)) {
                $tmp['${' . $key . '}'] = $value;
                unset($result['parameter'][$key]);
            }
        }
        $result['url'] = str_replace(array_keys($tmp),array_values($tmp), $result['url']);
        return $result;
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
