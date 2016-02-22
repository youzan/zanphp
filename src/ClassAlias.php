<?php

return [
//    'Config'          => '',
//    'Cache'           => '',
//    'Db'              => '',
//    'Kv'              => '',
//    'Queue'           => '',
//    'HttpClient'      => '',
      'Zan'             => 'Zan\\Framework\\Zan',
      'Config'          => 'Zan\\Framework\\Foundation\\Core\\Config',
      'Handler'         => 'Zan\\Framework\\Foundation\\Exception\\Handler',
      'HttpServer'      => 'Zan\\Framework\\Network\\Http\\Server',
      'HttpApplication' => 'Zan\\Framework\\Network\\Http\\Application',
      'TcpServer'       => 'Zan\\Framework\\Network\\Tcp\\Server',
      'TcpApplication'  => 'Zan\\Framework\\Network\\Tcp\\Application',
];