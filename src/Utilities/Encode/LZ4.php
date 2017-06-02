<?php

namespace Zan\Framework\Utilities\Encode;
use Zan\Framework\Utilities\DesignPattern\Singleton;

class LZ4
{
    const LZ4_DELIMITER = ':';
    const LZ4_DELIMITER_LEN = 1;
    const LZ4_PREFIX = 'LZ4';
    const LZ4_PREFIX_LEN = 3;

    use Singleton;

    public function encode($str)
    {
        $prefix = self::LZ4_PREFIX . strlen($str) . self::LZ4_DELIMITER;
        return lz4_compress($str, false, $prefix);
    }

    public function decode($str)
    {
        $dataSize = $this->getDataSize($str);
        $headerSize = $this->getHeaderSize($dataSize);
        return lz4_uncompress($str, $dataSize, $headerSize);
    }

    public function isLZ4($str)
    {
        return is_string($str) && (0 === stripos($str, self::LZ4_PREFIX));
    }

    private function getDataSize($str)
    {
        return explode(self::LZ4_DELIMITER, substr($str, self::LZ4_PREFIX_LEN))[0];
    }

    private function getHeaderSize($dataSize)
    {
        return self::LZ4_PREFIX_LEN + strlen($dataSize) + self::LZ4_DELIMITER_LEN;
    }
}