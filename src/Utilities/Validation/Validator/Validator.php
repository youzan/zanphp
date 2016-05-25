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


use Zan\Framework\Foundation\Exception\System\InvalidArgument;

class Validator {
    protected $object = null;
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function validate(array $rules)
    {
        if(!$rules) {
            throw new InvalidArgument('empty rules for Validator.validate()');
        }

        foreach($rules as $rule) {
            $response = $this->matchRule($rule);
            if($response) {
                continue;
            }

            $msg = isset($rule[2]) ? $rule[2] : '';
            return new ErrorMessage($msg, $this->object);
        }
        return true;
    }

    protected function matchRule($rule)
    {
        $this->validateRule($rule);
        $method = $rule[0];
        $parameter = isset($rule[1]) ? $rule[1] : null;

        if(!method_exists($this, $method)) {
            throw new InvalidArgument('class '. __CLASS__ .' has no such method:' . $method);
        }

        return call_user_func_array([$this, $method], $parameter);
    }

    protected function validateRule($rule)
    {
        if(!is_array($rule) || empty($rule)) {
            throw new InvalidArgument('invalid rule for Validator.validateRule()');
        }

        return true;
    }

    protected function validateNumber($num)
    {
        if(!is_int($num)) {
            throw new InvalidArgument('Invalid Validator parameter:' . $num);
        }
    }
}