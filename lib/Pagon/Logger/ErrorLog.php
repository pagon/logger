<?php

namespace Pagon\Logger;

class ErrorLog extends LoggerInterface
{
    protected $injectors = array(
        'auto_write' => true
    );

    public function write()
    {
        foreach ($this->formattedMessages() as $message) {
            error_log($message, 0);
        }
    }
}