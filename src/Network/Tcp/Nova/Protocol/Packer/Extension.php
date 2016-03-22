<?php
/**
 * Pack Thrift-bin with extension
 * User: moyo
 * Date: 10/22/15
 * Time: 3:23 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Protocol\Packer;

use Zan\Framework\Network\Tcp\Nova\Protocol\Container;
use Exception as SysException;

class Extension extends Abstracts
{
    /**
     * @var Container
     */
    private $container = null;

    /**
     * Extension constructor.
     */
    protected function constructing()
    {
        $this->container = Container::instance();
    }

    /**
     * @param $type
     * @param $name
     * @param $args
     * @return string
     */
    protected function processEncode($type, $name, $args)
    {
        $input = $this->container->inputObject($args);

        thrift_protocol_write_binary($this->outputBin, $name, $type, $input, $this->seqID, $this->outputBin->isStrictWrite());

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

        $output = $this->container->outputObject($args);
        thrift_protocol_read_binary($this->inputBin, $output, $this->inputBin->isStrictRead());

        // clear buffer (important!!)
        $this->inputBuffer->available() && $this->inputBuffer->read($this->maxPacketSize);

        return $output->export();
    }
}