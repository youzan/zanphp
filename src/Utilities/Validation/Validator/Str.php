<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/12/16
 * Time: 22:35
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