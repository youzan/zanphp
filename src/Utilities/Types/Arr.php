<?php

namespace Zan\Framework\Utilities\Types;

use ArrayAccess;
use Closure;

class Arr {
    public static function join(array $before, array $after) {
        if(empty($before) ) {
            return $after;
        }

        if(empty($after) ) {
            return $before;
        }

        foreach($after as $row) {
            $before[] = $row;
        }

        return $before;
    }

    public static function sortByArray(array $arr, array $sort, $withNotExists=false)
    {
        if(!$sort) return $arr;
        if(!$arr) return [];

        $ret = [];
        $notExist = [];
        $map = array_flip($arr);

        foreach($sort as $item){
            if(isset($map[$item])){
                $ret[] = $item;
                unset($map[$item]);
            }else{
                $notExist[] = $item;
            }
        }

        if(!empty($map)){
            $ret = Arr::join($ret, array_keys($map));
        }

        if(false === $withNotExists){
            return $ret;
        }

        return [
            'result' => $ret,
            'notExist' => $notExist
        ];
    }

    public static function merge()
    {
        $total = func_num_args();
        if(0 === $total){
            return [];
        }

        $result = func_get_arg(0);
        for ($i = 1; $i < $total; $i++) {
            foreach (func_get_arg($i) as $key => $val) {
                if (!isset($result[$key])) {
                    $result[$key] = $val;
                    continue;
                }

                if (is_array($val) && is_array($result[$key])) {
                    $result[$key] = Arr::merge($result[$key], $val);
                } else {
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }

    public static function mapKey(array $array, $col){
        $cols = self::array_cols($array,$col);
        if(empty($cols)){
            return array();
        }
        return array_combine($cols,$array);
    }

    /**
     * ======================================
     *  input:
     * --------------------------------------
     *  $arr = array(
     *      [ key1 => 1 , key2 => 2],
     *      [ key1 => 3 , key2 => 4],
     *      [ key1 => 5 , key2 => 6],
     *  );
     *  $col = key1;
     * ======================================
     * output
     * --------------------------------------
     * array( 1,3,5 );
     *
     */
    public static function array_cols($arr,$col,$default=false,$is_unique = false)
    {
        if(empty($arr)){
            return array();
        }

        $ret    = array();

        foreach($arr as $row){
            if(isset($row[$col])){
                $ret[] = $row[$col];
            }elseif(false !== $default){
                $ret[] = $default;
            }
        }
        if($is_unique){
            $ret = array_unique($ret);
        }
        return $ret;
    }

    /**
     * @param array $list
     * @param $value
     * @return array
     *
     * @example Arr::createTreeByList(['a','b','c'],1);
     * @output  ['a' => [ 'b' => [ 'c' => 1 ] ] ]
     */
    public static function createTreeByList(array $list, $value){
        if(empty($list)){
            return $value;
        }

        $map = [];
        $first = array_shift($list);
        $map[$first] = self::createTreeByList($list, $value);

        return $map;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  array   $array
     * @param  string  $prepend
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, static::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists($array, $key)
    {
        if (is_array($array)) {
            return array_key_exists($key, $array);
        }

        return $array->offsetExists($key);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        $arr = explode('.', $key);
        foreach ($arr as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default instanceof Closure ? $default() : $default;
            }
        }

        return $array;
    }

    /**
     * Check if an item exists in an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @return bool
     */
    public static function has($array, $key)
    {
        if (empty($array) || is_null($key)) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if ((is_array($array) && array_key_exists($segment, $array))
                || ($array instanceof ArrayAccess && $array->offsetExists($segment))) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Filter the array using the given callback.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     */
    public static function where($array, callable $callback)
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return array
     */
    public static function except($array, $keys)
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    public static function only(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Return the values from a single column in the input array
     *
     * @link http://www.php.net/manual/en/function.array-column.php
     *
     * @param array $input
     * @param mixed $columnKey
     * @param mixed $indexKey
     *
     * @return array Returns an array of values representing a single column from the input array.
     */
    public static function column(array $input, $columnKey, $indexKey = null)
    {
        $resultArray = array();

        foreach ($input as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($indexKey !== null && array_key_exists($indexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$indexKey];
            }

            if ($columnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($columnKey, $row)) {
                $valueSet = true;
                $value = $row[$columnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }
    
    /**
     * Return the values from a columns in the input array
     *
     * @param array $input
     * @param array $columnKeys
     * @param mixed $indexKey
     *
     * @return array Returns an array of values representing a columns from the input array.
     */
    public static function columns(array $input, $columnKeys, $indexKey = null)
    {
        $resultArray = array();

        foreach ($input as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($indexKey !== null && array_key_exists($indexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$indexKey];
            }

            if (!is_array($columnKeys) || empty($columnKeys)) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row)) {
                $valueSet = true;
                $value = self::only($row, $columnKeys);
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }

    public static function arraySort($arr, $col, $sort = 'desc', $default = false)
    {
        $cols = static::array_cols($arr, $col, $default);
        $sort = 'desc' === strtolower($sort) ? SORT_DESC : SORT_ASC;
        array_multisort($cols, $sort, $arr);
        return $arr;
    }
}
