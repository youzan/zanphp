<?php

use Zan\Framework\Utilities\Types\Arr;


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

if (! function_exists('sys_echo')) {
    function sys_echo($context) {
        $workerId = isset($_SERVER["WORKER_ID"]) ? $_SERVER["WORKER_ID"] : "";
        $dataStr = date("Y-m-d H:i:s");
        echo "[$dataStr #$workerId] $context\n";
    }
}

if (! function_exists('sys_error')) {
    function sys_error($context) {
        $workerId = isset($_SERVER["WORKER_ID"]) ? $_SERVER["WORKER_ID"] : "";
        $dataStr = date("Y-m-d H:i:s");
        $context = str_replace("%", "%%", $context);
        fprintf(STDERR, "[$dataStr #$workerId] $context\n");
    }
}

if (! function_exists('echo_exception')) {
    /**
     * @param \Throwable $t
     */
    function echo_exception($t)
    {
        // 兼容PHP7 & PHP5
        if ($t instanceof \Throwable || $t instanceof \Exception) {
            $time = date('Y-m-d H:i:s');
            $class = get_class($t);
            $code = $t->getCode();
            $msg = $t->getMessage();
            $trace = $t->getTraceAsString();
            $workerId = isset($_SERVER["WORKER_ID"]) ? $_SERVER["WORKER_ID"] : -1;
            echo <<<EOF
        
        
###################################################################################
          \033[1;31mGot an exception\033[0m
          worker: #$workerId
          time: $time
          class: $class
          code: $code
          message: $msg
          
$trace
###################################################################################


EOF;
        }
    }
}

if (! function_exists('t2ex')) {
    if (interface_exists("Throwable")) {
        /**
         * @param Throwable $t
         * @return Exception
         */
        function t2ex(\Throwable $t)
        {
            if ($t instanceof \Exception) {
                return $t;
            } else if ($t instanceof \Error) {
                return new \Exception($t->getMessage(), $t->getCode(), $t);
            } else {
                assert(false);
            }
        }
    }
}

if (! function_exists('dd')) {
    function dd()
    {
        var_dump(...func_get_args());
        die;
    }
}

if (! function_exists('d')) {
    function d()
    {
        var_dump(...func_get_args());
    }
}