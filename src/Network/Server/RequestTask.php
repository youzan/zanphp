<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/13
 * Time: 21:15
 */

namespace Zan\Framework\Network\Server;


use Zan\Framework\Contract\Network\Request;
use Zan\Framework\Utilities\DesignPattern\Context;

class RequestTask {

    /**
     * @var Request
     */
    private $request;
    /**
     * @var Context
     */
    private $context;

    /**
     * @param Request $request
     * @param Context $context
     */
    public function __construct(Request $request, Context $context)
    {

        $this->request = $request;
        $this->context = $context;
    }


    /**
     * @return \Zan\Framework\Contract\Network\Response
     */
    public function run()
    {

    }
}