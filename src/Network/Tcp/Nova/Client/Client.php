<?php
/**
 * Transport for client
 * User: moyo
 * Date: 9/11/15
 * Time: 1:39 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Client;

use Zan\Framework\Network\Tcp\Nova\Network\Network;
use Zan\Framework\Network\Tcp\Nova\Protocol\Packer;
use Zan\Framework\Network\Tcp\Nova\Service\Convert;
use Zan\Framework\Network\Tcp\Nova\Service\Finder;
use Thrift\Type\TMessageType;

class Client
{
    /**
     * @var string
     */
    private $serviceName = '';

    /**
     * @var Packer
     */
    private $packer = null;

    /**
     * @var Network
     */
    private $network = null;

    /**
     * @var Finder
     */
    private $finder = null;

    /**
     * @var Convert
     */
    private $convert = null;

    /**
     * Client constructor.
     * @param $serviceName
     */
    public function __construct($serviceName)
    {
        $this->serviceName = $serviceName;
        $this->packer = Packer::newInstance();
        $this->network = Network::instance();
        $this->finder = Finder::instance();
        $this->convert = Convert::instance();
    }

    /**
     * @param $method
     * @param $inputArguments
     * @param $outputStruct
     * @param $exceptionStruct
     * @return mixed
     */
    public function call($method, $inputArguments, $outputStruct, $exceptionStruct)
    {
        $response = $this->packer->decode(
            $this->network->request(
                $this->serviceName,
                $method,
                $this->packer->encode(TMessageType::CALL, $method, $inputArguments)
            ),
            $this->packer->struct($outputStruct, $exceptionStruct)
        );
        return isset($response[$this->packer->successKey]) ? $response[$this->packer->successKey] : null;
    }
}