<?php
class UnitTest extends \Zan\Framework\Testing\UnitTest {}
class TcpServer extends \Zan\Framework\Network\Tcp\Server {}

if (!function_exists("nova_encode")) {
    /**
     * @param string $service_name
     * @param string $method_name
     * @param int $ip
     * @param int $port
     * @param int $seq_no
     * @param string $attach_data
     * @param string $in_data
     * @param string $out_buffer
     * @return bool
     */
    function nova_encode($service_name, $method_name, $ip, $port, $seq_no, $attach_data, $in_data, &$out_buffer) {
        return false;
    }
}
if (function_exists("nova_decode")) {
    /**
     * @param string $in_buffer
     * @param string $service_name
     * @param string $method_name
     * @param int $ip
     * @param int $port
     * @param int $seq_no
     * @param string $attach_data
     * @param string $out_data
     * @return bool
     */
    function nova_decode($in_buffer, &$service_name, &$method_name, &$ip, &$port, &$seq_no, &$attach_data, &$out_data) {
        return false;
    }
}