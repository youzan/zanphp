<?php
/**
 * Pack Thrift-bin with extension
 * User: moyo
 * Date: 10/22/15
 * Time: 3:23 PM
 */

namespace Kdt\Iron\Nova\Protocol\Packer;

use Kdt\Iron\Nova\Protocol\Container;
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
     * @param $side
     * @return string
     */
    protected function processEncode($type, $name, $args, $side)
    {
        $input = $this->container->inputObject($args);

        thrift_protocol_write_binary($this->outputBin, $name, $type, $input, $this->seqID, $this->outputBin->isStrictWrite());

        return $this->outputBuffer->read($this->maxPacketSize);
    }

    /**
     * @param $data
     * @param $args
     * @param $side
     * @return array
     */
    public function processDecode($data, $args, $side)
    {
        $this->inputBuffer->write($data);

        $output = $this->container->outputObject($args);
        thrift_protocol_read_binary($this->inputBin, $output, $this->inputBin->isStrictRead());

        // clear buffer (important!!)
        $this->inputBuffer->available() && $this->inputBuffer->read($this->maxPacketSize);

        return $output->export();
    }
}