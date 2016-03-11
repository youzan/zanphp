<?php
/**
 * @author hupp
 * create date: 16/03/02
 */

namespace Zan\Framework\Network\Http;

class Parser
{
    public static function parseResponseData($response)
    {
        list($header, $body) = explode("\r\n\r\n", $response, 2);

        $header = self::parseHeader($header);

        if (isset($header['Transfer-Encoding']) && $header['Transfer-Encoding'] == 'chunked')
        {
            $body = self::parseChunked($body);
        }
        else {
            $body = self::parseBody($body, $header['Content-Type']);
        }
        return [$header, $body];
    }

    public static function parseHeader($header)
    {
        $headerLines = explode("\r\n", $header);
        list($method, $uri, $protocol) = explode(' ', $headerLines[0], 3);

        if (!$method || !$uri || !$protocol) {
            return false;
        }
        unset($headerLines[0]);

        return self::parseHeaderLine($headerLines);
    }

    public static function parseHeaderLine($headerLines)
    {
        if (is_string($headerLines)) {
            $headerLines = explode("\r\n", $headerLines);
        }
        $header = [];

        foreach ($headerLines as $row) {
            if (!($row = trim($row))) {
                continue;
            };
            $context = explode(':', $row, 2);
            $key = $context[0];

            $value = isset($context[1]) ? $context[1] : '';
            $header[trim($key)] = trim($value);
        }
        return $header;
    }

    public static function parseParams($str)
    {
        $params = [];
        $blocks = explode(";", $str);
        foreach ($blocks as $block) {
            $row = explode("=", $block, 2);
            if (count($row) == 2) {
                list ($key, $value) = $row;
                $params[trim($key)] = trim($value, "\r\n \t\"");
            }
            else {
                $params[$row[0]] = '';
            }
        }
        return $params;
    }

    public static function parseBody($body, $contentLength)
    {
        if (strlen($body) < $contentLength) {
            return false;
        }
        return $body;
    }

    public static function parseChunked($body)
    {
        $trunkLength = 0;

        while (true)
        {
            if ($trunkLength == 0)
            {
                $len = strstr($body, "\r\n", true);
                if ($len === false) {
                    return false;
                }
                if (($length = hexdec($len)) == 0)
                {
                    return true;
                }
                $trunkLength = $length;

                $body = substr($body, strlen($len) + 2);
            }
            else
            {
                if (strlen($body) < $trunkLength) {
                    return false;
                }
                $body .= substr($body, 0, $trunkLength);
                $body  = substr($body, $trunkLength + 2);

                $trunkLength = 0;
            }
        }
        return $body;
    }

    public static function parseCookie($request)
    {

    }

    public static function parseFormData()
    {

    }
}