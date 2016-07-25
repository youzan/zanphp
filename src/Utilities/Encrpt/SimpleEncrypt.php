<?php


namespace Zan\Framework\Utilities\Encrpt;


use Exception;

class SimpleEncrypt
{
    public static function encrypt($string)
    {
        if (strlen($string) > 100) {
            throw new Exception('too long string');
        }
        $x = rtrim(base64_encode(md5($string)), "=");
        $xLen = strlen($x);

        $y = base64_encode($string);
        // use gzcompress for more safety
        //$y = base64_encode(gzcompress($string, 2));
        $y = rtrim($y, '=');
        $yLen = strlen($y);
        $yLenLen = strlen($yLen);

        $str = [];
        if ($xLen > $yLen) {
            for ($i = 0; $i < $yLen; $i++) {
                $str[] = $y[$i];
                $str[] = $x[$i];
            }
            for (; $i < $xLen; $i++) {
                $str[] = $x[$i];
            }
        } else {
            for ($i = 0; $i < $xLen; $i++) {
                $str[] = $y[$i];
                $str[] = $x[$i];
            }
            for (; $i < $yLen; $i++) {
                $str[] = $y[$i];
            }
        }

        return $yLen . implode('', $str) . $yLenLen;
    }

    public static function decrypt($encrypted)
    {
        $len = strlen($encrypted);
        $yLenLen = intval($encrypted[$len - 1]);
        if ($yLenLen < 1) {
            return false;
        }

        $yLen = intval(substr($encrypted, 0, $yLenLen));
        if ($yLen < 1) {
            return false;
        }
        $xLen = $len - $yLen - $yLenLen - 1;
        if ($xLen < 40) {
            return false;
        }

        $xArr = [];
        $yArr = [];

        if ($xLen > $yLen) {
            for ($i = 0; $i < $yLen; $i++) {
                $yArr[] = $encrypted[$yLenLen + $i * 2];
                $xArr[] = $encrypted[$yLenLen + $i * 2 + 1];
            }
            for ($i = $yLenLen + $i * 2; $i < $len - 1; $i++) {
                $xArr[] = $encrypted[$i];
            }
        } else {
            for ($i = 0; $i < $xLen; $i++) {
                $yArr[] = $encrypted[$yLenLen + $i * 2];
                $xArr[] = $encrypted[$yLenLen + $i * 2 + 1];
            }
            for ($i = $yLenLen + $i * 2; $i < $len - 1; $i++) {
                $yArr[] = $encrypted[$i];
            }
        }

        $md5 = base64_decode(implode('', $xArr));
        $base64 = implode('', $yArr);

        $result = base64_decode($base64);
        $newMd5 = md5($result);
        if ($newMd5 != $md5) {
            return false;
        }

        return $result;
    }
}
