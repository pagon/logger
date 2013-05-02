<?php

namespace Pagon\Logger;

use Pagon\LoggerInterface;

class Console extends LoggerInterface
{
    protected $options = array(
        'auto_write' => true
    );

    public function write()
    {
        if (empty($this->messages)) return;

        print join("\n", $this->buildAll()) . "\n";
    }
}