<?php
/**
 * Service scanner
 * User: moyo
 * Date: 12/3/15
 * Time: 6:16 PM
 */

namespace Zan\Framework\Network\Tcp\Nova\Service;

use Zan\Framework\Network\Tcp\Nova\Foundation\Traits\InstanceManager;

class Scanner
{
    /**
     * Instance mgr
     */
    use InstanceManager;

    /**
     * @var string
     */
    private $kdtApiRoot = '/';

    /**
     * @var string
     */
    private $kdtApiPath = 'vendor/kdt-api/';

    /**
     * @var string
     */
    private $serviceDir = 'service';

    /**
     * @var string
     */
    private $interfaceDir = 'interfaces';

    /**
     * @var string
     */
    private $regexServiceName = '/com\.youzan\.[a-z0-9\.]+/i';

    /**
     * @var array
     */
    private $stashServiceMethods = [];

    /**
     * @var Finder
     */
    private $finder = null;

    /**
     * Scanner constructor.
     */
    public function __construct()
    {
        $this->finder = Finder::instance();
    }

    /**
     * @param $appName
     * @return array
     */
    public function scanApis($appName)
    {
        $this->kdtApiRoot = ROOT_PATH . $this->kdtApiPath . $appName;
        $this->stashInit();
        $this->searching($this->kdtApiRoot . '/' .$this->serviceDir);
        return $this->syntaxFormatting($this->stashFlush());
    }

    /**
     * @param $serviceMethods
     * @return array
     */
    private function syntaxFormatting($serviceMethods)
    {
        $registerMap = [];
        foreach ($serviceMethods as $serviceName => $methodsMap)
        {
            $registerMap[] = [
                'service' => $serviceName,
                'methods' => array_keys($methodsMap)
            ];
        }
        return $registerMap;
    }

    /**
     * @param $dir
     */
    private function searching($dir)
    {
        $handler = opendir($dir);
        while (false !== $file = readdir($handler))
        {
            if (in_array($file, ['.', '..', '.git']))
            {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_dir($path))
            {
                $this->searching($path);
            }
            else
            {
                $this->parsingService($path);
            }
        }
    }

    /**
     * @param $file
     */
    private function parsingService($file)
    {
        $serviceCode = file_get_contents($file);
        $matched = preg_match($this->regexServiceName, $serviceCode, $matches);
        if ($matched && isset($matches[0]) && $this->isLocalHosting($matches[0]))
        {
            $this->parsingInterface($matches[0], str_replace($this->getDirPattern($this->serviceDir), $this->getDirPattern($this->interfaceDir), $file));
        }
    }

    /**
     * @param $serviceName
     * @param $file
     */
    private function parsingInterface($serviceName, $file)
    {
        $interfaceCode = file_get_contents($file);
        $tokens = token_get_all($interfaceCode);
        $last_token_code = null;
        foreach ($tokens as $token)
        {
            if (is_array($token))
            {
                list($token_code, $token_string) = $token;
                switch ($token_code)
                {
                    case T_WHITESPACE:
                        break;
                    case T_STRING:
                        if ($last_token_code === T_FUNCTION)
                        {
                            $this->stashAppend($serviceName, trim($token_string));
                        }
                        break;
                    default:
                        $last_token_code = $token_code;
                }
            }
        }
    }

    /**
     * @param $serviceName
     * @return bool
     */
    private function isLocalHosting($serviceName)
    {
        return class_exists($this->finder->getServiceController($serviceName));
    }

    /**
     * @param $dirName
     * @return string
     */
    private function getDirPattern($dirName)
    {
        return $this->kdtApiRoot . '/' . $dirName . '/';
    }

    /**
     * stash space init
     */
    private function stashInit()
    {
        $this->stashServiceMethods = [];
    }

    /**
     * @param $service
     * @param $method
     */
    private function stashAppend($service, $method)
    {
        $this->stashServiceMethods[$service][$method] = true;
    }

    /**
     * @return array
     */
    private function stashFlush()
    {
        return $this->stashServiceMethods;
    }
}