<?php

namespace Zan\Framework\Utilities\Validation;

use Zan\Framework\Contract\Utilities\Validation\UnauthorizedException;
use Zan\Framework\Contract\Utilities\Validation\ValidationException as ContractValidationException;

/**
 * Provides default implementation of ValidatesWhenResolved contract.
 */
trait ValidatesWhenResolvedTrait
{
    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validate()
    {
        $instance = $this->getValidatorInstance();

        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        } elseif (! $instance->passes()) {
            $this->failedValidation($instance);
        }
    }

    /**
     * Get the validator instance for the request.
     *
     * @return \Zan\Framework\Utilities\Validation\Validator
     */
    protected function getValidatorInstance()
    {
        return $this->validator();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Zan\Framework\Utilities\Validation\Validator $validator
     *
     * @return mixed
     * @throws \Zan\Framework\Contract\Utilities\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ContractValidationException($validator);
    }

    /**
     * Determine if the request passes the authorization check.
     *
     * @return bool
     */
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->authorize();
        }

        return true;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws \Zan\Framework\Contract\Utilities\Validation\UnauthorizedException
     */
    protected function failedAuthorization()
    {
        throw new UnauthorizedException;
    }
}
