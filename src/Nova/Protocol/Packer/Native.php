<?php

namespace Kdt\Iron\Nova\Protocol\Packer;

use Kdt\Iron\Nova\Foundation\Protocol\TException as BizException;
use Kdt\Iron\Nova\Service\StructValidator;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TProtocolException;
use Thrift\Type\TMessageType;
use Thrift\Type\TType;

class Native extends Abstracts
{
    /**
     * @var string
     */
    private $getSpecFunc = 'getStructSpec';

    /**
     * for encode
     * @var array
     */
    private $rCallbacks = [];

    /**
     * for decode
     * @var array
     */
    private $wCallbacks = [];

    /**
     * Native constructor.
     */
    protected function constructing()
    {
        parent::constructing();

        $this->genCallbacks();
    }

    /**
     * @param $type
     * @param $name
     * @param $args
     * @param $side
     * @return string
     */
    protected function processEncode($type, $name, $args, $side)
    {
        $this->clearOutputBuffer();

        $this->outputBin->writeMessageBegin($name, $type, $this->seqID);
        if (is_object($args) && $args instanceof TApplicationException)
        {
            $args->write($this->outputBin);
        }
        else
        {
            StructValidator::validateOutput($args, $side);
            $this->structWrite($args);
        }
        $this->outputBin->writeMessageEnd();
        $this->outputTrans->flush();
        return $this->outputBuffer->read($this->maxPacketSize);
    }

    /**
     * @param $data
     * @param $args
     * @param $side
     * @return array
     * @throws TApplicationException
     */
    protected function processDecode($data, $args, $side)
    {
        $this->clearInputBuffer();
        $this->inputBuffer->write($data);

        $rSeqID = 0;
        $fName = null;
        $mType = 0;

        $this->inputBin->readMessageBegin($fName, $mType, $rSeqID);

        if ($mType == TMessageType::EXCEPTION)
        {
            $x = new TApplicationException();
            $x->read($this->inputBin);
            $this->inputBin->readMessageEnd();
            throw $x;
        }

        $this->structRead($args);

        $this->inputBin->readMessageEnd();

        $values = [];

        foreach ($args as $arg)
        {
            if ($arg['type'] == TType::STRUCT && isset($arg['value']) && $arg['value'] instanceof BizException)
            {
                throw $arg['value'];
            }
            else if (isset($arg['value']) && $arg['value'] !== null)
            {
                $values[$arg['var']] = $arg['value'];
            }
        }

        StructValidator::validateInput($values, $args, $side);

        return $values;
    }

    /**
     * @param $args
     * @return int
     */
    private function structWrite($args)
    {
        $xfer = 0;
        $xfer += $this->outputBin->writeStructBegin(get_class($this));

        foreach ($args as $key => $arg)
        {
            if ($arg['value'] !== null)
            {
                $this->writeArgByType($key, $arg, $xfer);
            }
        }

        $xfer += $this->outputBin->writeFieldStop();
        $xfer += $this->outputBin->writeStructEnd();

        return $xfer;
    }

    /**
     * @param $key
     * @param $item
     * @param $xfer
     */
    private function writeArgByType($key, $item, &$xfer)
    {
        $type = $item['type'];

        if (isset($this->rCallbacks[$type]))
        {
            $xfer += $this->outputBin->writeFieldBegin($item['var'], $type, $key);
            $this->rCallbacks[$type]($xfer, $item);
            $xfer += $this->outputBin->writeFieldEnd();
        }
        else
        {
            throw new \InvalidArgumentException('Invalid type:' . $type);
        }
    }

    /**
     * @param $args
     * @return int
     * @throws TProtocolException
     */
    private function structRead(&$args)
    {
        $xfer = 0;
        $fName = null;
        $fType = 0;
        $fid = 0;
        $xfer += $this->inputBin->readStructBegin($fName);

        while (true)
        {
            $xfer += $this->inputBin->readFieldBegin($fName, $fType, $fid);

            if ($fType == TType::STOP)
            {
                break;
            }

            if (isset($args[$fid]))
            {
                if (isset($args[$fid]["type"])) {
                    $expectType = $args[$fid]["type"];
                    $readType = $fType;
                    if ($expectType != $readType) {
                        $t1 = $this->getTType($expectType);
                        $t2 = $this->getTType($fType);
                        throw new TProtocolException("Nova Decode Fail: expected $t1 but got $t2");
                    }
                }
                $this->setArg($args[$fid], $fType, $xfer);
            }
            else
            {
                $xfer += $this->inputBin->skip($fType);
                break;
            }

            $xfer += $this->inputBin->readFieldEnd();
        }

        $xfer += $this->inputBin->readStructEnd();

        return $xfer;
    }

    /**
     * @param $item
     * @param $fType
     * @param $xfer
     * @throws TProtocolException
     */
    private function setArg(&$item, $fType, &$xfer)
    {
        $type = $item['type'];
        $item['value'] = null;

        if (isset($this->wCallbacks[$type]))
        {
            $this->wCallbacks[$type]($item, $xfer);
        }
        else
        {
            $xfer += $this->inputBin->skip($fType);
        }
    }

    /**
     * gen RW callbacks
     */
    private function genCallbacks()
    {
        if (empty($this->rCallbacks))
        {
            $this->rCallbacks = [
                TType::BOOL =>
                    function (&$xfer, $item)
                    {
                        $xfer += $this->outputBin->writeBool($item['value']);
                    },
                TType::BYTE =>
                    function (&$xfer, $item)
                    {
                        $xfer += $this->outputBin->writeByte($item['value']);
                    },
                TType::DOUBLE =>
                    function (&$xfer, $item)
                    {
                        $xfer += $this->outputBin->writeDouble($item['value']);
                    },
                TType::I16 =>
                    function (&$xfer, $item)
                    {
                        $xfer += $this->outputBin->writeI16($item['value']);
                    },
                TType::I32 =>
                    function (&$xfer, $item)
                    {
                        $xfer += $this->outputBin->writeI32($item['value']);
                    },
                TType::I64 =>
                    function (&$xfer, $item)
                    {
                        $xfer += $this->outputBin->writeI64($item['value']);
                    },
                TType::STRING =>
                    function (&$xfer, $item)
                    {
                        $xfer += $this->outputBin->writeString($item['value']);
                    },
                TType::STRUCT =>
                    function (&$xfer, $item)
                    {
                        if (is_object($item['value']) && method_exists($item['value'], $this->getSpecFunc))
                        {
                            $subArgs = call_user_func([$item['value'], $this->getSpecFunc]);
                            foreach ($subArgs as &$subArg)
                            {
                                $subArg['value'] = $item['value']->{$subArg['var']};
                            }
                            $xfer += $this->structWrite($subArgs);
                        }
                        else
                        {
                            throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
                        }
                    },
                TType::MAP =>
                    function (&$xfer, $items)
                    {
                        if (!is_array($items['value']))
                        {
                            throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
                        }

                        // map filter null values
                        $items['value'] = array_filter($items['value'], function($v) {
                            return $v !== null;
                        });

                        $this->outputBin->writeMapBegin($items['ktype'], $items['vtype'], count($items['value']));

                        foreach ($items['key'] as $ki => $keyType)
                        {
                            $valSpec = $items['val'];
                            $valType = $valSpec[$ki];
                            foreach ($items['value'] as $key => $val)
                            {
                                $this->rCallbacks[$keyType]($xfer, array('value' => $key));
                                $this->rCallbacks[$valType]($xfer, array_merge($valSpec, array('value' => $val)));
                            }
                        }
                        $this->outputBin->writeMapEnd();
                        $xfer += $this->outputBin->writeFieldEnd();
                    },
                TType::LST =>
                    function (&$xfer, $items)
                    {
                        if (!is_array($items['value']))
                        {
                            throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
                        }

                        // list/set filter null values
                        $items['value'] = array_filter($items['value'], function($v) {
                            return $v !== null;
                        });

                        $this->outputBin->writeListBegin($items['etype'], count($items['value']));

                        $valSpec = $items['elem'];
                        foreach ($items['value'] as $item)
                        {
                            $this->rCallbacks[$valSpec['type']]($xfer, array_merge($valSpec, array('value' => $item)));
                        }
                        $this->outputBin->writeListEnd();
                        $xfer += $this->outputBin->writeFieldEnd();
                    }
            ];

            $this->rCallbacks[TType::SET] = $this->rCallbacks[TType::LST];
        }

        if (empty($this->wCallbacks))
        {
            $this->wCallbacks = [
                TType::BOOL =>
                    function (&$item, &$xfer)
                    {
                        $xfer += $this->inputBin->readBool($item['value']);
                    },
                TType::BYTE =>
                    function (&$item, &$xfer)
                    {
                        $xfer += $this->inputBin->readByte($item['value']);
                    },
                TType::DOUBLE =>
                    function (&$item, &$xfer)
                    {
                        $xfer += $this->inputBin->readDouble($item['value']);
                    },
                TType::I16 =>
                    function (&$item, &$xfer)
                    {
                        $xfer += $this->inputBin->readI16($item['value']);
                    },
                TType::I32 =>
                    function (&$item, &$xfer)
                    {
                        $xfer += $this->inputBin->readI32($item['value']);
                    },
                TType::I64 =>
                    function (&$item, &$xfer)
                    {
                        $xfer += $this->inputBin->readI64($item['value']);
                    },
                TType::STRING =>
                    function (&$item, &$xfer)
                    {
                        $xfer += $this->inputBin->readString($item['value']);
                    },
                TType::STRUCT =>
                    function (&$item, &$xfer)
                    {
                        $class = $item['class'];

                        $item['value'] = new $class();

                        if (method_exists($item['value'], $this->getSpecFunc))
                        {
                            $subArgs = call_user_func([$item['value'], $this->getSpecFunc]);
                            $xfer += $this->structRead($subArgs);

                            foreach ($subArgs as $subArg)
                            {
                                if (isset($subArg['value']))
                                {
                                    $item['value']->{$subArg['var']} = $subArg['value'];
                                }
                            }
                        }
                        else
                        {
                            throw new \InvalidArgumentException('Invalid argument: ' . $class);
                        }

                    },
                TType::MAP =>
                    function (&$items, &$xfer)
                    {
                        $kType = $items['ktype'];
                        $vType = $items['vtype'];
                        $vSize = 0;

                        $this->inputBin->readMapBegin($kType, $vType, $vSize);

                        $mapResult = [];

                        $keySpec = $items['key'];
                        $valSpec = $items['val'];

                        for ($vi = 0; $vi < $vSize; $vi ++)
                        {
                            $this->wCallbacks[$kType]($keySpec, $xfer);
                            $this->wCallbacks[$vType]($valSpec, $xfer);
                            $mapKey = $keySpec['value'];
                            is_int($mapKey) || $mapKey = (string)$mapKey;
                            $mapResult[$mapKey] = $valSpec['value'];
                        }

                        $this->inputBin->readMapEnd();
                        $xfer += $this->inputBin->readFieldEnd();

                        $items['value'] = $mapResult;
                    },
                TType::LST =>
                    function (&$items, &$xfer)
                    {
                        $items['value'] = array();
                        $size = 0;
                        $eType = 0;
                        $xfer += $this->inputBin->readListBegin($eType, $size);

                        for ($i = 0; $i < $size; ++$i)
                        {
                            $item = $items['elem'] + array('value' => null);
                            $this->wCallbacks[$items['elem']['type']]($item, $xfer);
                            $items['value'][] = $item['value'];
                        }
                        $xfer += $this->inputBin->readListEnd();
                    }
            ];

            $this->wCallbacks[TType::SET] = $this->wCallbacks[TType::LST];
        }
    }

    private function getTType($type)
    {
        static $cache = [];
        if (empty($cache)) {
            $clazz = new \ReflectionClass(TType::class);
            $cache = array_flip($clazz->getConstants());
        }
        return isset($cache[$type]) ? $cache[$type] : "UNKNOWN";
    }
}