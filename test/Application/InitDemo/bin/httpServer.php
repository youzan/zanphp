<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/1
 * Time: 20:45
 */

require __DIR__ . '/../../../../' . 'vendor/autoload.php';
require __DIR__ . '/../../../../' . 'src/Zan.php';
require __DIR__ . '/config/httpArguments.php';
require __DIR__ . '/../init/bootstrap.php';

$cli = new \League\CLImate\CLImate();
$cli->arguments->add($arguments);
$cli->arguments->parse();
\Zan\Framework\Network\Server\Registry::set('cli', $cli);

var_dump($cli->arguments->get('verbose'));exit;

Zan\Framework\Zan::createHttpApplication($appConfig)
        ->init()
        ->run();



