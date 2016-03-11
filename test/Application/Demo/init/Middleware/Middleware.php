<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/8
 * Time: 23:04
 */


use Zan\Framework\Network\Contract\Request;
use Zan\Framework\Network\Contract\Response;
use Zan\Framework\Utilities\DesignPattern\Context;

interface RequestFilter{
    /**
     * @param Request $request
     * @param Context $context
     * @return Response|null;
     */
    public function doFilter(Request $request, Context $context);
}

interface RequestTerminater{
    /**
     * @param Request $request
     * @param Response $response
     * @param Context $context
     * @return void
     */
    public function terminate(Request $request, Response $response, Context $context);
}



class Middleware {
    public function group($groupMap)
    {

    }

    public function match($pattern, $group)
    {

    }
}

$middlewareConfig = [
    'group' => [
        'web1'   => [
            'xxx','\\ddd\\xx'
        ],
        'web2'   => [
            'xxx','\\ddd\\xx','xxxxx'
        ],
        'auth'  => [],
        'xxx'   => ['ac'],
    ],
    'alias'     => [
        'acl'   => '\\com\\youxan\\acl'
    ],
    'pattern'    =>  [
        '/'         => 'web',
        '/trade'    => 'web',
    ],
];


//Middleware::match('/', 'web');

