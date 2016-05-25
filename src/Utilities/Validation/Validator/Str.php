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

namespace Zan\Framework\Utilities\Validation\Validator;

class Str extends Validator {
    private $length = null;

    public function minLength($minLength)
    {
        $this->validateNumber($minLength);
        $length = $this->getLength();
        if($length >= $minLength) {
            return true;
        }
        return false;
    }

    public function maxLength($maxLength)
    {
        $this->validateNumber($maxLength);
        $length = $this->getLength();
        if($length <= $maxLength) {
            return true;
        }
        return false;
    }

    public function regex($regex)
    {
        return false;
    }

    private function getLength()
    {
        if(null !== $this->length) {
            return $this->length;
        }

        $this->length = strlen($this->object);
    }
}