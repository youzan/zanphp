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

namespace Zan\Framework\Utilities\Types;


class ObjectArray {

    private $map = [];

    public function push ($object)
    {
        $key = spl_object_hash($object);
        $this->map[$key] = $object;
    }

    public function pop()
    {
        return array_pop($this->map);
    }

    public function remove($object)
    {
        $key = spl_object_hash($object);
        if (!isset($this->map[$key])) {
            return false;
        }
        unset($this->map[$key]);
        return true;
    }

    public function length() {
        return count($this->map);
    }

    public function get($key)
    {
        return isset($this->map[$key]) ? $this->map[$key] : null;
    }

    public function isEmpty()
    {
        return empty($this->map);
    }

}