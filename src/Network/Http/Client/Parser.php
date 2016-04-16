<?php

namespace Zan\Framework\Network\Http\Client;

class Parser
{
    const HEADER = 1;
    const BODY = 2;
    const FINISHED = 3;

    private $header = [];
    private $body;

    private $current;
    private $chunkdLength = null;

    public function __construct()
    {
        $this->current = self::HEADER;
        $this->header = '';
        $this->body = '';
        $this->chunkdLength = null;
    }

    public function parse($data)
    {
        if ($this->current === self::HEADER) {
            for(;;) {
                $pos = stripos($data, "\r\n");
                if ($pos === false) {
                    break;
                }
                if ($pos === 0) {
                    $this->current = self::BODY;
                    $data = substr($data, $pos+2);
                    break;
                }
                $pre = substr($data, 0, $pos);

                $this->parseHeader($pre);
                $data = substr($data, $pos+2);
            }
        }

        if ($this->current === self::BODY) {
            $this->parseBody($data);
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
            for(;;) {
                if (is_null($this->chunkdLength)) {
                    $pos = stripos($data, "\r\n");
                    if ($pos === false) {
                        break;
                    }
                    $pre = substr($data, 0, $pos);
                    if ($pre === '') {
                        break;
                    }
                    $sizeInfo = explode(' ', $pre, 1);
                    $this->chunkdLength = hexdec($sizeInfo[0]);
                    $data = substr($data, $pos+2);
                    if ($this->chunkdLength === 0) {
                        $this->current = self::FINISHED;
                    }
                } else {
                    if (strlen($data) >= $this->chunkdLength) {
                        $this->body .= substr($data, 0, $this->chunkdLength);
                        $data = substr($data, $this->chunkdLength+2);
                        $this->chunkdLength = null;
                    } else {
                        $this->body .= $data;
                        $this->chunkdLength = $this->chunkdLength - strlen($data);
                    }

                }
            }

        } elseif (isset($this->header['Content-Length'])) {
            $this->body .= substr($data, 0, $this->header['Content-Length']);
            if (strlen($this->body) >= $this->header['Content-Length']) {
                $this->current = self::FINISHED;
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