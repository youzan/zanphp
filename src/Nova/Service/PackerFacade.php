<?php

namespace Kdt\Iron\Nova\Service;



use Kdt\Iron\Nova\Exception\NovaException;
use Kdt\Iron\Nova\Foundation\Traits\InstanceManager;
use Kdt\Iron\Nova\Foundation\TSpecification;
use Kdt\Iron\Nova\Protocol\Packer;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TProtocolException;
use Thrift\Type\TMessageType;

class PackerFacade {
    use InstanceManager;

    public function decodeServiceArgs($serviceName, $methodName, $binArgs, $side)
    {
        /* @var $spec TSpecification */
        $spec = $this->getSpecClass($serviceName);
        if (!$spec) {
            throw new NovaException("no such serviceName spec");
        }
        $inputStruct = $spec->getInputStructSpec($methodName);

        /* @var $packer Packer */
        $packer = Packer::getInstance();
        $args = $packer->decode($binArgs,$inputStruct, $side);
        $args = Convert::argsToArray($args, $inputStruct);

        return $args;
    }

    public function encodeServiceOutput($serviceName, $methodName, $output, $side)
    {
        /* @var $spec TSpecification */
        $spec = $this->getSpecClass($serviceName);
        if (!$spec) {
            throw new NovaException("no such serviceName");
        }

        $outputStruct = $spec->getOutputStructSpec($methodName);
        
        $response = $this->parseNullResult($output);
        $withNullExceptions = null !== $response['output'] ? false : true;
        $exceptionStruct = $spec->getExceptionStructSpec($methodName, $withNullExceptions);

        /* @var $packer Packer */
        $packer = Packer::getInstance();
        $package = $packer->struct($outputStruct, $exceptionStruct, $response['output'], $response['exception']);

        return $packer->encode(TMessageType::REPLY, $methodName, $package, $side);
    }
    
    protected function parseNullResult($output)
    {
        return [
            'output' => $output,
            'exception' => null
        ];
    }

    /**
     * @param string $serviceName
     * @param string $methodName
     * @param \Exception $exceptions
     * @return mixed
     */
    public function encodeServiceException($serviceName, $methodName, $exceptions, $side)
    {
        /* @var $exceptions \Exception */
        /* @var $packer Packer */

        $packer = Packer::getInstance();

        $tApplicationMsg = $tApplicationCode = null;
        $tApplicationMethod = '';

        do {
            if (!$serviceName || !$methodName) {
                $tApplicationCode = TApplicationException::PROTOCOL_ERROR;
                $tApplicationMsg = $exceptions->getMessage();
                break;
            }

            $tApplicationMethod = $methodName;
            /* @var $spec TSpecification */
            $spec = $this->getSpecClass($serviceName);
            if (!$spec) {
                $tApplicationCode = TApplicationException::INTERNAL_ERROR;
                $tApplicationMsg = "No such service spec";
                break;
            }

            $outputStruct = $spec->getOutputStructSpec($methodName);
            if (!$outputStruct) {
                $tApplicationCode = TApplicationException::WRONG_METHOD_NAME;
                $tApplicationMsg = "No such method output";
                break;
            }

            $exceptionStruct = $spec->getExceptionStructSpec($methodName);

            if ($this->isBizException($exceptions, $exceptionStruct)) {
                $package = $packer->struct($outputStruct, $exceptionStruct, null, $exceptions);
            } else {
                $tApplicationCode = TApplicationException::UNKNOWN;
                $tApplicationMsg = $exceptions->getMessage();
                break;
            }
            //biz exception
            return $packer->encode(TMessageType::REPLY, $methodName, $package, $side);
        } while(0);

        $hex = $this->encodeProtocolHex($exceptions);
        if ($hex !== false) {
            $tApplicationMsg .= $hex;
        }

        //application exception
        $e = new TApplicationException($tApplicationMsg, $tApplicationCode);
        return $packer->encode(TMessageType::EXCEPTION, $tApplicationMethod, $e, $side);
    }


    private function getSpecClass($serviceName)
    {
        $spec = ClassMap::getInstance()->getSpec($serviceName);
        if(!$spec) {
            return null;
        }

        return $spec;
    }

    private function isBizException($e, $exceptionStruct)
    {
        $bizExceptions = [];

        if (empty($exceptionStruct)) {
            return false;
        }
        
        foreach ($exceptionStruct as $bizException) {
            $bizExceptions[] = ltrim($bizException['class'], '\\');
        }
        
        return in_array(ltrim(get_class($e), '\\'), $bizExceptions)
                    ? true : false;
    }

    private function encodeProtocolHex($ex)
    {
        $addPrefix = function($v) { return "0x$v"; };

        if ($ex instanceof TProtocolException) {

            $backtrace = $ex->getTrace();
            foreach ($backtrace as $frame) {

                if (isset($frame["class"]) && $frame["class"] === Packer::class
                    &&
                    isset($frame["function"]) && $frame["function"] === "encode"
                ) {
                    $raw = serialize($frame["args"]);
                    $hex = implode(" ", array_map($addPrefix, str_split(bin2hex($raw), 2)));
                    return " [type=encode, raw=$hex]";
                }

                if (isset($frame["class"]) && $frame["class"] === Packer::class
                    &&
                    isset($frame["function"]) && $frame["function"] === "decode"
                ) {
                    $raw = $frame["args"][0];
                    $hex = implode(" ", array_map($addPrefix, str_split(bin2hex($raw), 2)));
                    return " [type=decode, raw=$hex]";
                }

            }
        }

        return false;
    }

}