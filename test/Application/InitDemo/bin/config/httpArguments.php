<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/1
 * Time: 23:43
 */

$arguments = [
    'command'           => [
        'description'   => "server command(start|stop|reload)",
    ],
    'verbose'           => [
        'prefix'        => 'v',
        'longPrefix'    => 'verbose',
        'description'   => 'Verbose output',
        'noValue'       => true,
    ],
    'runmode'           => [
        'prefix'        => 'r',
        'longPrefix'    => 'run-mode',
        'description'   => 'application run mode',
    ],

];

return $arguments;