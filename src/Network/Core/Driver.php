<?php
namespace Zan\Framework\Network\Core;

interface Driver
{
    function run($setting);

    function send($client_id, $data);

    function close($client_id);

    function setProtocol($protocol);
}