<?php

namespace Pagon;

abstract class LoggerInterface extends Fiber
{
    /**
     * @var array Options of logger
     */
    protected $options = array(
        'auto_write' => false
    );

    /**
     * @var array Saved messages
     */
    protected $messages = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->options;
    }

    /**
     * Send message to logger
     *
     * @param $message
     */
    public function send($message)
    {
        $this->messages[] = $message;

        if ($this->options['auto_write']) {
            $this->write();
        }
    }

    /**
     * Clear
     */
    public function clear()
    {
        $this->messages = array();
    }

    /**
     * @return mixed
     */
    abstract function write();
}