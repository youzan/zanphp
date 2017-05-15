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
use Zan\Framework\Utilities\Types\Arr;

//TODO move to Arr
if (! function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param  mixed  $target
     * @param  string|array  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (! Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (! Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        }

        return $target;
    }
}

if (! function_exists('echo_exception')) {
    function echo_exception(\Exception $e)
    {
        $code = $e->getCode();
        $msg = $e->getMessage();
        $trace = $e->getTraceAsString();

        echo <<<EOF
        
        
###################################################################################
          \033[1;31mGot an exception\033[0m
          code: $code
          message: $msg
          
$trace
###################################################################################


EOF;
    }
}

if (! function_exists('dd')) {
    function dd()
    {
        if (func_num_args() === 0) {
            return;
        }

        // Get all passed variables
        $variables = func_get_args();
        var_dump($variables);
        die;
    }
}

if (! function_exists('d')) {
    function d()
    {
        if (func_num_args() === 0) {
            return;
        }

        // Get all passed variables
        $variables = func_get_args();
        var_dump($variables);
    }
}