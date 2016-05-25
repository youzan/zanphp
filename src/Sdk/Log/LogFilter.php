<?php
/**
 * Created by IntelliJ IDEA.
 * User: nuomi
 * Date: 16/5/25
 * Time: 下午3:49
 */

namespace Zan\Framework\Sdk\Log;

class LogFilter
{

    public function __construct(array $config)
    {
        if (!$config) {
            throw new InvalidArgumentException('Config is required' . $config);
            return false;
        }
        $this->config = $config;
        $this->logMap = isset($this->config['logMap']) ? $this->config['logMap'] : [];
        $this->config['levelNum'] = $this->getLevelNum($this->config['logLevel']);
    }

    public function getFilteredLog($level, $message = '', array $context = array())
    {
        $result = $message;
        if ($this->checkLevel($level)) {
            $data = $this->format($context);
            $result = sprintf("%s\t%s", $message, $data);
        }
        return $result;
    }

    private function getLevelNum($level)
    {
        return $this->logMap[$level];
    }

    private function checkLevel($level)
    {
        $levelNum = $this->getLevelNum($level);
        $result = $this->config['levelNum'] >= $levelNum;
        return $result;
    }

    private function format($data)
    {
        if (!$data || !is_array($data) || !is_object($data)) {
            return $data;
        }
        $format = isset($this->config['format']) ? $this->config['format'] : '';
        switch ($format) {
            case 'json':
                return json_encode($data, JSON_UNESCAPED_UNICODE);
                break;
            case 'var':
                return var_export($data, true);
                break;
            default :
                return '';
        }
    }

}
