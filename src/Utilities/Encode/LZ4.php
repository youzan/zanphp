<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */

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
        return self::LZ4_PREFIX_LEN + strlen(strlen($dataSize)) + self::LZ4_DELIMITER_LEN;
    }
}