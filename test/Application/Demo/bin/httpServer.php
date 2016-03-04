<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/1
 * Time: 20:45
 */

use \Zan\Framework\Utilities\DesignPattern\Registry;
use \Zan\Framework\Zan;


require __DIR__ . '/../../../../' . 'vendor/autoload.php';
require __DIR__ . '/config/httpArguments.php';
require __DIR__ . '/../init/bootstrap.php';

$cli = new \League\CLImate\CLImate();
$cli->arguments->add($arguments);
$cli->arguments->parse();
Registry::set('cli', $cli);

Zan::createHttpApplication($appConfig)->run();



