<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
namespace Zan\Framework\Network\Http\Router;

use Zan\Framework\Foundation\Core\Config;

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
        $routeConfig = Config::get('route');
        $rules = $routeConfig['rewrite'];
        $rules = array_merge($rules, $routeConfig['tiny_url_rules']);
        $this->formatRules($rules);
    }

    public function decode($url)
    {
        foreach($this->rules as $regex => $route){
            if(preg_match($regex,$url,$rows)){
                $parameter = $this->getParameter($rows);
                $route['parameter'] = $parameter;
                return $route;
            }
        }
        return false;
    }

    private function getParameter($rows)
    {
        $ret  = [];
        foreach($rows as $k => $v){
            if(!is_int($k)){
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

    private function formatRules($rules)
    {
        if(!$rules) return false;

        foreach($rules as $regex => $row){
            $regex  = ltrim($regex,'/');
            if(!$regex || !$row){
                continue;
            }
            $data = $this->parseRule($regex,$row);
            $this->rules[$data['regex']] = $data['rule'];
        }
    }

    private function parseRule($regex,$data)
    {
        if(is_array($data)){
            $rule   = $this->getRouteFromConfig($data);
        }else{
            $rule   = ['url'=>$data];
            $data   = null;
        }

        if($data){
            $regex  = $this->parseRegexFromConfig($regex,$data);
        }
        $regex  = $this->parseRegex($regex);
        $regex  = str_replace('/','\/',$regex);
        $regex  = '/^' . $regex . '/i';

        return [
            'regex' => $regex,
            'rule'  => $rule,
        ];
    }

    private function parseRegex($regex)
    {
        if(false === strpos($regex,':')){
            return $regex;
        }

        $pattern    = '/(\/:([^\/]+))/';
        $replace    = '/?(?<${2}>[^/]*)';
        return preg_replace($pattern,$replace,$regex);
    }

    private function parseRegexFromConfig($regex,$data=[])
    {
        if(!$data) return $regex;
        foreach($data as $k => $v){
            $key = ltrim($k,':');
            $data[$k] = '(?<' . $key . '>' . $v . ')';
        }
        return str_replace(array_keys($data),array_values($data),$regex);
    }

    private function getRouteFromConfig(&$data)
    {
        $keys = ['url','module','controller','action','format','parameter'];
        $ret  = [];
        foreach($keys as $key){
            if(isset($data[$key])){
                $ret[$key] = $data[$key];
                unset($data[$key]);
            }
        }
        return $ret;
    }
}
