<?php

namespace Zan\Framework\Network\Http\Client;

class Parser
{
    const HEADER = 1;
    const BODY = 2;
    const FINISHED = 3;

    private $header;
    private $body;

    private $current;
    private $chunkdLength;

    public function __construct()
    {
        $this->current = self::HEADER;
        $this->header = '';
        $this->body = '';
        $this->chunkdLength = 0;
    }

    public function parse($data)
    {
        $lines = explode("\r\n", $data);

        foreach ($lines  as $key => $line) {
            if (strlen($line) == 0 and $this->current == self::HEADER) {
                $this->current = self::BODY;
                continue;
            }

            if ($this->current == self::HEADER) {
                $this->parseHeader($line);
            }

            if ($this->current == self::BODY) {
                $this->parseBody($line);
            }

            if ($this->current == self::FINISHED) {
                break;
            }
        }

        return $this->current;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getBody()
    {
        return $this->decode($this->body);
    }

    private function parseHeader($data)
    {
        if (stripos($data, ':') === false) {
            list($protocol, $code, $status) = explode(' ', $data, 3);
            if (empty($protocol) or empty($code) or empty($status)) {
                return false;
            }
            $this->header['protocol'] = $protocol;
            $this->header['code'] = $code;
            $this->header['status'] = $status;

            return true;
        } else {
            $row = explode(':', $data, 2);
            $value = isset($row[1]) ? $row[1] : '';
            $this->header[trim($row[0])] = trim($value);
            return true;
        }
    }

    private function parseBody($data)
    {
        if (isset($this->header['Transfer-Encoding']) and $this->header['Transfer-Encoding'] == 'chunked') {
            if (!$this->chunkdLength) {
                $sizeInfo = explode(' ', $data, 1);
                $this->chunkdLength = hexdec($sizeInfo[0]);
                if ($data === '0'){
                    $this->current = self::FINISHED;
                }
            } else {
                $this->body .= $data;
                $this->chunkdLength = 0;
            }
        } else {
            $this->body .= $data;
            $this->current = self::FINISHED;
        }
    }

    private function decode($data)
    {
        $encoding = isset($this->header['Content-Encoding']) ? $this->header['Content-Encoding'] : '';

        switch ($encoding)
        {
            case 'gzip':
                $content = gzdecode($data);
                break;
            case 'deflate':
                $content = gzinflate($data);
                break;
            case 'compress':
                $content = gzinflate(substr($data, 2, -4));
                break;
            default:
                $content = $data;
        }

        return $content;
    }
}