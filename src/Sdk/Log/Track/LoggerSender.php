<?php

namespace Zan\Framework\Sdk\Log\Track;

interface LoggerSender {

    public function send($log);
}