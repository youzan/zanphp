<?php

namespace Zan\Framework\Utilities\Validation;

use Exception;

class ValidationException extends Exception
{
    /**
     * The validator instance.
     *
     * @var \Zan\Framework\Utilities\Validation\Validator
     */
    public $validator;

    /**
     * The recommended response to send to the client.
     *
     * @var \Illuminate\Http\Response|null
     */
    public $response;

    /**
     * Create a new exception instance.
     *
     * @param  \Zan\Framework\Utilities\Validation\Validator  $validator
     * @param  \Illuminate\Http\Response  $response
     */
    public function __construct($validator, $response = null)
    {
        parent::__construct('The given data failed to pass validation.');

        $this->response = $response;
        $this->validator = $validator;
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
