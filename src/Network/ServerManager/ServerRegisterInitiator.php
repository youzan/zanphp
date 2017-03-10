<?php
/**
 * Created by PhpStorm.
 * User: xiaoniu
 * Date: 16/6/6
 * Time: 上午10:41
 */
namespace Zan\Framework\Network\ServerManager;

use Com\Youzan\Nova\Framework\Generic\Servicespecification\GenericService;
use Kdt\Iron\Nova\Service\Registry;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Network\Common\HttpClient;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\ServerManager\ServerRegister;
use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Core\Config;
use Kdt\Iron\Nova\Nova;

class ServerRegisterInitiator
{
    use Singleton;

    CONST ENABLE_REGISTER = 1;
    CONST DISABLE_REGISTER = 0;

    private $register;

    public function enableRegister()
    {
        $this->register = self::ENABLE_REGISTER;
    }

    public function disableRegister()
    {
        $this->register = self::DISABLE_REGISTER;
    }

    public function getRegister()
    {
        return $this->register;
    }

    public function init()
    {
        $keys = Nova::getEtcdKeyList();
        foreach ($keys as list($protocol, $domain, $appName)) {
            $this->doRegisterOneGroup($protocol, $domain, $appName);
        }

        $this->doReportSwooleVer();
    }

    private function doRegisterOneGroup($protocol, $domain, $appName)
    {
        $config = [];
        $config["services"] = Nova::getAvailableService($protocol, $domain, $appName);
        $config["domain"] = $domain;
        $config["appName"] = $appName;
        $config["protocol"] = $protocol;


        // TODO 移除
        // $this->initGenericService($config);

        $haunt = Config::get('haunt.register');

        if (null !== $this->register) {
            if ($this->register == self::DISABLE_REGISTER) {
                return;
            }
            $this->toRegister($config);
            return;
        }

        $this->register = isset($haunt['enable_register']) ? $haunt['enable_register'] : self::ENABLE_REGISTER;

        if (self::DISABLE_REGISTER === $this->register) {
            return;
        }
        $this->toRegister($config);
    }

    private function toRegister($config)
    {
        $register = new ServerRegister();
        $coroutine = $register->register($config);
        Task::execute($coroutine);
    }

    private function doReportSwooleVer()
    {
        $runmode = RunMode::get();
        if ($runmode === "test" || $runmode === "qatest") {
            try {
                $task = function() {
                    yield (new HttpClient("10.9.143.96", 3000))->get("/", [
                        "host" => gethostname(),
                        "ip" => nova_get_ip(),
                        "ver" => swoole_version(),
                    ], null);
                };
                Task::execute($task());
            } catch (\Exception $e) {}
        }
    }
}