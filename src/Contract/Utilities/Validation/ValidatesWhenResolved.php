<?php

namespace Zan\Framework\Contract\Utilities\Validation;

interface ValidatesWhenResolved
{
    /**
     * Validate the given class instance.
     *
     * @return void
     */
    public function validate();
}
