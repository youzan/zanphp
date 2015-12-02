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
}