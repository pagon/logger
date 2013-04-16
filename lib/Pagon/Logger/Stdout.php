<?php

namespace Pagon\Logger;

class Stdout extends \Pagon\LoggerInterface
{
    protected $options = array(
        'auto_write' => true
    );

    public function write()
    {
        if (empty($this->messages)) return;

        print join("\n", $this->messages) . "\n";

        $this->clear();
    }
}