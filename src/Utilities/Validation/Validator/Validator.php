<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 22:10
 */

namespace Zan\Framework\Utilities\Validation\Validator;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Validator {
    protected $object = null;
    public function __construct($object)
    {
        $this->object = $object;
    }

    public function validate(array $rules)
    {
        if(!$rules) {
            throw new InvalidArgumentException('empty rules for Validator.validate()');
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
            throw new InvalidArgumentException('class '. __CLASS__ .' has no such method:' . $method);
        }

        return call_user_func_array([$this, $method], $parameter);
    }

    protected function validateRule($rule)
    {
        if(!is_array($rule) || empty($rule)) {
            throw new InvalidArgumentException('invalid rule for Validator.validateRule()');
        }

        return true;
    }

    protected function validateNumber($num)
    {
        if(!is_int($num)) {
            throw new InvalidArgumentException('Invalid Validator parameter:' . $num);
        }
    }
}