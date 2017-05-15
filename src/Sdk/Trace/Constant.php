<?php
/**
 * Created by IntelliJ IDEA.
 * User: Demon
 * Date: 16/6/3
 * Time: 下午3:56
 */

namespace Zan\Framework\Sdk\Trace;


class Constant
{
    const SUCCESS = '0';

    /******************** TYPE ******************/
    const NOVA = "Nova";
    const NOVA_CLIENT = 'Nova.Client';
    const NOVA_SERVER = 'Nova.Server';
    const HTTP = "Http";
    const REMOTE_CALL = "RemoteCall";
    const SQL = "SQL";
    const REDIS = "Redis";
    const HTTP_CALL = "HttpCall";
    const NSQ_PUB = 'nsq.pub';

    /******************Event Type ******************/
    const NOVA_PROCCESS = 'Nova.Proccess.Event';
    const POST = "POST";
    const GET = "GET";
}