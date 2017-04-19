<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/4/20
 * Time: 上午2:32
 */

namespace Zan\Framework\Sdk\Trace;


use JsonSerializable;

class ChromeTraceJSONObject implements JsonSerializable
{
    private $json = [
        'version' => '1.0.0',
        'columns' => ['log', 'backtrace', 'type'],
        'rows'    => [],
    ];

    public function addRow($type, array $logs)
    {
        $backtrace = null;
        $this->json['rows'][] = [$logs, $backtrace, $type];
    }

    public function jsonSerialize()
    {
        return $this->json;
    }
}