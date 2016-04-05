<?php

namespace Zan\Framework\Sdk\Log\Track;

class TrackPersistenceLogger extends TrackLogger {

    public function init() {
        $this->appender = new LoggerAppender(AppenderType::persistence, $this->type, $this->topic);
    }
}