<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/1
 * Time: 22:05
 */

require __DIR__ . '/../../../../' . 'src/Test.php';

$cli = new \League\CLImate\CLImate();

$cli->arguments->add([
    'command'   => [
        'description'   => 'command',
    ],
    'help'      => [
        'longPrefix'  => 'help',
        'description' => 'Prints a usage statement',
        'noValue'     => true,
    ],
    'verbose' => [
        'prefix'      => 'v',
        'longPrefix'  => 'verbose',
        'description' => 'Verbose output',
        'noValue'     => true,
    ],
]);

$cli->arguments->parse();
$command = $cli->arguments->get('command');
var_dump($command);