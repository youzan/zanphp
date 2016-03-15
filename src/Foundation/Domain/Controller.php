<?php

namespace Zan\Framework\Foundation\Domain;

use Zan\Framework\Network\Http\Request;
use Zan\Framework\Network\Http\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

class Controller {

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response;
     */
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->respones = $response;
    }

    public function display()
    {

    }

    public function assign()
    {

    }

    public function r($code=0, $msg='', $data=[])
    {
        //$format = $this->request['format'];
        $format = 'json';

        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ];
        if ($format === 'json' || $format === 'jsonp') {
            $result = json_encode($result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        }
        if ($format === 'jsonp') {

            $queryParams = $this->request->getQueryParams();
            $callback = isset($queryParams['callback']) ? trim($queryParams['callback']) : 'callback';

            $result = $callback . '('.$result. ')';

            if (!preg_match('/[0-9A-Za-z]+/i', $callback)) {
                $result = 'Are you hacking!';
            }
        }
        $this->output($result);
    }


    public function output($data)
    {
        $this->respones->setData($data);
        $this->respones->send();
    }

}
