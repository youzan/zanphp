<?php
namespace Zan\Framework\Utilities\Types;

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

        $result = [];
        for ($i = 0; $i < $total; $i++) {
            foreach (func_get_arg($i) as $key => $val) {
                if (isset($result[$key])) {
                    if (is_array($val)) {
                        $result[$key] = Arr::merge($result[$key], $val);
                    } elseif (is_int($key)){
                        $result[$key] = $val;
                    } else {
                        $result[$key] = $val;
                    }
                } else {
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }
}