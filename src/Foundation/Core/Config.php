<?php
namespace Zan\Framework\Foundation\Core;

use Zan\Framework\Utilities\Types\Arr;

class Config
{
    private static $configMap = [];

    public static function init()
    {
        $runMode = RunMode::get();
        $path = Path::getConfigPath();
        $modulePath = Path::getModuleConfigPath();

        $sharePath = $path . 'share/';
        $shareConfigMap = ConfigLoader::getInstance()->load($sharePath);

        $runModeConfigPath = $path . $runMode;
        $runModeConfig = ConfigLoader::getInstance()->load($runModeConfigPath);

        $moduleSharePath = $modulePath . 'share/';
        if (is_dir($moduleSharePath)) {
            $moduleShareConfigMap = ConfigLoader::getInstance()->load($moduleSharePath);
        } else {
            $moduleShareConfigMap = [];
        }

        $moduleRunModeConfigPath = $modulePath . $runMode;
        if (is_dir($moduleRunModeConfigPath)) {
            $moduleRunModeConfig = ConfigLoader::getInstance()->load($moduleRunModeConfigPath);
        } else {
            $moduleRunModeConfig = [];
        }

        self::$configMap = Arr::merge(self::$configMap,
            $shareConfigMap, $runModeConfig,
            $moduleShareConfigMap, $moduleRunModeConfig);

        //add private dir
        if (!RunMode::isOnline()) {
            $privatePath = $path . '.private/';
            if (is_dir($privatePath)) {
                $privateConfig = ConfigLoader::getInstance()->load($privatePath);
                self::$configMap = Arr::merge(self::$configMap, $privateConfig);
            }

            $modulePrivatePath = $modulePath . '.private/';
            if (is_dir($modulePrivatePath)) {
                $modulePrivateConfig = ConfigLoader::getInstance()->load($modulePrivatePath);
                self::$configMap = Arr::merge(self::$configMap, $modulePrivateConfig);
            }
        }
    }

    public static function get($key, $default = null)
    {
        $preKey = $key;
        $routes = explode('.', $key);
        if (empty($routes)) {
            return $default;
        }

        $result = &self::$configMap;
        $hasConfig = true;
        foreach ($routes as $route) {
            if (!isset($result[$route])) {
                $hasConfig = false;
                break;
            }
            $result = &$result[$route];
        }
        if (!$hasConfig) {
            return IronConfig::get($preKey, $default);
        }
        return $result;
    }

    public static function set($key, $value)
    {
        $routes = explode('.', $key);
        if (empty($routes)) {
            return false;
        }

        $newConfigMap = Arr::createTreeByList($routes, $value);
        self::$configMap = Arr::merge(self::$configMap, $newConfigMap);

        return true;
    }

    public static function clear()
    {
        self::$configMap = [];
    }
}
