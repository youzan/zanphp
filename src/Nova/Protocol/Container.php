<?php

namespace Kdt\Iron\Nova\Protocol;

use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Protocol\Container\Input;
use Kdt\Iron\Nova\Protocol\Container\Output;
use Thrift\Exception\TApplicationException;

class Container
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @param $spec
     * @param $object
     * @return Input
     */
    public function inputObject($spec, $object = null)
    {
        if (is_array($spec))
        {
            // assign input
            if (is_null($object))
            {
                $input = Input::newInstance();
                $input->setTSPEC($spec);
            }
            else
            {
                $input = $object;
            }
            // filling spec
            foreach ($spec as $struct)
            {
                if (isset($struct['value']))
                {
                    if (is_object($struct['value']) && method_exists($struct['value'], 'getStructSpec'))
                    {
                        $input->$struct['var'] = $this->inputObject($struct['value']->getStructSpec(), $struct['value']);
                    }
                    else
                    {
                        $input->$struct['var'] = $struct['value'];
                    }
                }
                else if (is_object($object) && property_exists($object, $struct['var']))
                {
                    $input->$struct['var'] = $object->$struct['var'];
                }
                else
                {
                    $input->$struct['var'] = null;
                }
            }
            return $input;
        }
        else if (is_object($spec) && ($spec instanceof TApplicationException))
        {
            // ignore when request is thrift-exception
            return $spec;
        }
        else
        {
            return $spec;
        }
    }

    /**
     * @param $spec
     * @return Output
     */
    public function outputObject($spec)
    {
        $output = Output::newInstance();
        $output->setTSPEC($spec);
        return $output;
    }
}