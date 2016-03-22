<?php
/**
 * Pack Thrift-bin with php code
 * User: moyo
 * Date: 10/22/15
 * Time: 3:22 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Protocol\Packer;

use Zan\Framework\Network\Tcp\Nova\Service\ExceptionPacket;
use Zan\Framework\Network\Tcp\Nova\Foundation\Protocol\TException as BizException;
use Thrift\Exception\TApplicationException;
use Thrift\Exception\TProtocolException;
use Thrift\Type\TMessageType;
use Thrift\Type\TType;
use Exception as SysException;

class Native extends Abstracts
{
    /**
     * @var string
     */
    private $getSpecFunc = 'getStructSpec';

    /**
     * @param $type
     * @param $name
     * @param $args
     * @return string
     */
    protected function processEncode($type, $name, $args)
    {
        $this->outputBin->writeMessageBegin($name, $type, $this->seqID);
        if (is_object($args) && $args instanceof TApplicationException)
        {
            $args->write($this->outputBin);
        }
        else
        {
            $this->structWrite($args);
        }
        $this->outputBin->writeMessageEnd();
        $this->outputTrans->flush();
        return $this->outputBuffer->read($this->maxPacketSize);
    }

    /**
     * @param $data
     * @param $args
     * @return array
     * @throws SysException
     */
    public function processDecode($data, $args)
    {
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

        // clear buffer (important!!)
        $this->inputBuffer->available() && $this->inputBuffer->read($this->maxPacketSize);

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
        $output = $this->outputBin;
        $type = $item['type'];

        $callbacks = [
            TType::BOOL =>
                function (&$xfer, $item) use ($output)
                {
                    $xfer += $output->writeBool($item['value']);
                },
            TType::BYTE =>
                function (&$xfer, $item) use ($output)
                {
                    $xfer += $output->writeByte($item['value']);
                },
            TType::DOUBLE =>
                function (&$xfer, $item) use ($output)
                {
                    $xfer += $output->writeDouble($item['value']);
                },
            TType::I16 =>
                function (&$xfer, $item) use ($output)
                {
                    $xfer += $output->writeI16($item['value']);
                },
            TType::I32 =>
                function (&$xfer, $item) use ($output)
                {
                    $xfer += $output->writeI32($item['value']);
                },
            TType::I64 =>
                function (&$xfer, $item) use ($output)
                {
                    $xfer += $output->writeI64($item['value']);
                },
            TType::STRING =>
                function (&$xfer, $item) use ($output)
                {
                    $xfer += $output->writeString($item['value']);
                },
            TType::STRUCT =>
                function (&$xfer, $item) use ($output)
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
        ];

        $callbacks[TType::MAP] = function (&$xfer, $items) use ($output, $callbacks)
        {
            if (!is_array($items['value']))
            {
                throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
            }
            $output->writeMapBegin($items['ktype'], $items['vtype'], count($items['value']));

            foreach ($items['key'] as $ki => $keyType)
            {
                $valType = $items['val'][$ki];
                foreach ($items['value'] as $key => $val)
                {
                    $callbacks[$keyType]($xfer, array('value' => $key));
                    $callbacks[$valType]($xfer, array('value' => $val));
                }
            }
            $output->writeMapEnd();
            $xfer += $output->writeFieldEnd();
        };

        $callbacks[TType::LST] = function (&$xfer, $items) use ($output, $callbacks)
        {
            if (!is_array($items['value']))
            {
                throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
            }
            $output->writeListBegin($items['etype'], count($items['value']));

            foreach ($items['value'] as $item)
            {
                $callbacks[$items['elem']['type']]($xfer, array('value' => $item));
            }
            $output->writeListEnd();
            $xfer += $output->writeFieldEnd();
        };

        $callbacks[TType::SET] = $callbacks[TType::LST];

        if (isset($callbacks[$type]))
        {
            $xfer += $output->writeFieldBegin($item['var'], $type, $key);
            $callbacks[$type]($xfer, $item);
            $xfer += $output->writeFieldEnd();
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
        $input = $this->inputBin;
        $type = $item['type'];
        $item['value'] = null;

        $callbacks = [
            TType::BOOL =>
                function (&$item, &$xfer) use ($input)
                {
                    $xfer += $input->readBool($item['value']);
                },
            TType::BYTE =>
                function (&$item, &$xfer) use ($input)
                {
                    $xfer += $input->readByte($item['value']);
                },
            TType::DOUBLE =>
                function (&$item, &$xfer) use ($input)
                {
                    $xfer += $input->readDouble($item['value']);
                },
            TType::I16 =>
                function (&$item, &$xfer) use ($input)
                {
                    $xfer += $input->readI16($item['value']);
                },
            TType::I32 =>
                function (&$item, &$xfer) use ($input)
                {
                    $xfer += $input->readI32($item['value']);
                },
            TType::I64 =>
                function (&$item, &$xfer) use ($input)
                {
                    $xfer += $input->readI64($item['value']);
                },
            TType::STRING =>
                function (&$item, &$xfer) use ($input)
                {
                    $xfer += $input->readString($item['value']);
                },
            TType::STRUCT =>
                function (&$item, &$xfer) use ($input)
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

                }
        ];

        $callbacks[TType::MAP] = function (&$items, &$xfer) use ($input, $callbacks)
        {
            $kType = $items['ktype'];
            $vType = $items['vtype'];
            $vSize = 0;

            $input->readMapBegin($kType, $vType, $vSize);

            $mapResult = [];

            $keySpec = $items['key'];
            $valSpec = $items['val'];

            for ($vi = 0; $vi < $vSize; $vi ++)
            {
                $callbacks[$kType]($keySpec, $xfer);
                $callbacks[$vType]($valSpec, $xfer);
                $mapKey = $keySpec['value'];
                is_int($mapKey) || $mapKey = (string)$mapKey;
                $mapResult[$mapKey] = $valSpec['value'];
            }

            $input->readMapEnd();
            $xfer += $input->readFieldEnd();

            $items['value'] = $mapResult;
        };

        $callbacks[TType::LST] = function (&$items, &$xfer) use ($input, $callbacks)
        {
            $items['value'] = array();
            $size = 0;
            $eType = 0;
            $xfer += $input->readListBegin($eType, $size);

            for ($i = 0; $i < $size; ++$i)
            {
                $item = $items['elem'] + array('value' => null);
                $callbacks[$items['elem']['type']]($item, $xfer);
                $items['value'][] = $item['value'];
            }
            $xfer += $input->readListEnd();
        };

        $callbacks[TType::SET] = $callbacks[TType::LST];

        if (isset($callbacks[$type]))
        {
            $callbacks[$type]($item, $xfer);
        }
        else
        {
            $xfer += $input->skip($fType);
        }
    }
}